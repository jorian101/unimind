<?php
// API para agendar cita por estudiante
require_once __DIR__ . '/../models/estudiante/TestsEstudianteModel.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}
$id_alumno = $_SESSION['id_usuario'];
$model = new TestsEstudianteModel();
$pdo = $model->getConn();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'list') {
    // Listar citas del alumno
    try {
        $stmt = $pdo->prepare("CALL sp_obtener_citas_por_alumno(:id_alumno)");
        $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
        $stmt->execute();
        $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        echo json_encode($citas);
    } catch (PDOException $e) {
        echo json_encode([]);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $action = $_GET['action'] ?? null;
    if ($action === 'editar') {
        $id_cita = $data['id_cita'] ?? null;
        $fecha_cita = $data['fecha_cita'] ?? null;
        $motivo = $data['motivo'] ?? '';
        if (!$id_cita || !$fecha_cita || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE Citas SET fecha_cita = :fecha_cita, motivo = :motivo WHERE id_cita = :id_cita AND id_alumno = :id_alumno AND estado != 'cancelada'");
            $stmt->bindParam(':fecha_cita', $fecha_cita);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cita actualizada']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar cita: ' . $e->getMessage()]);
        }
        exit;
    } elseif ($action === 'cancelar') {
        $id_cita = $data['id_cita'] ?? null;
        if (!$id_cita) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("UPDATE Citas SET estado = 'cancelada' WHERE id_cita = :id_cita AND id_alumno = :id_alumno AND estado != 'cancelada'");
            $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cita cancelada']);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cancelar cita: ' . $e->getMessage()]);
        }
        exit;
    } else {
        // Solicitar cita (agendar)
        $fecha_cita = $data['fecha_cita'] ?? null;
        $motivo = $data['motivo'] ?? '';
        if (!$fecha_cita || !$motivo) {
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        try {
            $stmt = $pdo->prepare("CALL sp_agendar_cita(:id_alumno, :fecha_cita, :motivo)");
            $stmt->bindParam(':id_alumno', $id_alumno, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_cita', $fecha_cita);
            $stmt->bindParam(':motivo', $motivo);
            $stmt->execute();
            $result = $stmt->fetch();
            $stmt->closeCursor();
            echo json_encode(['success' => true, 'message' => $result['Mensaje'] ?? 'Cita agendada', 'id_cita' => $result['id_cita'] ?? null]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error al agendar cita: ' . $e->getMessage()]);
        }
        exit;
    }
}
// Si no es POST, rechazar
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
exit;
