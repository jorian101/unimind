<?php
/**
 * API Endpoint: Notificaciones
 * Refactorizado con APIFacade pattern
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

// Verificar autenticación usando Facade
$userId = APIFacade::requireAuth();

// Ejecutar operación con manejo de excepciones
APIFacade::execute(function() use ($userId) {
    $conn = Database::getInstance()->getConnection();
    
    // GET: listar notificaciones (unread first)
    $stmt = $conn->prepare(
        'SELECT id_notificacion, mensaje, metadata, leido, creado_en 
         FROM Notificaciones 
         WHERE id_usuario_destino = :id_usuario 
         ORDER BY leido ASC, creado_en DESC 
         LIMIT 50'
    );
    $stmt->execute([':id_usuario' => $userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar respuesta exitosa
    APIFacade::sendSuccess(['notifications' => $notifications]);
});

?>
