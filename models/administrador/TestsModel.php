<?php
require_once __DIR__ . '/../../database/Database.php';

class TestsModel {
    private $conn;
    public $lastError = null;
    private $table_tests = 'Tests';
    private $table_items = 'Items';
    private $table_opciones = 'Opciones_Respuesta';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /**
     * Obtener todos los tests
     */
    public function getAllTests() {
        try {
            $query = "SELECT * FROM {$this->table_tests} ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener tests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un test por ID con sus items
     */
    public function getTestById($id_test) {
        try {
            // Obtener información del test
            $query = "SELECT * FROM {$this->table_tests} WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $test = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($test) {
                // Obtener items del test
                $test['items'] = $this->getItemsByTestId($id_test);
            }

            return $test;
        } catch (PDOException $e) {
            error_log("Error al obtener test: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtener items de un test específico
     */
    public function getItemsByTestId($id_test) {
        try {
            $query = "SELECT * FROM {$this->table_items} 
                     WHERE id_test = :id_test 
                     ORDER BY orden ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener items: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener todas las opciones de respuesta disponibles
     */
    public function getAllOpciones() {
        try {
            $query = "SELECT * FROM {$this->table_opciones} ORDER BY valor_puntuacion ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener opciones: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear un nuevo test
     */
    public function createTest($nombre, $descripcion, $num_items) {
        try {
            $query = "INSERT INTO {$this->table_tests} (nombre, descripcion, num_items, created_at, updated_at) 
                     VALUES (:nombre, :descripcion, :num_items, NOW(), NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':num_items', $num_items, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear test: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un test existente
     */
    public function updateTest($id_test, $nombre, $descripcion, $num_items) {
        try {
            $query = "UPDATE {$this->table_tests} 
                     SET nombre = :nombre, 
                         descripcion = :descripcion, 
                         num_items = :num_items,
                         updated_at = NOW() 
                     WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':num_items', $num_items, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar test: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un test
     */
    public function deleteTest($id_test) {
        try {
            // Los items se eliminan automáticamente por ON DELETE CASCADE
            $query = "DELETE FROM {$this->table_tests} WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar test: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear un item para un test
     */
    public function createItem($id_test, $texto_item, $subescala, $orden) {
        try {
            $query = "INSERT INTO {$this->table_items} (id_test, texto_item, subescala, orden) 
                     VALUES (:id_test, :texto_item, :subescala, :orden)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->bindParam(':texto_item', $texto_item);
            $stmt->bindParam(':subescala', $subescala);
            $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un item
     */
    public function updateItem($id_item, $texto_item, $subescala, $orden) {
        try {
            $query = "UPDATE {$this->table_items} 
                     SET texto_item = :texto_item, 
                         subescala = :subescala, 
                         orden = :orden 
                     WHERE id_item = :id_item";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
            $stmt->bindParam(':texto_item', $texto_item);
            $stmt->bindParam(':subescala', $subescala);
            $stmt->bindParam(':orden', $orden, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un item
     */
    public function deleteItem($id_item) {
        try {
            $query = "DELETE FROM {$this->table_items} WHERE id_item = :id_item";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_item', $id_item, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar item: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar todos los items de un test
     */
    public function deleteItemsByTestId($id_test) {
        try {
            $query = "DELETE FROM {$this->table_items} WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar items: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buscar tests por nombre
     */
    public function searchTests($searchTerm) {
        try {
            $query = "SELECT * FROM {$this->table_tests} 
                     WHERE nombre LIKE :search OR descripcion LIKE :search 
                     ORDER BY nombre ASC";
            $stmt = $this->conn->prepare($query);
            $searchParam = "%{$searchTerm}%";
            $stmt->bindParam(':search', $searchParam);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al buscar tests: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener estadísticas de tests
     */
    public function getTestStats($id_test) {
        try {
            $query = "SELECT COUNT(DISTINCT id_aplicacion) as total_aplicaciones,
                            AVG(puntuacion_total) as promedio_puntuacion
                     FROM Aplicaciones
                     WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return ['total_aplicaciones' => 0, 'promedio_puntuacion' => 0];
        }
    }

    /**
     * Verificar si un test tiene aplicaciones
     */
    public function testHasApplications($id_test) {
        try {
            $query = "SELECT COUNT(*) as count FROM Aplicaciones WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Error al verificar aplicaciones: " . $e->getMessage());
            return false;
        }
    }
}
?>
