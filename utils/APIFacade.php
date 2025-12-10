<?php
/**
 * APIFacade - Facade Pattern
 * 
 * Proporciona una interfaz unificada para operaciones comunes de API:
 * - Autenticación
 * - Validación de sesión
 * - Respuestas JSON estandarizadas
 * - Manejo de errores
 * 
 * Elimina código duplicado en endpoints API
 */
class APIFacade {
    /**
     * Verificar autenticación de usuario
     * @return array ['authenticated' => bool, 'user_id' => int|null]
     */
    public static function checkAuth() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['id_usuario'] ?? null;
        
        return [
            'authenticated' => $userId !== null,
            'user_id' => $userId ? (int)$userId : null,
            'role' => $_SESSION['user_role'] ?? $_SESSION['id_rol'] ?? null
        ];
    }

    /**
     * Requerir autenticación - responde 401 si no autenticado
     * @return int User ID si autenticado
     */
    public static function requireAuth() {
        $auth = self::checkAuth();
        
        if (!$auth['authenticated']) {
            self::sendUnauthorized();
        }
        
        return $auth['user_id'];
    }

    /**
     * Enviar respuesta JSON exitosa
     */
    public static function sendSuccess($data = [], $message = '') {
        self::sendJSON([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    /**
     * Enviar respuesta JSON de error
     */
    public static function sendError($message, $code = 400) {
        self::sendJSON([
            'success' => false,
            'message' => $message
        ], $code);
    }

    /**
     * Enviar respuesta 401 Unauthorized
     */
    public static function sendUnauthorized($message = 'No autorizado') {
        self::sendError($message, 401);
    }

    /**
     * Enviar respuesta 404 Not Found
     */
    public static function sendNotFound($message = 'Recurso no encontrado') {
        self::sendError($message, 404);
    }

    /**
     * Enviar respuesta 500 Internal Server Error
     */
    public static function sendServerError($message = 'Error del servidor') {
        self::sendError($message, 500);
    }

    /**
     * Enviar respuesta JSON genérica
     */
    private static function sendJSON($data, $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    /**
     * Validar parámetros requeridos en request
     * @param array $params Parámetros a validar
     * @param array $source Fuente ($_POST, $_GET, etc.)
     * @return array Parámetros validados
     */
    public static function validateParams($params, $source = null) {
        if ($source === null) {
            $source = array_merge($_GET, $_POST);
        }

        $validated = [];
        $missing = [];

        foreach ($params as $param) {
            if (isset($source[$param]) && $source[$param] !== '') {
                $validated[$param] = $source[$param];
            } else {
                $missing[] = $param;
            }
        }

        if (!empty($missing)) {
            self::sendError('Parámetros faltantes: ' . implode(', ', $missing), 400);
        }

        return $validated;
    }

    /**
     * Ejecutar operación con manejo de excepciones
     * @param callable $callback Operación a ejecutar
     */
    public static function execute($callback) {
        try {
            $result = $callback();
            
            if ($result === false) {
                self::sendServerError();
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("API Error (PDO): " . $e->getMessage());
            self::sendServerError('Error de base de datos');
        } catch (Exception $e) {
            error_log("API Error: " . $e->getMessage());
            self::sendServerError();
        }
    }

    /**
     * Obtener datos JSON del body (para PUT, PATCH, etc.)
     */
    public static function getJsonBody() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            self::sendError('JSON inválido', 400);
        }
        
        return $data ?? [];
    }

    /**
     * Sanitizar entrada de usuario
     */
    public static function sanitize($value) {
        if (is_array($value)) {
            return array_map([self::class, 'sanitize'], $value);
        }
        
        return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Log de actividad de API
     */
    public static function logActivity($action, $details = []) {
        $auth = self::checkAuth();
        
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $auth['user_id'],
            'action' => $action,
            'details' => $details,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        error_log("API Activity: " . json_encode($logEntry));
    }
}
?>
