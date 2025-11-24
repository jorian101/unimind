<?php
// Endpoint JSON para métricas de profesor
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'message' => '', 'courses' => []];

// Verificar autenticación y rol
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    http_response_code(401);
    $response['message'] = 'No autenticado';
    echo json_encode($response);
    exit;
}

if (strtolower($_SESSION['user_role']) !== 'docente' && strtolower($_SESSION['user_role']) !== 'teacher' && strtolower($_SESSION['user_role']) !== 'docente') {
    http_response_code(403);
    $response['message'] = 'Acceso no autorizado';
    echo json_encode($response);
    exit;
}

$profesorId = (int) $_SESSION['user_id'];

try {
    $db = new Database();
    $conn = $db->connect();

    // Obtener cursos asignados al profesor
    $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = :id_profesor');
    $stmt->bindParam(':id_profesor', $profesorId, PDO::PARAM_INT);
    $stmt->execute();
    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cursos as $curso) {
        $id_curso = (int) $curso['id_curso'];

        // Cantidad de alumnos inscritos
        $stmt = $conn->prepare('SELECT COUNT(*) as total_students FROM Usuario_Curso WHERE id_curso = :id_curso');
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $totalStudents = (int) $stmt->fetchColumn();

        // Promedio y distribución para tests que mencionen 'estres' o 'ansiedad'
        $sqlAvg = "SELECT AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                   JOIN Tests t ON a.id_test = t.id_test
                   JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                   WHERE uc.id_curso = :id_curso AND (LOWER(t.nombre) LIKE '%estres%' OR LOWER(t.nombre) LIKE '%ansiedad%')";
        $stmt = $conn->prepare($sqlAvg);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $avgScore = $stmt->fetchColumn();
        $avgScore = $avgScore !== null ? round((float)$avgScore, 1) : null;

        // Distribución por nivel basada en resultado_nivel si existe
        $sqlDist = "SELECT a.resultado_nivel as nivel, COUNT(*) as cnt FROM Aplicaciones a
                    JOIN Tests t ON a.id_test = t.id_test
                    JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                    WHERE uc.id_curso = :id_curso AND (LOWER(t.nombre) LIKE '%estres%' OR LOWER(t.nombre) LIKE '%ansiedad%')
                    GROUP BY a.resultado_nivel";
        $stmt = $conn->prepare($sqlDist);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $distribution = ['Bajo' => 0, 'Moderado' => 0, 'Alto' => 0];
        foreach ($rows as $r) {
            $nivel = $r['nivel'] ?? '';
            $cnt = (int) $r['cnt'];
            if (stripos($nivel, 'bajo') !== false) $distribution['Bajo'] += $cnt;
            elseif (stripos($nivel, 'moderado') !== false) $distribution['Moderado'] += $cnt;
            elseif (stripos($nivel, 'alto') !== false) $distribution['Alto'] += $cnt;
            else {
                // si no hay texto, clasificar por umbrales simples: (puntuacion <30 low, <70 mod, else high)
            }
        }

        // Si no hay distribución por texto, intentar por puntuaciones
        $totalDist = array_sum($distribution);
        if ($totalDist === 0 && $totalStudents > 0) {
            $sqlScoreDist = "SELECT a.puntuacion_total as score FROM Aplicaciones a
                              JOIN Tests t ON a.id_test = t.id_test
                              JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                              WHERE uc.id_curso = :id_curso AND (LOWER(t.nombre) LIKE '%estres%' OR LOWER(t.nombre) LIKE '%ansiedad%')";
            $stmt = $conn->prepare($sqlScoreDist);
            $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
            $stmt->execute();
            $scores = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($scores as $s) {
                $s = (float)$s;
                if ($s < 30) $distribution['Bajo']++;
                elseif ($s < 70) $distribution['Moderado']++;
                else $distribution['Alto']++;
            }
        }

        // Series temporal (últimos 8 días) promedio por fecha
        $sqlSeries = "SELECT DATE(a.fecha_aplicacion) as fecha, AVG(a.puntuacion_total) as avg_score FROM Aplicaciones a
                      JOIN Tests t ON a.id_test = t.id_test
                      JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                      WHERE uc.id_curso = :id_curso AND (LOWER(t.nombre) LIKE '%estres%' OR LOWER(t.nombre) LIKE '%ansiedad%')
                      GROUP BY DATE(a.fecha_aplicacion)
                      ORDER BY DATE(a.fecha_aplicacion) ASC
                      LIMIT 12";
        $stmt = $conn->prepare($sqlSeries);
        $stmt->bindParam(':id_curso', $id_curso, PDO::PARAM_INT);
        $stmt->execute();
        $seriesRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $series = [];
        foreach ($seriesRows as $sr) {
            $series[] = ['date' => $sr['fecha'], 'value' => (float) round($sr['avg_score'],1)];
        }

        $response['courses'][] = [
            'id_curso' => $id_curso,
            'nombre_curso' => $curso['nombre_curso'],
            'total_students' => $totalStudents,
            'avg_score' => $avgScore,
            'distribution' => $distribution,
            'series' => $series
        ];
    }

    // Agregar métricas agregadas por facultad/escuela donde el profesor enseña
    $stmt = $conn->prepare('SELECT DISTINCT e.id_escuela, e.nombre_escuela
                            FROM Escuelas e
                            JOIN Cursos c ON c.id_escuela = e.id_escuela
                            WHERE c.id_profesor = :id_profesor');
    $stmt->bindParam(':id_profesor', $profesorId, PDO::PARAM_INT);
    $stmt->execute();
    $facultades = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['faculties'] = [];
    foreach ($facultades as $fac) {
        $id_esc = (int) $fac['id_escuela'];

        // Promedio de puntuación para tests de estrés en la facultad
        $sqlFacEst = "SELECT AVG(a.puntuacion_total) as avg_score, COUNT(*) as cnt FROM Aplicaciones a
                   JOIN Tests t ON a.id_test = t.id_test
                   JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                   JOIN Cursos c ON uc.id_curso = c.id_curso
                   WHERE c.id_escuela = :id_esc AND LOWER(t.nombre) LIKE '%estres%'";
        $stmt = $conn->prepare($sqlFacEst);
        $stmt->bindParam(':id_esc', $id_esc, PDO::PARAM_INT);
        $stmt->execute();
        $facEst = $stmt->fetch(PDO::FETCH_ASSOC);

        // Promedio de puntuación para tests de ansiedad en la facultad
        $sqlFacAns = "SELECT AVG(a.puntuacion_total) as avg_score, COUNT(*) as cnt FROM Aplicaciones a
                   JOIN Tests t ON a.id_test = t.id_test
                   JOIN Usuario_Curso uc ON a.id_usuario = uc.id_usuario
                   JOIN Cursos c ON uc.id_curso = c.id_curso
                   WHERE c.id_escuela = :id_esc AND LOWER(t.nombre) LIKE '%ansiedad%'";
        $stmt = $conn->prepare($sqlFacAns);
        $stmt->bindParam(':id_esc', $id_esc, PDO::PARAM_INT);
        $stmt->execute();
        $facAns = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['faculties'][] = [
            'id_escuela' => $id_esc,
            'nombre_escuela' => $fac['nombre_escuela'],
            'avg_estres' => $facEst['avg_score'] !== null ? round((float)$facEst['avg_score'],1) : null,
            'count_estres' => (int) ($facEst['cnt'] ?? 0),
            'avg_ansiedad' => $facAns['avg_score'] !== null ? round((float)$facAns['avg_score'],1) : null,
            'count_ansiedad' => (int) ($facAns['cnt'] ?? 0),
        ];
    }

    $response['success'] = true;
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    http_response_code(500);
    $response['message'] = 'Error de servidor: ' . $e->getMessage();
    echo json_encode($response);
    exit;
}

?>
