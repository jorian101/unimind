<?php
/**
 * API Endpoint: Escuelas
 * Refactorizado con APIFacade + ModelFactory
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../utils/ModelFactory.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar escuelas
if ($method === 'GET') {
    APIFacade::execute(function() {
        $model = ModelFactory::createShared('escuelas');
        $escuelas = $model->getAll();
        APIFacade::sendSuccess($escuelas);
    });
}

// POST: Crear escuela
if ($method === 'POST' && isset($_POST['crear_escuela'])) {
    $params = APIFacade::validateParams(['nombre_escuela'], $_POST);
    
    APIFacade::execute(function() use ($params) {
        $conn = Database::getInstance()->getConnection();
        
        $telefono = $_POST['telefono'] ?? null;
        $stmt = $conn->prepare('CALL sp_crear_escuela(?, ?)');
        $stmt->execute([$params['nombre_escuela'], $telefono]);
        
        APIFacade::sendSuccess([], 'Escuela creada correctamente');
    });
}

// Acción no válida
APIFacade::sendError('Acción no válida', 400);
