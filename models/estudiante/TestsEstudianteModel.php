<?php
require_once __DIR__ . '/../../database/Database.php';


class TestsEstudianteModel {
    private $conn;

    public function getConn() {
        return $this->conn;
    }

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Obtener todos los tests disponibles (incluyendo sugeridos por profesores)
     */
    public function getTestsDisponibles($id_usuario = null) {
        try {
            if ($id_usuario) {
                // Usar el nuevo procedimiento que incluye tests sugeridos y generales
                $stmt = $this->conn->prepare("CALL sp_obtener_todos_tests_estudiante(:id_usuario)");
                $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
                $stmt->execute();
                $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                return $tests;
            }

            // Fallback: lista todos los tests activos
            $stmt = $this->conn->prepare("CALL sp_obtener_tests_disponibles()");
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $tests;
        } catch (PDOException $e) {
            error_log("Error en getTestsDisponibles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener solo los tests sugeridos por profesores para un estudiante
     */
    public function getTestsSugeridos($id_usuario) {
        try {
            $stmt = $this->conn->prepare("CALL sp_obtener_tests_sugeridos_estudiante(:id_usuario)");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return $tests;
        } catch (PDOException $e) {
            error_log("Error en getTestsSugeridos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un test específico por ID
     */
    public function getTestById($id_test) {
        try {
            $stmt = $this->conn->prepare("
                SELECT id_test, nombre, descripcion, num_items 
                FROM Tests 
                WHERE id_test = :id_test
            ");
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getTestById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener una aplicación pendiente para un usuario y test (si existe)
     */
    public function getPendingAplicacion($id_usuario, $id_test) {
        try {
            $stmt = $this->conn->prepare("SELECT id_aplicacion, id_usuario, id_test, fecha_aplicacion FROM Aplicaciones WHERE id_usuario = :id_usuario AND id_test = :id_test AND puntuacion_total IS NULL LIMIT 1");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getPendingAplicacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener los items (preguntas) de un test
     */
    public function getItemsByTest($id_test) {
        try {
            $stmt = $this->conn->prepare("CALL sp_obtener_items_por_test(:id_test)");
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $items = $stmt->fetchAll();
            $stmt->closeCursor();
            return $items;
        } catch (PDOException $e) {
            error_log("Error en getItemsByTest: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener las opciones de respuesta generales
     */
    public function getOpcionesRespuesta() {
        try {
            $stmt = $this->conn->prepare("CALL sp_obtener_opciones_respuesta_generales()");
            $stmt->execute();
            $opciones = $stmt->fetchAll();
            $stmt->closeCursor();
            return $opciones;
        } catch (PDOException $e) {
            error_log("Error en getOpcionesRespuesta: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener opciones de respuesta filtradas por tipo de escala del test
     */
    public function getOpcionesByTestId($id_test) {
        try {
            // Obtener el tipo de escala del test
            $stmt = $this->conn->prepare("SELECT id_tipo_escala FROM Tests WHERE id_test = :id_test");
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch();
            
            if (!$result || empty($result['id_tipo_escala'])) {
                // Si no tiene tipo de escala, retornar todas las opciones generales
                return $this->getOpcionesRespuesta();
            }

            $id_tipo_escala = (int)$result['id_tipo_escala'];

            // Obtener las opciones vinculadas a este tipo de escala usando la tabla intermedia
            $query = "SELECT o.id_opcion, o.texto_opcion, o.valor_puntuacion
                      FROM TiposEscala_Opciones te
                      JOIN Opciones_Respuesta o ON te.id_opcion = o.id_opcion
                      WHERE te.id_tipo_escala = :id_tipo_escala
                      ORDER BY o.valor_puntuacion ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_tipo_escala', $id_tipo_escala, PDO::PARAM_INT);
            $stmt->execute();
            $opciones = $stmt->fetchAll();
            $stmt->closeCursor();
            return $opciones;
        } catch (PDOException $e) {
            error_log("Error en getOpcionesByTestId: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Iniciar una nueva aplicación de test
     */
    public function iniciarAplicacion($id_usuario, $id_test) {
        try {
            $stmt = $this->conn->prepare("CALL sp_iniciar_aplicacion(:id_usuario, :id_test, @id_aplicacion)");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            
            // Obtener el ID de aplicación generado
            $result = $this->conn->query("SELECT @id_aplicacion as id_aplicacion")->fetch();
            return $result['id_aplicacion'];
        } catch (PDOException $e) {
            error_log("Error en iniciarAplicacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Registrar una respuesta individual
     */
    public function registrarRespuesta($id_aplicacion, $id_item, $id_opcion_seleccionada) {
        try {
            $stmt = $this->conn->prepare("
                CALL sp_registrar_respuesta(:id_aplicacion, :id_item, :id_opcion)
            ");
            $stmt->bindParam(':id_aplicacion', $id_aplicacion, PDO::PARAM_INT);
            $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
            $stmt->bindParam(':id_opcion', $id_opcion_seleccionada, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            return true;
        } catch (PDOException $e) {
            error_log("Error en registrarRespuesta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Finalizar aplicación y calcular puntuación
     */
    public function finalizarAplicacion($id_aplicacion) {
        try {
            $stmt = $this->conn->prepare("
                CALL sp_finalizar_aplicacion_y_calcular_puntuacion(:id_aplicacion)
            ");
            $stmt->bindParam(':id_aplicacion', $id_aplicacion, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch();
            $stmt->closeCursor();
            return $resultado;
        } catch (PDOException $e) {
            error_log("Error en finalizarAplicacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener el historial de aplicaciones de un usuario
     */
    public function getHistorialUsuario($id_usuario) {
        try {
            $stmt = $this->conn->prepare("CALL sp_obtener_historial_usuario(:id_usuario)");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $historial = $stmt->fetchAll();
            $stmt->closeCursor();
            return $historial;
        } catch (PDOException $e) {
            error_log("Error en getHistorialUsuario: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener detalles de una aplicación específica
     */
    public function getDetalleAplicacion($id_aplicacion) {
        try {
            $stmt = $this->conn->prepare("CALL sp_obtener_detalle_aplicacion(:id_aplicacion)");
            $stmt->bindParam(':id_aplicacion', $id_aplicacion, PDO::PARAM_INT);
            $stmt->execute();
            $detalle = $stmt->fetchAll();
            $stmt->closeCursor();
            return $detalle;
        } catch (PDOException $e) {
            error_log("Error en getDetalleAplicacion: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener resultado de una aplicación
     */
    public function getResultadoAplicacion($id_aplicacion) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    a.id_aplicacion,
                    t.nombre as nombre_test,
                    a.fecha_aplicacion,
                    a.puntuacion_total,
                    a.resultado_nivel
                FROM Aplicaciones a
                JOIN Tests t ON a.id_test = t.id_test
                WHERE a.id_aplicacion = :id_aplicacion
            ");
            $stmt->bindParam(':id_aplicacion', $id_aplicacion, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error en getResultadoAplicacion: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener IDs de tests que el usuario ya ha completado
     */
    public function getTestsCompletadosPorUsuario($id_usuario) {
        try {
            $stmt = $this->conn->prepare("
                SELECT DISTINCT t.id_test
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.ID_Test = t.id_test
                WHERE a.ID_Usuario = :id_usuario 
                  AND a.Completado = 1
                ORDER BY a.Fecha_Aplicacion DESC
            ");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            
            // Retornar array de IDs
            $completados = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
            $stmt->closeCursor();
            return $completados;
        } catch (PDOException $e) {
            error_log("Error en getTestsCompletadosPorUsuario: " . $e->getMessage());
            return [];
        }
    }
}
?>
