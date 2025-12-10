<?php
require_once __DIR__ . '/../BaseModel.php';

class DashboardEstudianteModel extends BaseModel {
    protected function getTableName() {
        return 'Aplicaciones';
    }

    protected function getPrimaryKey() {
        return 'id_aplicacion';
    }

    /**
     * Obtener estadísticas completas del dashboard para un estudiante
     * Incluye última aplicación de estrés, ansiedad y métricas globales
     */
    public function getEstadisticasDashboard($id_usuario) {
        try {
            $stmt = $this->conn->prepare("CALL sp_dashboard_estudiante(:id_usuario)");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            
            // Primera consulta: última aplicación de estrés
            $estres = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            
            // Segunda consulta: última aplicación de ansiedad
            $ansiedad = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->nextRowset();
            
            // Tercera consulta: estadísticas globales
            $global = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return [
                'estres' => $estres ?: null,
                'ansiedad' => $ansiedad ?: null,
                'global' => $global ?: [
                    'total_tests' => 0,
                    'dias_ultimo_test' => null,
                    'total_tests_estres' => 0,
                    'total_tests_ansiedad' => 0
                ]
            ];
        } catch (PDOException $e) {
            error_log("Error en getEstadisticasDashboard: " . $e->getMessage());
            return [
                'estres' => null,
                'ansiedad' => null,
                'global' => [
                    'total_tests' => 0,
                    'dias_ultimo_test' => null,
                    'total_tests_estres' => 0,
                    'total_tests_ansiedad' => 0
                ]
            ];
        }
    }

