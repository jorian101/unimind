<?php
/**
 * CursosController
 * Controlador MVC para gestión de cursos
 */

require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/ModelFactory.php';

class CursosController {
    private $model;

    public function __construct() {
        $this->model = ModelFactory::create('administrador', 'cursos');
    }

    // ========================================
    // MÉTODOS MVC (para vistas)
    // ========================================

    /**
     * Obtener todos los cursos o filtrados por escuela
     */
    public function getCursos(?int $escuelaId = null): array {
        if ($escuelaId !== null) {
            return $this->model->getCursosByEscuela($escuelaId);
        }
        return $this->model->getAll();
    }

    /**
     * Crear nuevo curso
     */
    public function crearCurso(string $nombre, int $idEscuela, int $idProfesor): bool {
        return $this->model->crear($nombre, $idEscuela, $idProfesor);
    }

    // ========================================
    // MÉTODOS API (para endpoints REST)
    // ========================================

    /**
     * GET: Listar cursos (opcionalmente filtrados por escuela)
     * Query params: ?escuela_id=X
     */
    public function handleApiGet(): void {
        header('Content-Type: application/json');
        
        try {
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
            echo json_encode($cursos);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener cursos: ' . $e->getMessage()]);
        }
    }

    /**
     * POST: Crear curso
     * Body: crear_curso=1, nombre_curso=X, id_escuela=Y, id_profesor=Z
     */
    public function handleApiCreate(): void {
        header('Content-Type: application/json');
        
        // Validar parámetros requeridos
        if (!isset($_POST['nombre_curso']) || !isset($_POST['id_escuela']) || !isset($_POST['id_profesor'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Parámetros faltantes: nombre_curso, id_escuela, id_profesor']);
            return;
        }

        try {
            $conn = Database::getInstance()->getConnection();
            
            $stmt = $conn->prepare('CALL sp_crear_curso(?, ?, ?)');
            $stmt->execute([
                $_POST['nombre_curso'],
                intval($_POST['id_escuela']),
                intval($_POST['id_profesor'])
            ]);
            
            echo json_encode(['Mensaje' => 'Curso creado correctamente']);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error al crear curso: ' . $e->getMessage()]);
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

        if ($method === 'POST' && isset($_POST['crear_curso'])) {
            $this->handleApiCreate();
            return;
        }

        // Acción no válida
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Acción no válida']);
    }
}
