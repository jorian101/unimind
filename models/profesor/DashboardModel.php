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
    
    public function sugerirTestACurso($id_curso, $id_test) {
        try {
            $stmt = $this->conn->prepare("CALL sp_sugerir_test_a_curso(:p_id_curso, :p_id_test)");
            $stmt->bindParam(':p_id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->bindParam(':p_id_test', $id_test, PDO::PARAM_INT);
            $stmt->execute();
            $stmt->closeCursor();
            return true;
        } catch(PDOException $e) {
            // Manejar error (ej. log)
            return false;
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
}
?>