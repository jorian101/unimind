<?php
require_once __DIR__ . '/../BaseModel.php';

class ReportsModel extends BaseModel {
    protected function getTableName() {
        return 'Aplicaciones';
    }

    protected function getPrimaryKey() {
        return 'id_aplicacion';
    }

    public function getSummaryCounts() {
        try {
            $usuarios = (int) $this->conn->query('SELECT COUNT(*) FROM Usuarios')->fetchColumn();
            $cursos = (int) $this->conn->query('SELECT COUNT(*) FROM Cursos')->fetchColumn();
            $escuelas = (int) $this->conn->query('SELECT COUNT(*) FROM Escuelas')->fetchColumn();
            $tests = (int) $this->conn->query('SELECT COUNT(*) FROM Tests')->fetchColumn();

            return [
                'usuarios' => $usuarios,
                'cursos' => $cursos,
                'escuelas' => $escuelas,
                'tests' => $tests,
            ];
        } catch (PDOException $e) {
            error_log('ReportsModel::getSummaryCounts error: ' . $e->getMessage());
            return ['usuarios' => 0, 'cursos' => 0, 'escuelas' => 0, 'tests' => 0];
        }
    }

    public function getActividadReciente($limit = 10) {
        try {
            $stmt = $this->conn->prepare(
                'SELECT a.fecha_aplicacion AS fecha, CONCAT(u.nombre, " ", u.apellido) AS usuario, "Creó Test" AS accion, t.nombre AS detalle
                 FROM Aplicaciones a
                 JOIN Usuarios u ON a.id_usuario = u.id_usuario
                 JOIN Tests t ON a.id_test = t.id_test
                 ORDER BY a.fecha_aplicacion DESC
                 LIMIT :lim'
            );
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getActividadReciente error: ' . $e->getMessage());
            return [];
        }
    }

    public function getNivelesDistribucion() {
        try {
            $stmt = $this->conn->query(
                'SELECT resultado_nivel, COUNT(*) as total
                 FROM Aplicaciones
                 GROUP BY resultado_nivel'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getNivelesDistribucion error: ' . $e->getMessage());
            return [];
        }
    }

    public function getEscuelasRiesgo() {
        try {
            $stmt = $this->conn->query(
                'SELECT e.nombre_escuela,
                        AVG(a.puntuacion_total) as promedio_puntuacion,
                        SUM(CASE WHEN a.resultado_nivel = "Alto" THEN 1 ELSE 0 END) as casos_alto
                 FROM Aplicaciones a
                 JOIN Usuarios u ON a.id_usuario = u.id_usuario
                 JOIN Usuario_Escuela ue ON ue.id_usuario = u.id_usuario
                 JOIN Escuelas e ON ue.id_escuela = e.id_escuela
                 GROUP BY e.id_escuela
                 ORDER BY casos_alto DESC, promedio_puntuacion DESC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getEscuelasRiesgo error: ' . $e->getMessage());
            return [];
        }
    }

    public function getPuntuacionesMes() {
        try {
            $stmt = $this->conn->query(
                'SELECT DATE_FORMAT(fecha_aplicacion, "%Y-%m") as mes, AVG(puntuacion_total) as promedio
                 FROM Aplicaciones
                 GROUP BY mes
                 ORDER BY mes ASC'
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getPuntuacionesMes error: ' . $e->getMessage());
            return [];
        }
    }
}

?>