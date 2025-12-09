<?php
/**
 * API Endpoint: Cursos
 * Refactorizado con APIFacade + Database Singleton
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

$method = $_SERVER['REQUEST_METHOD'];

// GET: Listar cursos (opcionalmente filtrar por escuela)
if ($method === 'GET') {
    APIFacade::execute(function() {
        $conn = Database::getInstance()->getConnection();
        
        if (isset($_GET['escuela_id']) && $_GET['escuela_id'] !== '') {
            $id_escuela = intval($_GET['escuela_id']);
            $stmt = $conn->prepare(
                'SELECT id_curso, nombre_curso AS nombre, id_escuela, id_profesor 
                 FROM Cursos 
                 WHERE id_escuela = ? 
                 ORDER BY nombre_curso'
            );
            $stmt->execute([$id_escuela]);
        } else {
            $stmt = $conn->prepare(
                'SELECT id_curso, nombre_curso AS nombre, id_escuela, id_profesor 
                 FROM Cursos 
                 ORDER BY nombre_curso'
            );
            $stmt->execute();
        }
        
        $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        APIFacade::sendSuccess($cursos);
    });
}

// POST: Crear curso
if ($method === 'POST' && isset($_POST['crear_curso'])) {
    $params = APIFacade::validateParams(['nombre_curso', 'id_escuela', 'id_profesor'], $_POST);
    
    APIFacade::execute(function() use ($params) {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare('CALL sp_crear_curso(?, ?, ?)');
        $stmt->execute([
            $params['nombre_curso'],
            intval($params['id_escuela']),
            intval($params['id_profesor'])
        ]);
        
        APIFacade::sendSuccess([], 'Curso creado correctamente');
    });
}

// Acción no válida
APIFacade::sendError('Acción no válida', 400);
