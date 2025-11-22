<?php
session_start();
require_once __DIR__ . '/../models/estudiante/TestsEstudianteModel.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ../index.php?role=estudiante&page=login');
    exit;
}

// Verificar método de envío
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?role=estudiante&page=tests');
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
    
    // Guardar resultado en sesión para mostrarlo
    $_SESSION['test_resultado'] = [
        'id_aplicacion' => $id_aplicacion,
        'test_name' => $testName,
        'puntuacion_total' => $resultado['Puntuacion_Final'] ?? 0,
        'resultado_nivel' => $resultado['Nivel_Resultado'] ?? 'No determinado',
        'completed_at' => date('Y-m-d H:i:s')
    ];
    
    // Redirigir a página de historial con mensaje de éxito
    header('Location: ../index.php?role=estudiante&page=historial&success=1&id_aplicacion=' . $id_aplicacion);
    exit;
    
} catch (Exception $e) {
    error_log("Error en submit-test.php: " . $e->getMessage());
    $_SESSION['error_message'] = 'Error al procesar el test: ' . $e->getMessage();
    header('Location: ../index.php?role=estudiante&page=tests&error=1');
    exit;
}
?>
