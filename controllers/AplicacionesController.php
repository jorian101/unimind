<?php
session_start();
require_once __DIR__ . '/../models/estudiante/TestsEstudianteModel.php';

class AplicacionesController {
    private $model;

    public function __construct() {
        $this->model = new TestsEstudianteModel();
    }

    /**
     * Manejar las peticiones según la acción
     */
    public function handleRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'getTestsDisponibles':
                    $this->getTestsDisponibles();
                    break;
                case 'getTestsSugeridos':
                    $this->getTestsSugeridos();
                    break;
                
                case 'getTestData':
                    $this->getTestData();
                    break;
                
                case 'iniciarTest':
                    $this->iniciarTest();
                    break;
                
                case 'enviarRespuestas':
                    $this->enviarRespuestas();
                    break;
                
                case 'getHistorial':
                    $this->getHistorial();
                    break;
                
                case 'getResultado':
                    $this->getResultado();
                    break;
                
                case 'getDetalleAplicacion':
                    $this->getDetalleAplicacion();
                    break;
                
                default:
                    $this->sendResponse(false, 'Acción no válida');
            }
        } catch (Exception $e) {
            $this->sendResponse(false, 'Error del servidor: ' . $e->getMessage());
        }
    }

    /**
     * Obtener todos los tests disponibles
     */
    private function getTestsDisponibles() {
        if (!isset($_SESSION['id_usuario'])) {
            $this->sendResponse(false, 'Usuario no autenticado');
            return;
        }
        
        $id_usuario = $_SESSION['id_usuario'];
        $tests = $this->model->getTestsDisponibles($id_usuario);
        
        // Los tests ya vienen con las banderas es_sugerido y completado desde el SP
        // Asegurar que las banderas estén como booleanos para el frontend
        foreach ($tests as &$test) {
            $test['es_sugerido'] = (bool)($test['es_sugerido'] ?? false);
            $test['completado'] = (bool)($test['completado'] ?? false);
            
            // Convertir campos numéricos
            $test['id_test'] = (int)$test['id_test'];
            $test['num_items'] = (int)$test['num_items'];
            
            // Si tiene sugerencia, incluir información adicional
            if ($test['es_sugerido']) {
                $test['id_sugerencia'] = isset($test['id_sugerencia']) ? (int)$test['id_sugerencia'] : null;
            }
        }
        
        $this->sendResponse(true, 'Tests obtenidos correctamente', $tests);
    }

    /**
     * Obtener solo los tests sugeridos por los profesores para el estudiante
     */
    private function getTestsSugeridos() {
        if (!isset($_SESSION['id_usuario'])) {
            $this->sendResponse(false, 'Usuario no autenticado');
            return;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $tests = $this->model->getTestsSugeridos($id_usuario);

        foreach ($tests as &$test) {
            $test['es_sugerido'] = true; // vienen como sugeridos
            $test['completado'] = (bool)($test['completado'] ?? false);
            $test['id_test'] = isset($test['id_test']) ? (int)$test['id_test'] : null;
            $test['num_items'] = isset($test['num_items']) ? (int)$test['num_items'] : 0;
            if ($test['es_sugerido']) {
                $test['id_sugerencia'] = isset($test['id_sugerencia']) ? (int)$test['id_sugerencia'] : null;
            }
        }

        $this->sendResponse(true, 'Tests sugeridos obtenidos correctamente', $tests);
    }

    /**
     * Obtener datos completos de un test (info, items y opciones)
     */
    private function getTestData() {
        $id_test = $_GET['id_test'] ?? null;
        
        if (!$id_test) {
            $this->sendResponse(false, 'ID de test no proporcionado');
            return;
        }

        $test = $this->model->getTestById($id_test);
        if (!$test) {
            $this->sendResponse(false, 'Test no encontrado');
            return;
        }

        $items = $this->model->getItemsByTest($id_test);
        $opciones = $this->model->getOpcionesRespuesta();

        $data = [
            'test' => $test,
            'items' => $items,
            'opciones' => $opciones
        ];

        $this->sendResponse(true, 'Datos del test obtenidos correctamente', $data);
    }

    /**
     * Iniciar una nueva aplicación de test
     */
    private function iniciarTest() {
        if (!isset($_SESSION['id_usuario'])) {
            $this->sendResponse(false, 'Usuario no autenticado');
            return;
        }

        $id_test = $_POST['id_test'] ?? null;
        
        if (!$id_test) {
            $this->sendResponse(false, 'ID de test no proporcionado');
            return;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $id_aplicacion = $this->model->iniciarAplicacion($id_usuario, $id_test);

        if ($id_aplicacion) {
            $_SESSION['id_aplicacion_activa'] = $id_aplicacion;
            $this->sendResponse(true, 'Test iniciado correctamente', ['id_aplicacion' => $id_aplicacion]);
        } else {
            $this->sendResponse(false, 'Error al iniciar el test');
        }
    }

    /**
     * Enviar todas las respuestas y finalizar el test
     */
    private function enviarRespuestas() {
        if (!isset($_SESSION['id_usuario'])) {
            $this->sendResponse(false, 'Usuario no autenticado');
            return;
        }

        $id_test = $_POST['id_test'] ?? null;
        $respuestas = $_POST['respuestas'] ?? null;

        if (!$id_test || !$respuestas) {
            $this->sendResponse(false, 'Datos incompletos');
            return;
        }

        // Decodificar respuestas si vienen como JSON
        if (is_string($respuestas)) {
            $respuestas = json_decode($respuestas, true);
        }

        // Iniciar la aplicación
        $id_usuario = $_SESSION['id_usuario'];
        $id_aplicacion = $this->model->iniciarAplicacion($id_usuario, $id_test);

        if (!$id_aplicacion) {
            $this->sendResponse(false, 'Error al crear la aplicación');
            return;
        }

        // Registrar cada respuesta
        $errores = [];
        foreach ($respuestas as $id_item => $id_opcion) {
            $success = $this->model->registrarRespuesta($id_aplicacion, $id_item, $id_opcion);
            if (!$success) {
                $errores[] = "Error al registrar respuesta del item $id_item";
            }
        }

        if (!empty($errores)) {
            $this->sendResponse(false, 'Algunos errores al guardar respuestas', ['errores' => $errores]);
            return;
        }

        // Finalizar y calcular puntuación
        $resultado = $this->model->finalizarAplicacion($id_aplicacion);

        if ($resultado) {
            $this->sendResponse(true, 'Test completado exitosamente', [
                'id_aplicacion' => $id_aplicacion,
                'resultado' => $resultado
            ]);
        } else {
            $this->sendResponse(false, 'Error al calcular la puntuación');
        }
    }

    /**
     * Obtener historial de tests del usuario
     */
    private function getHistorial() {
        if (!isset($_SESSION['id_usuario'])) {
            $this->sendResponse(false, 'Usuario no autenticado');
            return;
        }

        $id_usuario = $_SESSION['id_usuario'];
        $historial = $this->model->getHistorialUsuario($id_usuario);
        
        $this->sendResponse(true, 'Historial obtenido correctamente', $historial);
    }

    /**
     * Obtener resultado de una aplicación específica
     */
    private function getResultado() {
        $id_aplicacion = $_GET['id_aplicacion'] ?? null;
        
        if (!$id_aplicacion) {
            $this->sendResponse(false, 'ID de aplicación no proporcionado');
            return;
        }

        $resultado = $this->model->getResultadoAplicacion($id_aplicacion);
        $detalle = $this->model->getDetalleAplicacion($id_aplicacion);

        if ($resultado) {
            $data = [
                'resultado' => $resultado,
                'detalle' => $detalle
            ];
            $this->sendResponse(true, 'Resultado obtenido correctamente', $data);
        } else {
            $this->sendResponse(false, 'Resultado no encontrado');
        }
    }

    /**
     * Obtener detalle completo de una aplicación con respuestas
     */
    private function getDetalleAplicacion() {
        $id_aplicacion = $_GET['id_aplicacion'] ?? null;
        
        if (!$id_aplicacion) {
            $this->sendResponse(false, 'ID de aplicación no proporcionado');
            return;
        }

        // Obtener resultado general
        $resultado = $this->model->getResultadoAplicacion($id_aplicacion);
        
        // Obtener respuestas detalladas
        $respuestas = $this->model->getDetalleAplicacion($id_aplicacion);

        if ($resultado) {
            $data = [
                'resultado' => $resultado,
                'respuestas' => $respuestas
            ];
            $this->sendResponse(true, 'Detalles obtenidos correctamente', $data);
        } else {
            $this->sendResponse(false, 'Aplicación no encontrada');
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

// Instanciar y manejar la petición
$controller = new AplicacionesController();
$controller->handleRequest();
?>
