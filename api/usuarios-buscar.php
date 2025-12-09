<?php
/**
 * API Endpoint: Búsqueda de Usuarios
 * Refactorizado con APIFacade + Database Singleton
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

// GET: Autocompletado y búsqueda en tiempo real
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    APIFacade::execute(function() {
        $conn = Database::getInstance()->getConnection();
        
        $q = trim($_GET['q']);
        $cargo = $_GET['cargo'] ?? '';
        
        $sql = "SELECT * FROM Usuarios WHERE 1";
        $params = [];
        
        if ($cargo && in_array($cargo, ['Estudiante','Docente','Administrador'])) {
            $sql .= " AND cargo = ?";
            $params[] = $cargo;
        }
        
        if ($q) {
            $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR codigo_usuario LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }
        
        $sql .= " ORDER BY fecha_registro DESC LIMIT 50";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        APIFacade::sendSuccess($usuarios);
    });
}

// Acción no válida
APIFacade::sendError('Acción no válida', 400);
