<?php
require_once __DIR__ . '/../BaseModel.php';

/**
 * RecomendacionesModel
 * Modelo para gestión de recomendaciones personalizadas según niveles de estrés/ansiedad
 * Implementa patrón Template Method heredando de BaseModel
 */
class RecomendacionesModel extends BaseModel {
    
    protected function getTableName() {
        return 'Recomendaciones';
    }

    protected function getPrimaryKey() {
        return 'id_recomendacion';
    }

    protected function getOrderBy() {
        return 'prioridad DESC, titulo ASC';
    }

    /**
     * Obtener todas las recomendaciones activas
     */
    public function getAllActive() {
        try {
            $query = "SELECT * FROM {$this->getTableName()} 
                      WHERE activa = 1 
                      ORDER BY prioridad DESC, titulo ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Crear una nueva recomendación
     */
    public function create($data) {
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO Recomendaciones 
                (titulo, descripcion, categoria, tipo_test, nivel_minimo, nivel_maximo, prioridad, activa) 
                VALUES (:titulo, :descripcion, :categoria, :tipo_test, :nivel_minimo, :nivel_maximo, :prioridad, :activa)'
            );
            
            return $stmt->execute([
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':categoria' => $data['categoria'],
                ':tipo_test' => $data['tipo_test'] ?? 'ambos',
                ':nivel_minimo' => $data['nivel_minimo'] ?? 1,
                ':nivel_maximo' => $data['nivel_maximo'] ?? 5,
                ':prioridad' => $data['prioridad'] ?? 3,
                ':activa' => $data['activa'] ?? 1
            ]);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return false;
        }
    }

    /**
     * Actualizar una recomendación existente
     */
    public function update($id, $data) {
        try {
            $stmt = $this->conn->prepare(
                'UPDATE Recomendaciones 
                 SET titulo = :titulo, 
                     descripcion = :descripcion, 
                     categoria = :categoria, 
                     tipo_test = :tipo_test,
                     nivel_minimo = :nivel_minimo, 
                     nivel_maximo = :nivel_maximo, 
                     prioridad = :prioridad, 
                     activa = :activa 
                 WHERE id_recomendacion = :id'
            );
            
            return $stmt->execute([
                ':id' => $id,
                ':titulo' => $data['titulo'],
                ':descripcion' => $data['descripcion'],
                ':categoria' => $data['categoria'],
                ':tipo_test' => $data['tipo_test'] ?? 'ambos',
                ':nivel_minimo' => $data['nivel_minimo'] ?? 1,
                ':nivel_maximo' => $data['nivel_maximo'] ?? 5,
                ':prioridad' => $data['prioridad'] ?? 3,
                ':activa' => $data['activa'] ?? 1
            ]);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return false;
        }
    }

