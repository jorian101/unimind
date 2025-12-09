<?php
/**
 * UserController - Controlador para gestión de usuarios
 * Refactorizado con ModelFactory + UsuariosModel (MVC Pattern)
 */
require_once __DIR__ . '/../utils/ModelFactory.php';

class UserController {
    private $model;
    public $lastError = null;

    public function __construct() {
        // Usar ModelFactory para crear el modelo
        $this->model = ModelFactory::create('administrador', 'usuarios');
    }

    /**
     * Obtener todos los usuarios con filtros opcionales
     * 
     * @param string $cargo Filtro por cargo
     * @param string $busqueda Término de búsqueda
     * @return array Lista de usuarios
     */
    public function getUsuarios(string $cargo = '', string $busqueda = ''): array {
        try {
            if ($cargo || $busqueda) {
                return $this->model->buscarUsuarios($cargo, $busqueda);
            }
            return $this->model->getAll();
        } catch (Exception $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return [];
        }
    }

    /**
     * Obtener usuario por ID
     * 
     * @param int $id ID del usuario
     * @return array|null Datos del usuario
     */
    public function getUsuarioById(int $id): ?array {
        try {
            return $this->model->getById($id);
        } catch (Exception $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return null;
        }
    }

    /**
     * Obtener roles/cargos disponibles
     * 
     * @return array Lista de cargos
     */
    public function getCargosDisponibles(): array {
        return $this->model->getCargosDisponibles();
    }

