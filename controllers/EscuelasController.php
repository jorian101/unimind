<?php
/**
 * EscuelasController
 * Controlador MVC para gestión de escuelas
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/ModelFactory.php';

class EscuelasController {
    private $model;

    public function __construct() {
        $this->model = ModelFactory::create('administrador', 'escuelas');
    }

    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener todas las escuelas
     */
    public function getEscuelas(): array {
        return $this->model->getAll();
    }

    /**
     * Crear nueva escuela
     */
    public function crearEscuela(string $nombre, ?string $telefono = null): bool {
        return $this->model->crear($nombre, $telefono);
    }

    // ========================================
    // MÉTODOS API (para endpoints REST)
    // ========================================

    /**
     * GET: Listar escuelas
     */
    public function handleApiGet(): void {
        header('Content-Type: application/json');
        
        try {
            $escuelas = $this->model->getAll();
            echo json_encode($escuelas);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener escuelas: ' . $e->getMessage()]);
        }
    }

    /**
     * POST: Crear escuela
     * Body: crear_escuela=1, nombre_escuela=X, telefono=Y (opcional)
     */
    public function handleApiCreate(): void {
        header('Content-Type: application/json');
        
        // Validar parámetros requeridos
        if (!isset($_POST['nombre_escuela'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetro faltante: nombre_escuela']);
            return;
        }

        try {
            $conn = Database::getInstance()->getConnection();
            
            $telefono = $_POST['telefono'] ?? null;
            $stmt = $conn->prepare('CALL sp_crear_escuela(?, ?)');
            $stmt->execute([$_POST['nombre_escuela'], $telefono]);
            
            echo json_encode(['Mensaje' => 'Escuela creada correctamente']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear escuela: ' . $e->getMessage()]);
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

        if ($method === 'POST' && isset($_POST['crear_escuela'])) {
            $this->handleApiCreate();
            return;
        }

        // Acción no válida
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no válida']);
    }
}