    /**
     * Filtrar recomendaciones por categoría
     */
    public function getByCategoria($categoria) {
        try {
            $query = "SELECT * FROM {$this->getTableName()} 
                      WHERE categoria = :categoria AND activa = 1 
                      ORDER BY prioridad DESC, titulo ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':categoria', $categoria, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Filtrar recomendaciones por magnitud/nivel
     */
    public function getByMagnitud($magnitud) {
        try {
            $query = "SELECT * FROM {$this->getTableName()} 
                      WHERE nivel_minimo <= :magnitud 
                      AND nivel_maximo >= :magnitud 
                      AND activa = 1 
                      ORDER BY prioridad DESC, titulo ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':magnitud', $magnitud, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Obtener recomendaciones personalizadas para un estudiante según sus últimas aplicaciones
     * Esta es la función clave para auto-recomendación
     */
    public function getRecomendacionesParaEstudiante($idEstudiante) {
        try {
            // Obtener las últimas aplicaciones del estudiante
            $query = "
                SELECT 
                    a.id_aplicacion,
                    a.id_test,
                    t.tipo_test,
                    a.nivel_calculado,
                    a.porcentaje_score,
                    a.fecha_finalizacion
                FROM Aplicaciones a
                INNER JOIN Tests t ON a.id_test = t.id_test
                WHERE a.id_usuario = :id_usuario
                AND a.completo = 1
                AND a.nivel_calculado IS NOT NULL
                ORDER BY a.fecha_finalizacion DESC
                LIMIT 2
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id_usuario' => $idEstudiante]);
            $aplicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($aplicaciones)) {
                return [];
            }

            // Mapear niveles textuales a numéricos
            $nivelMap = [
                'normal' => 1,
                'leve' => 2,
                'moderado' => 3,
                'alto' => 4,
                'severo' => 5
            ];

            $recomendaciones = [];
            $recomendacionesIds = []; // Para evitar duplicados

            foreach ($aplicaciones as $app) {
                $tipoTest = $app['tipo_test'];
                $nivelTexto = $app['nivel_calculado'];
                $nivelNumerico = $nivelMap[$nivelTexto] ?? 3;

                // Buscar recomendaciones apropiadas
                $queryRec = "
                    SELECT * FROM Recomendaciones
                    WHERE activa = 1
                    AND (tipo_test = :tipo_test OR tipo_test = 'ambos')
                    AND nivel_minimo <= :nivel
                    AND nivel_maximo >= :nivel
                    ORDER BY prioridad DESC, titulo ASC
                    LIMIT 5
                ";

                $stmtRec = $this->conn->prepare($queryRec);
                $stmtRec->execute([
                    ':tipo_test' => $tipoTest,
                    ':nivel' => $nivelNumerico
                ]);

                $recsApp = $stmtRec->fetchAll(PDO::FETCH_ASSOC);

                foreach ($recsApp as $rec) {
                    $recId = $rec['id_recomendacion'];
                    if (!in_array($recId, $recomendacionesIds)) {
                        $rec['test_tipo'] = $tipoTest;
                        $rec['nivel_detectado'] = $nivelTexto;
                        $rec['nivel_numerico'] = $nivelNumerico;
                        $recomendaciones[] = $rec;
                        $recomendacionesIds[] = $recId;
                    }
                }
            }

            // Ordenar por prioridad descendente
            usort($recomendaciones, function($a, $b) {
                return $b['prioridad'] - $a['prioridad'];
            });

            return $recomendaciones;
            
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Obtener estadísticas de recomendaciones
     */
    public function getEstadisticas() {
        try {
            $stats = [];
            
            // Total de recomendaciones
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM Recomendaciones");
            $stats['total'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Recomendaciones activas
            $stmt = $this->conn->query("SELECT COUNT(*) as activas FROM Recomendaciones WHERE activa = 1");
            $stats['activas'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['activas'];
            
            // Recomendaciones críticas (prioridad 4-5)
            $stmt = $this->conn->query("SELECT COUNT(*) as criticas FROM Recomendaciones WHERE prioridad >= 4 AND activa = 1");
            $stats['criticas'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['criticas'];
            
            // Categorías únicas
            $stmt = $this->conn->query("SELECT COUNT(DISTINCT categoria) as categorias FROM Recomendaciones WHERE activa = 1");
            $stats['categorias'] = (int)$stmt->fetch(PDO::FETCH_ASSOC)['categorias'];
            
            return $stats;
            
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [
                'total' => 0,
                'activas' => 0,
                'criticas' => 0,
                'categorias' => 0
            ];
        }
    }

    /**
     * Buscar recomendaciones por término
     */
    public function search($searchTerm) {
        try {
            $query = "SELECT * FROM {$this->getTableName()} 
                      WHERE (titulo LIKE :search OR descripcion LIKE :search) 
                      ORDER BY prioridad DESC, titulo ASC";
            $stmt = $this->conn->prepare($query);
            $searchPattern = '%' . $searchTerm . '%';
            $stmt->bindParam(':search', $searchPattern, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Activar/Desactivar recomendación
     */
    public function toggleActiva($id) {
        try {
            $stmt = $this->conn->prepare(
                'UPDATE Recomendaciones 
                 SET activa = NOT activa 
                 WHERE id_recomendacion = :id'
            );
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return false;
        }
    }
}
?>
