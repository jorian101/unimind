<?php
session_start();
require_once __DIR__ . '/../models/estudiante/TestsEstudianteModel.php';

// Responder siempre con JSON
header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No estás autenticado. Por favor, inicia sesión.',
        'redirect' => '../index.php?role=estudiante&page=login'
    ]);
    exit;
}

// Verificar método de envío
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
    exit;
}

try {
    $testId = $_POST['test_id'] ?? null;
    $testName = $_POST['test_name'] ?? '';
    
    if (!$testId) {
        throw new Exception('ID de test no proporcionado');
    }
    
    $model = new TestsEstudianteModel();
    $id_usuario = $_SESSION['id_usuario'];
    
    // Recopilar todas las respuestas
    $respuestas = [];
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'item_') === 0) {
            $id_item = str_replace('item_', '', $key);
            $id_opcion = (int)$value;
            $respuestas[$id_item] = $id_opcion;
        }
    }
    
    if (empty($respuestas)) {
        throw new Exception('No se encontraron respuestas');
    }
    
    // 1. Iniciar la aplicación
    $id_aplicacion = $model->iniciarAplicacion($id_usuario, $testId);
    
    if (!$id_aplicacion) {
        throw new Exception('Error al crear la aplicación del test');
    }
    
    // 2. Registrar cada respuesta
    $errores = [];
    foreach ($respuestas as $id_item => $id_opcion) {
        $success = $model->registrarRespuesta($id_aplicacion, $id_item, $id_opcion);
        if (!$success) {
            $errores[] = "Error al registrar respuesta del item $id_item";
        }
    }
    
    if (!empty($errores)) {
        error_log("Errores al guardar respuestas: " . implode(', ', $errores));
        // Continuar de todas formas si al menos algunas se guardaron
    }
    
    // 3. Finalizar y calcular puntuación
    $resultado = $model->finalizarAplicacion($id_aplicacion);
    
    if (!$resultado) {
        throw new Exception('Error al calcular la puntuación del test');
    }
    
    // Guardar resultado en sesión para mostrarlo cuando sea posible
    $puntuacion_total = $resultado['Puntuacion_Final'] ?? 0;
    $resultado_nivel = $resultado['Nivel_Resultado'] ?? 'No determinado';
    $completed_at = date('Y-m-d H:i:s');

    $_SESSION['test_resultado'] = [
        'id_aplicacion' => $id_aplicacion,
        'test_name' => $testName,
        'puntuacion_total' => $puntuacion_total,
        'resultado_nivel' => $resultado_nivel,
        'completed_at' => $completed_at
    ];

    // Responder con JSON para que el cliente maneje la redirección
    echo json_encode([
        'success' => true,
        'message' => 'Test completado exitosamente',
        'data' => [
            'test_name' => $testName,
            'score' => $puntuacion_total,
            'level' => $resultado_nivel,
            'completed_at' => $completed_at
        ]
    ]);
    exit;
    
} catch (Exception $e) {
    error_log("Error en submit-test.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al procesar el test: ' . $e->getMessage()
    ]);
    exit;
}
?>
