<?php
/**
 * Modelo para gestión de Tipos de Escalas y Opciones
 */

require_once dirname(__DIR__, 2) . '/database/Database.php';

class EscalasModel {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los tipos de escalas
     */
    public function getTiposEscalas() {
        try {
            $query = "SELECT 
                        te.id_tipo_escala,
                        te.nombre,
                        te.descripcion,
                        COUNT(teo.id_opcion) as num_opciones,
                        (SELECT COUNT(*) FROM Tests WHERE id_tipo_escala = te.id_tipo_escala) as tests_usando
                      FROM Tipos_Escalas te
                      LEFT JOIN TiposEscala_Opciones teo ON te.id_tipo_escala = teo.id_tipo_escala
                      GROUP BY te.id_tipo_escala
                      ORDER BY te.id_tipo_escala ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tipos de escalas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener detalles de un tipo de escala con sus opciones
     */
    public function getTipoEscalaConOpciones($id_tipo_escala) {
        try {
            // Obtener tipo de escala
            $query = "SELECT * FROM Tipos_Escalas WHERE id_tipo_escala = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_tipo_escala]);
            $escala = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$escala) {
                return null;
            }
            
            // Obtener opciones asociadas
            $queryOpciones = "SELECT 
                                or.id_opcion,
                                or.texto_opcion,
                                or.valor_puntuacion
                              FROM Opciones_Respuesta or
                              INNER JOIN TiposEscala_Opciones teo 
                                ON or.id_opcion = teo.id_opcion
                              WHERE teo.id_tipo_escala = ?
                              ORDER BY or.valor_puntuacion ASC";
            
            $stmtOpciones = $this->db->prepare($queryOpciones);
            $stmtOpciones->execute([$id_tipo_escala]);
            $escala['opciones'] = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);
            
            return $escala;
        } catch (PDOException $e) {
            error_log("Error al obtener tipo de escala: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear un nuevo tipo de escala
     */
    public function createTipoEscala($nombre, $descripcion, $opciones) {
        try {
            $this->db->beginTransaction();
            
            // Insertar tipo de escala
            $query = "INSERT INTO Tipos_Escalas (nombre, descripcion) VALUES (?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nombre, $descripcion]);
            
            $id_tipo_escala = $this->db->lastInsertId();
            
            // Insertar opciones
            if (!empty($opciones)) {
                foreach ($opciones as $opcion) {
                    // Insertar opción de respuesta
                    $queryOpcion = "INSERT INTO Opciones_Respuesta (texto_opcion, valor_puntuacion) 
                                   VALUES (?, ?)";
                    $stmtOpcion = $this->db->prepare($queryOpcion);
                    $stmtOpcion->execute([
                        $opcion['texto_opcion'],
                        $opcion['valor_puntuacion']
                    ]);
                    
                    $id_opcion = $this->db->lastInsertId();
                    
                    // Vincular con tipo de escala
                    $queryVinculo = "INSERT INTO TiposEscala_Opciones (id_tipo_escala, id_opcion) 
                                    VALUES (?, ?)";
                    $stmtVinculo = $this->db->prepare($queryVinculo);
                    $stmtVinculo->execute([$id_tipo_escala, $id_opcion]);
                }
            }
            
            $this->db->commit();
            return $id_tipo_escala;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al crear tipo de escala: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un tipo de escala
     */
    public function updateTipoEscala($id_tipo_escala, $nombre, $descripcion, $opciones = null) {
        try {
            $this->db->beginTransaction();
            
            // Actualizar tipo de escala
            $query = "UPDATE Tipos_Escalas SET nombre = ?, descripcion = ? WHERE id_tipo_escala = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$nombre, $descripcion, $id_tipo_escala]);
            
            // Si se proporcionan opciones, actualizar
            if ($opciones !== null) {
                // Eliminar vínculos existentes
                $queryDelete = "DELETE FROM TiposEscala_Opciones WHERE id_tipo_escala = ?";
                $stmtDelete = $this->db->prepare($queryDelete);
                $stmtDelete->execute([$id_tipo_escala]);
                
                // Insertar nuevas opciones
                foreach ($opciones as $opcion) {
                    if (isset($opcion['id_opcion']) && $opcion['id_opcion']) {
                        // Actualizar opción existente
                        $queryUpdateOpcion = "UPDATE Opciones_Respuesta 
                                             SET texto_opcion = ?, valor_puntuacion = ?
                                             WHERE id_opcion = ?";
                        $stmtUpdateOpcion = $this->db->prepare($queryUpdateOpcion);
                        $stmtUpdateOpcion->execute([
                            $opcion['texto_opcion'],
                            $opcion['valor_puntuacion'],
                            $opcion['id_opcion']
                        ]);
                        $id_opcion = $opcion['id_opcion'];
                    } else {
                        // Insertar nueva opción
                        $queryOpcion = "INSERT INTO Opciones_Respuesta (texto_opcion, valor_puntuacion) 
                                       VALUES (?, ?)";
                        $stmtOpcion = $this->db->prepare($queryOpcion);
                        $stmtOpcion->execute([
                            $opcion['texto_opcion'],
                            $opcion['valor_puntuacion']
                        ]);
                        $id_opcion = $this->db->lastInsertId();
                    }
                    
                    // Vincular con tipo de escala
                    $queryVinculo = "INSERT INTO TiposEscala_Opciones (id_tipo_escala, id_opcion) 
                                    VALUES (?, ?)";
                    $stmtVinculo = $this->db->prepare($queryVinculo);
                    $stmtVinculo->execute([$id_tipo_escala, $id_opcion]);
                }
            }
            
            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error al actualizar tipo de escala: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar un tipo de escala
     */
    public function deleteTipoEscala($id_tipo_escala) {
        try {
            // Verificar si está siendo usado
            $queryCheck = "SELECT COUNT(*) as count FROM Tests WHERE id_tipo_escala = ?";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->execute([$id_tipo_escala]);
            $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return false; // No se puede eliminar
            }
            
            $query = "DELETE FROM Tipos_Escalas WHERE id_tipo_escala = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$id_tipo_escala]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Error al eliminar tipo de escala: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todas las opciones de respuesta disponibles
     */
    public function getOpcionesRespuesta() {
        try {
            $query = "SELECT * FROM Opciones_Respuesta ORDER BY valor_puntuacion ASC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener opciones: " . $e->getMessage());
            return [];
        }
    }
}
