<?php
require_once __DIR__ . '/../models/administrador/TestsModel.php';

class TestsController {
    private $model;

    public function __construct() {
        $this->model = new TestsModel();
    }

    /**
     * Manejar las peticiones según la acción
     */
    public function handleRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'getAll':
                    $this->getAllTests();
                    break;
                
                case 'getById':
                    $this->getTestById();
                    break;
                
                case 'getOpciones':
                    $this->getAllOpciones();
                    break;
                
                case 'getTiposEscalas':
                    $this->getTiposEscalas();
                    break;
                
                case 'getTipos':
                    $this->getTipos();
                    break;
                
                case 'getOpcionesByTipoEscala':
                    $this->getOpcionesByTipoEscala();
                    break;
                
                case 'create':
                    $this->createTest();
                    break;
                
                case 'update':
                    $this->updateTest();
                    break;
                
                case 'delete':
                    $this->deleteTest();
                    break;
                
                case 'search':
                    $this->searchTests();
                    break;
                
                case 'createTipoEscala':
                    $this->createTipoEscala();
                    break;
                
                default:
                    $this->sendResponse(false, 'Acción no válida');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error del servidor: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los tests
     */
    private function getAllTests() {
        $tests = $this->model->getAllTests();
        $this->sendResponse(true, 'Tests obtenidos correctamente', $tests);
    }

    /**
     * Obtener un test por ID
     */
    private function getTestById() {
        $id_test = $_GET['id_test'] ?? null;
        
        if (!$id_test) {
            $this->sendResponse(false, 'ID de test no proporcionado');
            return;
        }

        $test = $this->model->getTestById($id_test);
        
        if ($test) {
            $this->sendResponse(true, 'Test obtenido correctamente', $test);
        } else {
            $this->sendResponse(false, 'Test no encontrado');
        }
    }

    /**
     * Obtener todas las opciones de respuesta
     */
    private function getAllOpciones() {
        $opciones = $this->model->getAllOpciones();
        $this->sendResponse(true, 'Opciones obtenidas correctamente', $opciones);
    }

    /**
     * Obtener todos los tipos de escalas
     */
    private function getTiposEscalas() {
           $escalas = $this->model->getTiposEscalas();
           $this->sendResponse(true, 'Tipos de escalas obtenidos correctamente', $escalas);
    }

    /**
     * Obtener opciones de respuesta por tipo de escala
     */
    private function getOpcionesByTipoEscala() {
        $tipo_escala = $_GET['tipo_escala'] ?? null;
        
        if (!$tipo_escala) {
            $this->sendResponse(false, 'Tipo de escala no proporcionado');
            return;
        }

        $opciones = $this->model->getOpcionesByTipoEscala($tipo_escala);
        $this->sendResponse(true, 'Opciones obtenidas correctamente', $opciones);
    }

    /**
     * Obtener lista simple de tipos (sin opciones) - fallback para frontend
     */
    private function getTipos() {
        $tipos = $this->model->getTiposSimple();
        $this->sendResponse(true, 'Tipos obtenidos correctamente', $tipos);
    }

    /**
     * Crear un nuevo test con sus items
     */
    private function createTest() {
        // Validar datos requeridos
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $num_items = $_POST['num_items'] ?? 0;
        $tipo_escala = $_POST['tipo_escala'] ?? 1; // Por defecto escala de frecuencia
        $items = json_decode($_POST['items'] ?? '[]', true);

        // Si no vienen por form-data, intentar leer JSON raw (usado por PWA offline sync)
        if ((empty($nombre) || empty($descripcion) || $num_items == 0) &&
            strpos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $nombre = $json['nombre'] ?? $nombre;
                $descripcion = $json['descripcion'] ?? $descripcion;
                $num_items = $json['num_items'] ?? $num_items;
                $tipo_escala = $json['tipo_escala'] ?? $tipo_escala;
                $items = $json['items'] ?? $items;
            }
        }

        if (!$nombre || !$descripcion || $num_items <= 0 || !$tipo_escala) {
            $this->sendResponse(false, 'Datos incompletos o inválidos');
            return;
        }

        // Validar que el número de items coincida
        if (count($items) != $num_items) {
            $this->sendResponse(false, 'El número de ítems no coincide con el valor especificado');
            return;
        }

        // Crear el test
        $id_test = $this->model->createTest($nombre, $descripcion, $num_items, $tipo_escala);
        
        if (!$id_test) {
            $err = $this->model->lastError ?? null;
            $msg = 'Error al crear el test' . ($err ? (': ' . $err) : '');
            $this->sendResponse(false, $msg);
            return;
        }

        // Crear los items
        $errores = [];
        foreach ($items as $item) {
            $texto_item = $item['texto_item'] ?? '';
            $subescala = $item['subescala'] ?? '';
            $orden = $item['orden'] ?? 0;

            if (!$texto_item || $orden <= 0) {
                $errores[] = "Item {$orden}: datos incompletos";
                continue;
            }

            $result = $this->model->createItem($id_test, $texto_item, $subescala, $orden);
            if (!$result) {
                $errores[] = "Error al crear item {$orden}";
            }
        }

