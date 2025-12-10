<?php
/**
 * RecomendacionesController
 * Controlador MVC para gestión de recomendaciones (administrador)
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../models/administrador/RecomendacionesModel.php';

class RecomendacionesController {
    private $model;

    public function __construct() {
        $this->model = new RecomendacionesModel();
    }
    
    /**
     * Verificar autenticación y rol de administrador
     */
    private function requireAdminAuth(): array {
        session_start();
        
        if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['user_role'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado', 'authenticated' => false]);
            exit;
        }

        $role = strtolower($_SESSION['user_role']);
        if ($role !== 'administrador' && $role !== 'admin') {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acceso denegado. Solo administradores pueden acceder.']);
            exit;
        }

        return [
            'user_id' => $_SESSION['id_usuario'],
            'role' => $_SESSION['user_role']
        ];
    }

    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener todas las recomendaciones
     */
    public function getRecomendaciones(): array {
        return $this->model->getAll();
    }

    /**
     * Obtener recomendaciones activas
     */
    public function getRecomendacionesActivas(): array {
        return $this->model->getAllActive();
    }

    /**
     * Obtener recomendación por ID
     */
    public function getRecomendacion(int $id): ?array {
        return $this->model->getById($id);
    }

    /**
     * Obtener estadísticas de recomendaciones
     */
    public function getEstadisticas(): array {
        return $this->model->getEstadisticas();
    }

    /**
     * Obtener recomendaciones para un estudiante específico
     */
    public function getRecomendacionesParaEstudiante(int $idEstudiante): array {
        return $this->model->getRecomendacionesParaEstudiante($idEstudiante);
    }

    // ========================================
    // MÉTODOS API - ADMINISTRADOR
    // ========================================

    /**
     * API Admin: GET listar todas las recomendaciones
     */
    public function handleApiGet(): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        try {
            // Si hay filtros
            if (isset($_GET['categoria'])) {
                $recomendaciones = $this->model->getByCategoria($_GET['categoria']);
            } elseif (isset($_GET['magnitud'])) {
                $recomendaciones = $this->model->getByMagnitud((int)$_GET['magnitud']);
            } elseif (isset($_GET['search'])) {
                $recomendaciones = $this->model->search($_GET['search']);
            } elseif (isset($_GET['activas']) && $_GET['activas'] === 'true') {
                $recomendaciones = $this->model->getAllActive();
            } else {
                $recomendaciones = $this->model->getAll();
            }
            
            echo json_encode([
                'success' => true,
                'recomendaciones' => $recomendaciones
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener recomendaciones: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Admin: GET obtener estadísticas
     */
    public function handleApiGetEstadisticas(): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        try {
            $estadisticas = $this->getEstadisticas();
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener estadísticas'
            ]);
        }
    }

    /**
     * API Admin: POST crear nueva recomendación
     */
    public function handleApiCreate(): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (empty($data['titulo']) || empty($data['descripcion']) || empty($data['categoria'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos incompletos. Se requieren: titulo, descripcion, categoria'
            ]);
            return;
        }
        
        try {
            $result = $this->model->create($data);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Recomendación creada exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo crear la recomendación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al crear recomendación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Admin: PUT actualizar recomendación
     */
    public function handleApiUpdate(int $id): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (empty($data['titulo']) || empty($data['descripcion']) || empty($data['categoria'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Datos incompletos'
            ]);
            return;
        }
        
        try {
            $result = $this->model->update($id, $data);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Recomendación actualizada exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo actualizar la recomendación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al actualizar recomendación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Admin: DELETE eliminar recomendación
     */
    public function handleApiDelete(int $id): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        try {
            $result = $this->model->delete($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Recomendación eliminada exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo eliminar la recomendación'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al eliminar recomendación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API Admin: PATCH toggle activa/inactiva
     */
    public function handleApiToggle(int $id): void {
        $this->requireAdminAuth();
        header('Content-Type: application/json');
        
        try {
            $result = $this->model->toggleActiva($id);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado actualizado exitosamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'error' => 'No se pudo actualizar el estado'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al actualizar estado: ' . $e->getMessage()
            ]);
        }
    }

    // ========================================
    // MÉTODOS API - ESTUDIANTE
    // ========================================

    /**
     * API Estudiante: GET obtener recomendaciones personalizadas
     */
    public function handleApiEstudianteGet(): void {
        session_start();
        
        if (!isset($_SESSION['id_usuario'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }

        header('Content-Type: application/json');
        
        try {
            $idEstudiante = $_SESSION['id_usuario'];
            $recomendaciones = $this->getRecomendacionesParaEstudiante($idEstudiante);
            
            echo json_encode([
                'success' => true,
                'recomendaciones' => $recomendaciones
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener recomendaciones personalizadas'
            ]);
        }
    }
}
?>
