<?php
session_start();

// Solo permitir en localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('No autorizado');
}

require_once __DIR__ . '/database/Database.php';
require_once __DIR__ . '/models/estudiante/TestsEstudianteModel.php';

echo "<h1>Debug de Historial</h1>";
echo "<pre>";

// Mostrar sesión
echo "=== SESIÓN ===\n";
echo "Session ID: " . session_id() . "\n";
echo "id_usuario: " . ($_SESSION['id_usuario'] ?? 'NO DEFINIDO') . "\n";
echo "user_id: " . ($_SESSION['user_id'] ?? 'NO DEFINIDO') . "\n";
echo "user_name: " . ($_SESSION['user_name'] ?? 'NO DEFINIDO') . "\n";
echo "user_role: " . ($_SESSION['user_role'] ?? 'NO DEFINIDO') . "\n\n";

// Obtener ID de usuario
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if ($id_usuario) {
    echo "=== HISTORIAL PARA USUARIO $id_usuario ===\n";
    $model = new TestsEstudianteModel();
    $historial = $model->getHistorialUsuario($id_usuario);
    
    echo "Total de aplicaciones: " . count($historial) . "\n\n";
    
    if (count($historial) > 0) {
        echo "Aplicaciones:\n";
        foreach ($historial as $item) {
            print_r($item);
            echo "\n";
        }
    } else {
        echo "No hay aplicaciones en el historial.\n";
    }
    
    // Verificar directamente en la BD
    echo "\n=== VERIFICACIÓN DIRECTA EN BD ===\n";
    $database = new Database();
    $conn = $database->connect();
    $stmt = $conn->prepare("SELECT * FROM Aplicaciones WHERE id_usuario = :id_usuario");
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    $aplicaciones = $stmt->fetchAll();
    
    echo "Total de aplicaciones en BD: " . count($aplicaciones) . "\n\n";
    if (count($aplicaciones) > 0) {
        foreach ($aplicaciones as $app) {
            print_r($app);
            echo "\n";
        }
    }
} else {
    echo "NO HAY USUARIO LOGUEADO\n";
}

echo "</pre>";
?>
