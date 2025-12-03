<?php
require_once __DIR__ . '/../../database/Database.php';

class TestModel {
    private $conn;

    public function __construct($db) {
        // Se espera que $db sea una conexión PDO (Database->connect())
        $this->conn = $db;
    }

    /**
     * Obtener todos los tests con información completa incluyendo tipo de escala y opciones
     */
    public function getAllTestsConDetalles() {
        try {
            // Obtener tests con información del tipo de escala
            // Intentar ambas columnas posibles: id_tipo_escala y tipo_escala
            $query = "SELECT 
                        t.id_test,
                        t.nombre,
                        t.descripcion,
                        t.num_items,
                        t.created_at,
                        t.updated_at,
                        COALESCE(t.id_tipo_escala, t.tipo_escala) as id_tipo_escala,
                        COALESCE(te.nombre, te.nombre_escala) as nombre_escala,
                        te.descripcion as descripcion_escala
                      FROM Tests t
                      LEFT JOIN Tipos_Escalas te ON COALESCE(t.id_tipo_escala, t.tipo_escala) = te.id_tipo_escala
                      ORDER BY t.nombre ASC";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Para cada test, obtener las opciones de su tipo de escala
            foreach ($tests as &$test) {
                $test['opciones'] = [];

                if ($test['id_tipo_escala']) {
                    try {
                        // Intentar obtener opciones usando la tabla de mapeo TiposEscala_Opciones
                        $queryOpciones = "SELECT 
                                            o.id_opcion,
                                            o.texto_opcion,
                                            o.valor_puntuacion
                                          FROM Opciones_Respuesta o
                                          INNER JOIN TiposEscala_Opciones teo ON o.id_opcion = teo.id_opcion
                                          WHERE teo.id_tipo_escala = :id_tipo_escala
                                          ORDER BY o.valor_puntuacion ASC";

                        $stmtOpciones = $this->conn->prepare($queryOpciones);
                        $stmtOpciones->bindParam(':id_tipo_escala', $test['id_tipo_escala'], PDO::PARAM_INT);
                        $stmtOpciones->execute();
                        $opciones = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);

                        if ($opciones && count($opciones) > 0) {
                            $test['opciones'] = $opciones;
                        }
                    } catch (PDOException $e) {
                        // Si falla, intentar obtener opciones desde el campo opciones_ids en Tipos_Escalas
                        try {
                            $queryIds = "SELECT opciones_ids FROM Tipos_Escalas WHERE id_tipo_escala = :id_tipo_escala";
                            $stmtIds = $this->conn->prepare($queryIds);
                            $stmtIds->bindParam(':id_tipo_escala', $test['id_tipo_escala'], PDO::PARAM_INT);
                            $stmtIds->execute();
                            $result = $stmtIds->fetch(PDO::FETCH_ASSOC);

                            if ($result && !empty($result['opciones_ids'])) {
                                $ids = array_filter(array_map('trim', explode(',', $result['opciones_ids'])));
                                if (!empty($ids)) {
                                    $in = implode(',', array_map('intval', $ids));
                                    $queryOpcionesDirectas = "SELECT id_opcion, texto_opcion, valor_puntuacion 
                                                              FROM Opciones_Respuesta 
                                                              WHERE id_opcion IN ({$in}) 
                                                              ORDER BY valor_puntuacion ASC";
                                    $stmtOpcionesDirectas = $this->conn->prepare($queryOpcionesDirectas);
                                    $stmtOpcionesDirectas->execute();
                                    $test['opciones'] = $stmtOpcionesDirectas->fetchAll(PDO::FETCH_ASSOC);
                                }
                            }
                        } catch (PDOException $e2) {
                            // Si ambos métodos fallan, dejar opciones vacías
                            error_log("No se pudieron obtener opciones para el test {$test['id_test']}: " . $e2->getMessage());
                        }
                    }
                }
            }

            return $tests;
        } catch (PDOException $e) {
            error_log("Error al obtener tests con detalles: " . $e->getMessage());
            return [];
        }
    }
}
?>