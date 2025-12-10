<?php
class DashboardModel {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // --- Funciones de Reportes (Ahora por CURSO) ---

    public function getConteoNivelesAltosPorCurso($id_curso) {
        $stmt = $this->conn->prepare("CALL sp_contar_niveles_altos_por_curso(:p_id_curso)");
        $stmt->bindParam(':p_id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $resultado = $stmt->fetch();
        $stmt->closeCursor();
        return $resultado ? $resultado['conteo_niveles_altos'] : 0;
    }

    public function getEvolucionTemporalPorCurso($id_curso) {
        $stmt = $this->conn->prepare("CALL sp_obtener_evolucion_temporal_por_curso(:p_id_curso)");
        $stmt->bindParam(':p_id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        $stmt->closeCursor();
        return $resultados;
    }

    public function getDistribucionRiesgoPorCurso($id_curso) {
        $stmt = $this->conn->prepare("CALL sp_obtener_distribucion_riesgo_por_curso(:p_id_curso)");
        $stmt->bindParam(':p_id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        $stmt->closeCursor();
        return $resultados;
    }
    
    public function sugerirTestACurso($id_curso, $id_test, $id_profesor) {
        try {
            // Usar sp_sugerir_test que es el procedimiento correcto
            $stmt = $this->conn->prepare("CALL sp_sugerir_test(:p_id_curso, :p_id_test, :p_id_profesor)");
            $stmt->bindParam(':p_id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':p_id_test', $id_test, PDO::PARAM_INT);
            $stmt->bindParam(':p_id_profesor', $id_profesor, PDO::PARAM_INT);
            $stmt->execute();
            $resultado = $stmt->fetch();
            $stmt->closeCursor();
            return [
                'success' => true,
                'estudiantes_afectados' => $resultado['estudiantes_afectados'] ?? 0
            ];
        } catch(PDOException $e) {
            error_log("Error al sugerir test: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    public function getCursosPorProfesor($id_profesor) {
        $stmt = $this->conn->prepare("CALL sp_obtener_cursos_por_profesor(:p_id_profesor)");
        $stmt->bindParam(':p_id_profesor', $id_profesor, PDO::PARAM_INT);
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        $stmt->closeCursor();
        return $resultados;
    }
    
    public function getComparativaEscuelas() {
        // Esta función (y su SP) no cambian, es un reporte global
        $stmt = $this->conn->prepare("CALL sp_obtener_comparativa_escuelas()");
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        $stmt->closeCursor();
        return $resultados;
    }

    public function getTestsDisponibles() {
        // Asumimos que quieres sugerir los tests principales, 1 y 2
        $stmt = $this->conn->prepare(
            "SELECT id_test, nombre, descripcion, num_items 
             FROM Tests 
             WHERE id_test IN (1, 2) AND estado_test = 'activo'"
        );
        $stmt->execute();
        $resultados = $stmt->fetchAll();
        $stmt->closeCursor();
        return $resultados;
    }

    /**
     * Obtener todos los tests con información completa incluyendo tipo de escala y opciones
     */
    public function getAllTestsConDetalles() {
        try {
            // Obtener tests con información del tipo de escala
            $query = "SELECT 
                        t.id_test,
                        t.nombre,
                        t.descripcion,
                        t.num_items,
                        t.tipo_test,
                        t.created_at,
                        t.updated_at,
                        t.id_tipo_escala,
                        te.nombre as nombre_escala,
                        te.descripcion as descripcion_escala
                      FROM Tests t
                      LEFT JOIN Tipos_Escalas te ON t.id_tipo_escala = te.id_tipo_escala
                      ORDER BY t.nombre ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Para cada test, obtener las opciones de su tipo de escala
            foreach ($tests as &$test) {
                $test['opciones'] = [];
                
                if (!empty($test['id_tipo_escala'])) {
                    try {
                        // Obtener opciones usando la tabla de mapeo TiposEscala_Opciones
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
                        $test['opciones'] = $stmtOpciones->fetchAll(PDO::FETCH_ASSOC);
                    } catch (PDOException $e) {
                        error_log("Error al obtener opciones para el test {$test['id_test']}: " . $e->getMessage());
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