    /**
     * Crear nuevo usuario
     * 
     * @param array $data Datos del usuario
     * @return array Resultado de la operación
     */
    public function crearUsuario(array $data): array {
        try {
            return $this->model->crearUsuario($data);
        } catch (Exception $e) {
            error_log("Error al crear usuario: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar usuario existente
     * 
     * @param int $id ID del usuario
     * @param array $data Datos a actualizar
     * @return array Resultado de la operación
     */
    public function actualizarUsuario(int $id, array $data): array {
        try {
            $success = $this->model->actualizarUsuario($id, $data);
            return [
                'success' => $success,
                'message' => $success ? 'Usuario actualizado correctamente' : 'Error al actualizar usuario'
            ];
        } catch (Exception $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Eliminar usuario
     * 
     * @param int $id ID del usuario a eliminar
     * @return array Resultado de la operación
     */
    public function eliminarUsuario(int $id): array {
        try {
            return $this->model->eliminarUsuario($id);
        } catch (Exception $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            $this->lastError = $e->getMessage();
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Autocompletado para búsqueda
     * 
     * @param string $query Término de búsqueda
     * @return array Lista de sugerencias
     */
    public function autocompletar(string $query): array {
        try {
            return $this->model->autocompletar($query);
        } catch (Exception $e) {
            error_log("Error en autocompletado: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Procesar solicitudes POST
     * (Mantiene compatibilidad con código antiguo si existe)
     * 
     * @return array Resultado de la operación
     */
    public function handlePost(): array {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'delete') {
            return $this->eliminarUsuario(intval($_POST['id']));
        }
        
        $id = $_POST['id'] ?? '';
        
        if ($id) {
            // Actualizar usuario
            return $this->actualizarUsuario(intval($id), [
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'codigo_usuario' => $_POST['codigo_usuario'] ?? '',
                'cargo' => $_POST['rol'] ?? $_POST['cargo'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                'genero' => $_POST['genero'] ?? null,
                'password' => $_POST['password'] ?? ''
            ]);
        } else {
            // Crear usuario
            return $this->crearUsuario([
                'nombre' => $_POST['nombre'] ?? '',
                'apellido' => $_POST['apellido'] ?? '',
                'cargo' => $_POST['rol'] ?? $_POST['cargo'] ?? '',
                'password' => $_POST['password'] ?? '',
                'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?? null,
                'genero' => $_POST['genero'] ?? null,
                'id_curso' => $_POST['id_curso'] ?? null,
                'id_escuela' => $_POST['id_escuela'] ?? null
            ]);
        }
    }

    /**
     * =================================================================
     * API METHODS - Manejan requests HTTP directamente
     * =================================================================
     */

    /**
     * Manejar API request GET para obtener usuario por ID
     */
    public function handleApiGetById(): void {
        $id = intval($_GET['id'] ?? 0);
        
        if ($id <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $usuario = $this->getUsuarioById($id);
            header('Content-Type: application/json; charset=utf-8');
            if ($usuario) {
                echo json_encode($usuario);
            } else {
                http_response_code(404);
                echo json_encode(null);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => 'Error al obtener usuario']);
            exit;
        }
    }

    /**
     * Manejar API request POST para editar usuario
     */
    public function handleApiEdit(): void {
        require_once __DIR__ . '/../utils/APIFacade.php';
        
        $params = APIFacade::validateParams([
            'editar_id_usuario', 'editar_nombre', 'editar_apellido', 
            'editar_codigo_usuario', 'editar_cargo'
        ], $_POST);
        
        try {
            $id = intval($params['editar_id_usuario']);
            
            $data = [
                'nombre' => $params['editar_nombre'],
                'apellido' => $params['editar_apellido'],
                'codigo_usuario' => $params['editar_codigo_usuario'],
                'cargo' => $params['editar_cargo'],
                'fecha_nacimiento' => $_POST['editar_fecha_nacimiento'] ?: null,
                'genero' => $_POST['editar_genero'] ?: null
            ];
            
            if (isset($_POST['editar_password']) && $_POST['editar_password'] !== '') {
                $data['password'] = $_POST['editar_password'];
            }
            
            $success = $this->model->actualizarUsuario($id, $data);
            
            header('Content-Type: application/json; charset=utf-8');
            if ($success) {
                echo json_encode([
                    'Mensaje' => 'Usuario actualizado correctamente',
                    'id_usuario' => $id
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Error al actualizar usuario']);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Manejar API request POST para crear usuario
     */
    public function handleApiCreate(): void {
        require_once __DIR__ . '/../utils/APIFacade.php';
        
        $params = APIFacade::validateParams([
            'nuevo_nombre', 'nuevo_apellido', 'nuevo_cargo', 'nuevo_password'
        ], $_POST);
        
        try {
            $data = [
                'nombre' => $params['nuevo_nombre'],
                'apellido' => $params['nuevo_apellido'],
                'cargo' => $params['nuevo_cargo'],
                'password' => $params['nuevo_password'],
                'fecha_nacimiento' => $_POST['nuevo_fecha_nacimiento'] ?: null,
                'genero' => $_POST['nuevo_genero'] ?: null,
                'id_escuela' => isset($_POST['nuevo_escuela']) && $_POST['nuevo_escuela'] !== '' 
                    ? intval($_POST['nuevo_escuela']) : null,
                'id_curso' => isset($_POST['nuevo_curso']) && $_POST['nuevo_curso'] !== '' 
                    ? intval($_POST['nuevo_curso']) : null
            ];

            $result = $this->crearUsuario($data);
            
            header('Content-Type: application/json; charset=utf-8');
            if ($result['success']) {
                echo json_encode([
                    'Mensaje' => 'Usuario creado correctamente', 
                    'Nuevo_ID_Usuario' => $result['id'],
                    'Nuevo_Codigo_Usuario' => $result['codigo']
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => $result['error'] ?? 'Error al crear usuario']);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Manejar API request POST para eliminar usuario
     */
    public function handleApiDelete(): void {
        $id = intval($_POST['eliminar_id_usuario'] ?? 0);
        
        if ($id <= 0) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(400);
            echo json_encode(['error' => 'ID inválido']);
            exit;
        }

        try {
            $result = $this->eliminarUsuario($id);
            
            header('Content-Type: application/json; charset=utf-8');
            if ($result['success']) {
                echo json_encode(['Mensaje' => $result['mensaje']]);
            } else {
                http_response_code(400);
                echo json_encode(['error' => $result['error']]);
            }
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Manejar API request GET para búsqueda/autocompletado
     */
    public function handleApiBuscar(): void {
        $q = trim($_GET['q'] ?? '');
        $cargo = $_GET['cargo'] ?? '';
        
        try {
            $usuarios = $this->getUsuarios($cargo, $q);
            
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($usuarios);
            exit;
        } catch (Exception $e) {
            header('Content-Type: application/json; charset=utf-8');
            http_response_code(500);
            echo json_encode(['error' => 'Error al buscar usuarios']);
            exit;
        }
    }

    /**
     * Router principal para API requests
     * Determina qué método API llamar basado en el request
     */
    public function handleApiRequest(): void {
        $method = $_SERVER['REQUEST_METHOD'];

        // GET: Obtener usuario por ID o buscar
        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $this->handleApiGetById();
            } elseif (isset($_GET['q'])) {
                $this->handleApiBuscar();
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => 'Parámetros inválidos']);
                exit;
            }
        }

        // POST: Crear, editar o eliminar
        if ($method === 'POST') {
            if (isset($_POST['editar_id_usuario'])) {
                $this->handleApiEdit();
            } elseif (isset($_POST['crear_usuario'])) {
                $this->handleApiCreate();
            } elseif (isset($_POST['eliminar_id_usuario'])) {
                $this->handleApiDelete();
            } else {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(400);
                echo json_encode(['error' => 'Acción no válida']);
                exit;
            }
        }
    }
}

// Si es petición POST, procesar y devolver JSON
// (Mantiene compatibilidad con código antiguo)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !defined('NO_AUTO_HANDLE')) {
    $controller = new UserController();
    $result = $controller->handlePost();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
