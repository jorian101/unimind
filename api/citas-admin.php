<?php
/**
 * API Endpoint: Citas Admin
 * Delegado a CitasController
 */
require_once __DIR__ . '/../controllers/CitasController.php';


$controller = new CitasController();

$method = $_SERVER['REQUEST_METHOD'];
if ($method === 'GET') {
	$controller->handleApiAdminGet();
	exit;
}

if ($method === 'POST') {
	header('Content-Type: application/json');
	$data = json_decode(file_get_contents('php://input'), true);
	$action = $_GET['action'] ?? '';
	try {
		if ($action === 'editar') {
			// Editar cita (admin)
			$id_cita = $data['id_cita'] ?? null;
			$id_alumno = $data['id_alumno'] ?? null;
			$fecha_cita = $data['fecha_cita'] ?? null;
			$motivo = $data['motivo'] ?? '';
			if (!$id_cita || !$id_alumno || !$fecha_cita || !$motivo) {
				echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
				exit;
			}
			$success = $controller->actualizarCita($id_cita, $id_alumno, $fecha_cita, $motivo);
			echo json_encode(['success' => $success, 'message' => $success ? 'Cita actualizada' : 'No se pudo actualizar']);
			exit;
		} elseif ($action === 'eliminar') {
			// Eliminar (cancelar) cita (admin)
			$id_cita = $data['id_cita'] ?? null;
			$id_alumno = $data['id_alumno'] ?? null;
			if (!$id_cita || !$id_alumno) {
				echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
				exit;
			}
			$success = $controller->cancelarCita($id_cita, $id_alumno);
			echo json_encode(['success' => $success, 'message' => $success ? 'Cita eliminada' : 'No se pudo eliminar']);
			exit;
		} elseif ($action === 'crear') {
			// Crear cita (admin)
			$id_alumno = $data['id_alumno'] ?? null;
			$fecha_cita = $data['fecha_cita'] ?? null;
			$motivo = $data['motivo'] ?? '';
			if (!$id_alumno || !$fecha_cita || !$motivo) {
				echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
				exit;
			}
			$result = $controller->agendarCita($id_alumno, $fecha_cita, $motivo);
			$id_cita_new = $result['id_cita'] ?? null;
			echo json_encode([
				'success' => (bool)$id_cita_new,
				'message' => $result['Mensaje'] ?? ($id_cita_new ? 'Cita creada' : 'No se pudo crear'),
				'id_cita' => $id_cita_new
			]);
			exit;
		} else {
			echo json_encode(['success' => false, 'message' => 'Acción no soportada']);
			exit;
		}
	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
		exit;
	}
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