        if (!empty($errores)) {
            $this->sendResponse(false, 'Test creado pero con errores en algunos items', [
                'id_test' => $id_test,
                'errores' => $errores
            ]);
        } else {
            $this->sendResponse(true, 'Test creado exitosamente', ['id_test' => $id_test]);
        }
    }

    /**
     * Actualizar un test existente con sus items
     */
    private function updateTest() {
        $id_test = $_POST['id_test'] ?? null;
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? null;
        $num_items = $_POST['num_items'] ?? 0;
        $tipo_escala = $_POST['tipo_escala'] ?? 1;
        $items = json_decode($_POST['items'] ?? '[]', true);

        if (!$id_test || !$nombre || !$descripcion || $num_items <= 0 || !$tipo_escala) {
            $this->sendResponse(false, 'Datos incompletos o inválidos');
            return;
        }

        // Validar que el número de items coincida
        if (count($items) != $num_items) {
            $this->sendResponse(false, 'El número de ítems no coincide con el valor especificado');
            return;
        }

        // Actualizar el test
        $result = $this->model->updateTest($id_test, $nombre, $descripcion, $num_items, $tipo_escala);
        
        if (!$result) {
            $this->sendResponse(false, 'Error al actualizar el test');
            return;
        }

        // Eliminar items anteriores y crear los nuevos
        $this->model->deleteItemsByTestId($id_test);

        $errores = [];
        foreach ($items as $item) {
            $texto_item = $item['texto_item'] ?? '';
            $subescala = $item['subescala'] ?? '';
            $orden = $item['orden'] ?? 0;

            if (!$texto_item || $orden <= 0) {
                $errores[] = "Item {$orden}: datos incompletos";
                continue;
            }

            $result = $this->model->createItem($id_test, $texto_item, $subescala, $orden);
            if (!$result) {
                $errores[] = "Error al crear item {$orden}";
            }
        }

        if (!empty($errores)) {
            $this->sendResponse(false, 'Test actualizado pero con errores en algunos items', [
                'id_test' => $id_test,
                'errores' => $errores
            ]);
        } else {
            $this->sendResponse(true, 'Test actualizado exitosamente', ['id_test' => $id_test]);
        }
    }

    /**
     * Eliminar un test
     */
    private function deleteTest() {
        $id_test = $_POST['id_test'] ?? null;

        if (!$id_test) {
            $this->sendResponse(false, 'ID de test no proporcionado');
            return;
        }

        // Verificar si tiene aplicaciones
        if ($this->model->testHasApplications($id_test)) {
            $this->sendResponse(false, 'No se puede eliminar el test porque tiene aplicaciones registradas');
            return;
        }

        $result = $this->model->deleteTest($id_test);
        
        if ($result) {
            $this->sendResponse(true, 'Test eliminado exitosamente');
        } else {
            $this->sendResponse(false, 'Error al eliminar el test');
        }
    }

    /**
     * Buscar tests
     */
    private function searchTests() {
        $searchTerm = $_GET['search'] ?? '';
        
        if (empty($searchTerm)) {
            $this->getAllTests();
            return;
        }

        $tests = $this->model->searchTests($searchTerm);
        $this->sendResponse(true, 'Búsqueda completada', $tests);
    }

    /**
     * Crear un nuevo tipo de escala con sus opciones
     */
    private function createTipoEscala() {
        $nombre = $_POST['nombre'] ?? null;
        $descripcion = $_POST['descripcion'] ?? '';
        $opciones = json_decode($_POST['opciones'] ?? '[]', true);

        // Validar datos requeridos
        if (!$nombre) {
            $this->sendResponse(false, 'El nombre de la escala es requerido');
            return;
        }

        if (!is_array($opciones) || count($opciones) < 2) {
            $this->sendResponse(false, 'Debes proporcionar al menos 2 opciones de respuesta');
            return;
        }

        // Validar que cada opción tenga texto y valor
        foreach ($opciones as $opcion) {
            if (empty($opcion['texto_opcion']) || !isset($opcion['valor_puntuacion'])) {
                $this->sendResponse(false, 'Cada opción debe tener texto y valor de puntuación');
                return;
            }
        }

        // Crear el tipo de escala
        $id_tipo_escala = $this->model->createTipoEscala($nombre, $descripcion);
        
        if (!$id_tipo_escala) {
            $this->sendResponse(false, 'Error al crear el tipo de escala');
            return;
        }

        // Crear las opciones de respuesta y vincularlas con la escala
        $opciones_ids = [];
        $errores = [];

        foreach ($opciones as $opcion) {
            $texto = $opcion['texto_opcion'];
            $valor = $opcion['valor_puntuacion'];

            // Crear la opción de respuesta
            $id_opcion = $this->model->createOpcionRespuesta($texto, $valor);
            
            if (!$id_opcion) {
                $errores[] = "Error al crear opción: {$texto}";
                continue;
            }

            $opciones_ids[] = $id_opcion;

            // Vincular la opción con el tipo de escala
            $vinculado = $this->model->vincularOpcionConEscala($id_tipo_escala, $id_opcion);
            
            if (!$vinculado) {
                $errores[] = "Error al vincular opción: {$texto}";
            }
        }

        if (!empty($errores)) {
            $this->sendResponse(false, 'Escala creada pero con errores en algunas opciones', [
                'id_tipo_escala' => $id_tipo_escala,
                'opciones_ids' => $opciones_ids,
                'errores' => $errores
            ]);
        } else {
            $this->sendResponse(true, 'Escala creada exitosamente', [
                'id_tipo_escala' => $id_tipo_escala,
                'opciones_ids' => $opciones_ids
            ]);
        }
    }

    /**
     * Enviar respuesta JSON
     */
    private function sendResponse($success, $message, $data = null) {
        $response = [
            'success' => $success,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        echo json_encode($response);
        exit;
    }
}

// Procesar la petición si se accede directamente
if (basename($_SERVER['PHP_SELF']) === 'TestsController.php') {
    $controller = new TestsController();
    $controller->handleRequest();
}
?>
