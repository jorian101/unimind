<?php
/**
 * API Endpoint: Recomendaciones
 * Delegado a RecomendacionesController
 * 
 * Rutas:
 * GET    /api/recomendaciones.php              - Listar recomendaciones (admin)
 * GET    /api/recomendaciones.php?stats=true   - Obtener estadísticas (admin)
 * POST   /api/recomendaciones.php              - Crear recomendación (admin)
 * PUT    /api/recomendaciones.php?id=X         - Actualizar recomendación (admin)
 * DELETE /api/recomendaciones.php?id=X         - Eliminar recomendación (admin)
 * PATCH  /api/recomendaciones.php?id=X&action=toggle - Toggle activa/inactiva (admin)
 * 
 * GET    /api/recomendaciones.php?estudiante=true - Recomendaciones personalizadas (estudiante)
 */

require_once __DIR__ . '/../controllers/RecomendacionesController.php';

// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$controller = new RecomendacionesController();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Estudiante: recomendaciones personalizadas
            if (isset($_GET['estudiante']) && $_GET['estudiante'] === 'true') {
                $controller->handleApiEstudianteGet();
            }
            // Admin: estadísticas
            elseif (isset($_GET['stats']) && $_GET['stats'] === 'true') {
                $controller->handleApiGetEstadisticas();
            }
            // Admin: listar todas
            else {
                $controller->handleApiGet();
            }
            break;
            
        case 'POST':
            // Admin: crear nueva recomendación
            $controller->handleApiCreate();
            break;
            
        case 'PUT':
            // Admin: actualizar recomendación
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $controller->handleApiUpdate($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'DELETE':
            // Admin: eliminar recomendación
            if (isset($_GET['id'])) {
                $id = (int)$_GET['id'];
                $controller->handleApiDelete($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID requerido']);
            }
            break;
            
        case 'PATCH':
            // Admin: toggle activa/inactiva
            if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'toggle') {
                $id = (int)$_GET['id'];
                $controller->handleApiToggle($id);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'ID y action=toggle requeridos']);
            }
            break;
            
        default:
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Método no permitido']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>