    /**
     * Obtener historial detallado de aplicaciones de un estudiante
     */
    public function getHistorialDetallado($id_usuario, $tipo_test, $fecha_inicio, $fecha_fin) {
        try {
            $stmt = $this->conn->prepare("
                CALL sp_historial_estudiante(:id_usuario, :tipo_test, :fecha_inicio, :fecha_fin)
            ");
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_test', $tipo_test, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            
            $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            return $historial;
        } catch (PDOException $e) {
            error_log("Error en getHistorialDetallado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Calcular desviación estándar de las últimas N aplicaciones
     * Mide la estabilidad emocional del estudiante
     */
    public function getEstabilidadEmocional($id_usuario, $tipo_test, $num_aplicaciones = 3) {
        try {
            $query = "
                SELECT STDDEV(a.puntuacion_total) as desviacion,
                       COUNT(*) as num_mediciones
                FROM Aplicaciones a
                JOIN Tests t ON a.id_test = t.id_test
                WHERE a.id_usuario = :id_usuario
                  AND t.tipo_test = :tipo_test
                  AND a.completo = TRUE
                  AND a.fecha_finalizacion IS NOT NULL
                ORDER BY a.fecha_finalizacion DESC
                LIMIT :num_aplicaciones
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_test', $tipo_test, PDO::PARAM_STR);
            $stmt->bindParam(':num_aplicaciones', $num_aplicaciones, PDO::PARAM_INT);
            $stmt->execute();
            
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['num_mediciones'] >= 2) {
                $desviacion = floatval($resultado['desviacion']);
                
                // Interpretar estabilidad
                if ($desviacion < 3) {
                    $nivel = 'alta';
                } elseif ($desviacion <= 8) {
                    $nivel = 'moderada';
                } else {
                    $nivel = 'baja';
                }
                
                return [
                    'desviacion' => round($desviacion, 2),
                    'num_mediciones' => $resultado['num_mediciones'],
                    'nivel_estabilidad' => $nivel
                ];
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en getEstabilidadEmocional: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calcular percentil del estudiante dentro de su curso
     */
    public function getPercentilCurso($id_usuario, $tipo_test) {
        try {
            // Obtener curso(s) del estudiante
            $query_cursos = "
                SELECT DISTINCT uc.id_curso
                FROM Usuario_Curso uc
                WHERE uc.id_usuario = :id_usuario
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($query_cursos);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            $curso = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$curso) {
                return null;
            }
            
            $id_curso = $curso['id_curso'];
            
            // Obtener última aplicación del usuario
            $query_usuario = "
                SELECT a.porcentaje_score
                FROM Aplicaciones a
                JOIN Tests t ON a.id_test = t.id_test
                WHERE a.id_usuario = :id_usuario
                  AND t.tipo_test = :tipo_test
                  AND a.completo = TRUE
                  AND a.fecha_finalizacion IS NOT NULL
                ORDER BY a.fecha_finalizacion DESC
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($query_usuario);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_test', $tipo_test, PDO::PARAM_STR);
            $stmt->execute();
            $mi_aplicacion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$mi_aplicacion || $mi_aplicacion['porcentaje_score'] === null) {
                return null;
            }
            
            $mi_porcentaje = $mi_aplicacion['porcentaje_score'];
            
            // Contar estudiantes del curso con porcentaje menor
            $query_percentil = "
                WITH ultimas_aplicaciones AS (
                    SELECT DISTINCT ON (uc.id_usuario) 
                           uc.id_usuario,
                           a.porcentaje_score
                    FROM Usuario_Curso uc
                    JOIN Aplicaciones a ON a.id_usuario = uc.id_usuario
                    JOIN Tests t ON a.id_test = t.id_test
                    WHERE uc.id_curso = :id_curso
                      AND t.tipo_test = :tipo_test
                      AND a.completo = TRUE
                      AND a.fecha_finalizacion IS NOT NULL
                    ORDER BY uc.id_usuario, a.fecha_finalizacion DESC
                )
                SELECT 
                    COUNT(CASE WHEN porcentaje_score < :mi_porcentaje THEN 1 END) as num_por_debajo,
                    COUNT(*) as total_estudiantes
                FROM ultimas_aplicaciones
            ";
            
            // Para MySQL (no soporta DISTINCT ON)
            $query_percentil = "
                SELECT 
                    COUNT(CASE WHEN t1.porcentaje_score < :mi_porcentaje THEN 1 END) as num_por_debajo,
                    COUNT(*) as total_estudiantes
                FROM (
                    SELECT uc.id_usuario, MAX(a.fecha_finalizacion) as max_fecha
                    FROM Usuario_Curso uc
                    JOIN Aplicaciones a ON a.id_usuario = uc.id_usuario
                    JOIN Tests t ON a.id_test = t.id_test
                    WHERE uc.id_curso = :id_curso
                      AND t.tipo_test = :tipo_test
                      AND a.completo = TRUE
                      AND a.fecha_finalizacion IS NOT NULL
                    GROUP BY uc.id_usuario
                ) tmp
                JOIN Aplicaciones a ON a.id_usuario = tmp.id_usuario AND a.fecha_finalizacion = tmp.max_fecha
                JOIN Tests t ON a.id_test = t.id_test AND t.tipo_test = :tipo_test
                JOIN (SELECT DISTINCT a.id_usuario, a.porcentaje_score FROM Aplicaciones a) t1 
                    ON t1.id_usuario = tmp.id_usuario
            ";
            
            $stmt = $this->conn->prepare($query_percentil);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':tipo_test', $tipo_test, PDO::PARAM_STR);
            $stmt->bindParam(':mi_porcentaje', $mi_porcentaje);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($resultado && $resultado['total_estudiantes'] > 0) {
                $percentil = ($resultado['num_por_debajo'] / $resultado['total_estudiantes']) * 100;
                return round($percentil, 2);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error en getPercentilCurso: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Detectar si el estudiante tiene riesgo emergente en sus últimas aplicaciones
     */
    public function detectarRiesgoEmergente($id_usuario) {
        try {
            $query = "
                SELECT 
                    a.id_aplicacion,
                    t.tipo_test,
                    t.nombre,
                    a.nivel_calculado,
                    a.fecha_finalizacion,
                    a.notas_calculo
                FROM Aplicaciones a
                JOIN Tests t ON a.id_test = t.id_test
                WHERE a.id_usuario = :id_usuario
                  AND a.completo = TRUE
                  AND a.fecha_finalizacion >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  AND a.notas_calculo LIKE '%RIESGO_EMERGENTE%'
                ORDER BY a.fecha_finalizacion DESC
                LIMIT 5
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            
            $riesgos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'tiene_riesgo' => count($riesgos) > 0,
                'num_casos' => count($riesgos),
                'casos' => $riesgos
            ];
        } catch (PDOException $e) {
            error_log("Error en detectarRiesgoEmergente: " . $e->getMessage());
            return [
                'tiene_riesgo' => false,
                'num_casos' => 0,
                'casos' => []
            ];
        }
    }
}
