<?php
require_once __DIR__ . '/../BaseModel.php';

class TestsModel extends BaseModel {
    private $table_tests = 'Tests';
    private $table_items = 'Items';
    private $table_opciones = 'Opciones_Respuesta';
    private $table_tipos_escalas = 'Tipos_Escalas';

    protected function getTableName() {
        return 'Tests';
    }

    protected function getPrimaryKey() {
        return 'id_test';
    }

    protected function getOrderBy() {
        return 'nombre ASC';
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
     * Obtener todos los tipos de escalas
     */
    public function getTiposEscalas() {
        try {
                // Intentar usar la tabla de mapeo TiposEscala_Opciones (modelo antiguo)
                try {
                    $query = "SELECT te.id_tipo_escala, te.nombre AS nombre, te.descripcion, o.id_opcion, o.texto_opcion, o.valor_puntuacion
                              FROM Tipos_Escalas te
                              JOIN TiposEscala_Opciones teo ON te.id_tipo_escala = teo.id_tipo_escala
                              JOIN Opciones_Respuesta o ON teo.id_opcion = o.id_opcion
                              ORDER BY te.id_tipo_escala, o.id_opcion";
                    $stmt = $this->conn->prepare($query);
                    $stmt->execute();
                    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $escalas = [];
                    foreach ($result as $row) {
                        $id = $row['id_tipo_escala'];
                        if (!isset($escalas[$id])) {
                            $escalas[$id] = [
                                'id_tipo_escala' => $row['id_tipo_escala'],
                                'nombre' => $row['nombre'],
                                'descripcion' => $row['descripcion'],
                                'opciones' => []
                            ];
                        }
                        $escalas[$id]['opciones'][] = [
                            'id_opcion' => $row['id_opcion'],
                            'texto_opcion' => $row['texto_opcion'],
                            'valor_puntuacion' => $row['valor_puntuacion']
                        ];
                    }
                    return array_values($escalas);
                } catch (PDOException $e) {
                    // Si no existe la tabla de mapeo, intentar el esquema que guarda 'opciones_ids' en Tipos_Escalas
                    // Intentar seleccionar 'nombre' y si falla usar 'nombre_escala'
                    try {
                        $query = "SELECT id_tipo_escala, nombre AS nombre, descripcion, opciones_ids FROM {$this->table_tipos_escalas} ORDER BY id_tipo_escala";
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute();
                        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        $query = "SELECT id_tipo_escala, nombre_escala AS nombre, descripcion, opciones_ids FROM {$this->table_tipos_escalas} ORDER BY id_tipo_escala";
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute();
                        $tipos = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    }

                    $escalas = [];
                    foreach ($tipos as $tipo) {
                        $opciones = [];
                        if (!empty($tipo['opciones_ids'])) {
                            // opciones_ids expected as comma separated ids
                            $ids = array_filter(array_map('trim', explode(',', $tipo['opciones_ids'])));
                            if (!empty($ids)) {
                                $in = implode(',', array_map('intval', $ids));
                                $q2 = "SELECT id_opcion, texto_opcion, valor_puntuacion FROM {$this->table_opciones} WHERE id_opcion IN ({$in}) ORDER BY valor_puntuacion ASC";
                                $s2 = $this->conn->prepare($q2);
                                $s2->execute();
                                $opciones = $s2->fetchAll(PDO::FETCH_ASSOC);
                            }
                        }

                        $escalas[] = [
                            'id_tipo_escala' => $tipo['id_tipo_escala'],
                            'nombre' => $tipo['nombre'],
                            'descripcion' => $tipo['descripcion'],
                            'opciones' => $opciones,
                        ];
                    }

                    return $escalas;
                }
        } catch (PDOException $e) {
            error_log("Error al obtener tipos de escalas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener lista simple de tipos de escala (sin opciones)
     */
    public function getTiposSimple() {
        try {
            // Intentar seleccionar columna 'nombre', si no existe usar 'nombre_escala'
            try {
                $query = "SELECT id_tipo_escala, nombre AS nombre, descripcion FROM {$this->table_tipos_escalas} ORDER BY id_tipo_escala ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                $query = "SELECT id_tipo_escala, nombre_escala AS nombre, descripcion FROM {$this->table_tipos_escalas} ORDER BY id_tipo_escala ASC";
                $stmt = $this->conn->prepare($query);
                $stmt->execute();
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Error al obtener tipos simples: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener opciones de respuesta por tipo de escala
     */
    public function getOpcionesByTipoEscala($tipo_escala) {
        try {
            // Obtener los IDs de opciones del tipo de escala
            $query = "SELECT opciones_ids FROM {$this->table_tipos_escalas} WHERE id_tipo_escala = :tipo_escala";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':tipo_escala', $tipo_escala, PDO::PARAM_INT);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result || !$result['opciones_ids']) {
                return [];
            }
            
            $opciones_ids = $result['opciones_ids'];
            
            // Obtener las opciones correspondientes
            $query = "SELECT * FROM {$this->table_opciones} WHERE id_opcion IN ({$opciones_ids}) ORDER BY valor_puntuacion ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener opciones por tipo de escala: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crear un nuevo test
     */
    public function createTest($nombre, $descripcion, $num_items, $tipo_escala = 1) {
        try {
            // Some installations have the column named `id_tipo_escala` (schema),
            // others use `tipo_escala` (newer migrations). Use the existing
            // column name in the DB by inserting into `id_tipo_escala` which
            // is the column defined in `database/db.sql`.
            $query = "INSERT INTO {$this->table_tests} (nombre, descripcion, num_items, id_tipo_escala, created_at, updated_at) 
                     VALUES (:nombre, :descripcion, :num_items, :tipo_escala, NOW(), NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':num_items', $num_items, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_escala', $tipo_escala, PDO::PARAM_INT);
            
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
    public function updateTest($id_test, $nombre, $descripcion, $num_items, $tipo_escala = 1) {
        try {
            // Similar compatibility change for UPDATE: write into `id_tipo_escala`.
            $query = "UPDATE {$this->table_tests} 
                     SET nombre = :nombre, 
                         descripcion = :descripcion, 
                         num_items = :num_items,
                         id_tipo_escala = :tipo_escala,
                         updated_at = NOW() 
                     WHERE id_test = :id_test";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_test', $id_test, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':num_items', $num_items, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_escala', $tipo_escala, PDO::PARAM_INT);
            
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

    /**
     * Crear un nuevo tipo de escala
     */
    public function createTipoEscala($nombre, $descripcion = '') {
        try {
            $query = "INSERT INTO {$this->table_tipos_escalas} (nombre, descripcion) 
                     VALUES (:nombre, :descripcion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear tipo de escala: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva opción de respuesta
     */
    public function createOpcionRespuesta($texto_opcion, $valor_puntuacion) {
        try {
            $query = "INSERT INTO {$this->table_opciones} (texto_opcion, valor_puntuacion) 
                     VALUES (:texto_opcion, :valor_puntuacion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':texto_opcion', $texto_opcion);
            $stmt->bindParam(':valor_puntuacion', $valor_puntuacion, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al crear opción de respuesta: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vincular una opción de respuesta con un tipo de escala
     */
    public function vincularOpcionConEscala($id_tipo_escala, $id_opcion) {
        try {
            $query = "INSERT INTO TiposEscala_Opciones (id_tipo_escala, id_opcion) 
                     VALUES (:id_tipo_escala, :id_opcion)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_tipo_escala', $id_tipo_escala, PDO::PARAM_INT);
            $stmt->bindParam(':id_opcion', $id_opcion, PDO::PARAM_INT);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->lastError = $e->getMessage();
            error_log("Error al vincular opción con escala: " . $e->getMessage());
            return false;
        }
    }
}
?>
