<?php
/**
 * NotificationsController
 * Controlador MVC para gestión de notificaciones
 */

require_once __DIR__ . '/../database/Database.php';

class NotificationsController {
    // ========================================
    // MÉTODOS API (para endpoints REST)
    // ========================================

    /**
     * API: GET notificaciones del usuario autenticado
     */
    public function handleApiGet(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
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
     * API: POST marcar como leída
     */
    public function handleApiPost(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');
        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        $userId = $_SESSION['id_usuario'];
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['id_notificacion'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta id_notificacion']);
            return;
        }
        $idNotificacion = (int)$input['id_notificacion'];
        if ($this->marcarComoLeida($idNotificacion, $userId)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo marcar como leída']);
        }
    }

    /**
     * API: DELETE eliminar notificación
     */
    public function handleApiDelete(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        header('Content-Type: application/json');
        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        $userId = $_SESSION['id_usuario'];
        // Obtener id_notificacion de la query string
        $idNotificacion = isset($_GET['id_notificacion']) ? (int)$_GET['id_notificacion'] : null;
        if (!$idNotificacion) {
            http_response_code(400);
            echo json_encode(['error' => 'Falta id_notificacion']);
            return;
        }
        $conn = Database::getInstance()->getConnection();
        $stmt = $conn->prepare('DELETE FROM Notificaciones WHERE id_notificacion = ? AND id_usuario = ?');
        if ($stmt->execute([$idNotificacion, $userId])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo eliminar la notificación']);
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
        if ($method === 'POST') {
            $this->handleApiPost();
            return;
        }
        if ($method === 'DELETE') {
            $this->handleApiDelete();
            return;
        }
        // Método no implementado
        http_response_code(405);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Método no permitido']);
    }

    // ========================================
    // MÉTODOS DE NEGOCIO (deben existir en el modelo real)
    // ========================================
    // Placeholder: implementa estos métodos en tu modelo real
    public function getNotificaciones($userId) {
        // Debe devolver array de notificaciones para el usuario
        // Implementa según tu modelo
        return [];
    }
    public function marcarComoLeida($idNotificacion, $userId) {
        // Debe marcar como leída la notificación
        // Implementa según tu modelo
        return true;
    }
}
