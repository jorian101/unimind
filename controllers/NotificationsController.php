<?php
/**
 * NotificationsController
 * Controlador MVC para gestión de notificaciones
 */

require_once __DIR__ . '/../database/Database.php';

class NotificationsController {
    
    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener notificaciones de un usuario
     */
    public function getNotificaciones(int $userId, int $limit = 50): array {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare(
            'SELECT id_notificacion, mensaje, metadata, leido, creado_en 
             FROM Notificaciones 
             WHERE id_usuario_destino = ? 
             ORDER BY leido ASC, creado_en DESC 
             LIMIT ?'
        );
        $stmt->execute([$userId, $limit]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida(int $idNotificacion, int $userId): bool {
        $conn = Database::getInstance()->getConnection();
        
        $stmt = $conn->prepare(
            'UPDATE Notificaciones 
             SET leido = 1 
             WHERE id_notificacion = ? AND id_usuario_destino = ?'
        );
        
        return $stmt->execute([$idNotificacion, $userId]);
    }

    // ========================================
    // MÉTODOS API (para endpoints REST)
    // ========================================

    /**
     * API: GET notificaciones del usuario autenticado
     */
    public function handleApiGet(): void {
        session_start();
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        
        $userId = $_SESSION['id_usuario'];
        
        try {
            $notifications = $this->getNotificaciones($userId);
            echo json_encode(['notifications' => $notifications]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener notificaciones: ' . $e->getMessage()]);
        }
    }

    /**
     * Router principal para requests API
     */
    public function handleApiRequest(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->handleApiGet();
            return;
        }

        // Método no implementado
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido']);
    }
}
