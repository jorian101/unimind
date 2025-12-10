
<?php
/**
 * API Endpoint: Cursos
 * Delegado a CursosController
 *
 * Extiende: Si GET ?action=tests_disponibles, responde con los tests activos para el modal de sugerir test
 */
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/administrador/TestsModel.php';

if (isset($_GET['action']) && $_GET['action'] === 'tests_disponibles') {
	header('Content-Type: application/json');
	try {
		$model = new TestsModel();
		$tests = $model->getAllTests();
		// Solo tests activos
		$tests = array_filter($tests, function($t) {
			return ($t['estado_test'] ?? $t['estado'] ?? 'activo') === 'activo';
		});
		$tests = array_map(function($t) {
			return [
				'id_test' => (int)$t['id_test'],
				'nombre' => $t['nombre']
			];
		}, $tests);
		echo json_encode(['success' => true, 'tests' => array_values($tests)]);
	} catch (Exception $e) {
		http_response_code(500);
		echo json_encode(['success' => false, 'error' => 'Error al obtener tests: ' . $e->getMessage()]);
	}
	exit;
}

require_once __DIR__ . '/../controllers/CursosController.php';
$controller = new CursosController();
$controller->handleApiRequest();
