<?php
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();
header('Content-Type: application/json');

// Autocompletado y búsqueda en tiempo real
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['q'])) {
    $q = trim($_GET['q']);
    $cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';
    $sql = "SELECT * FROM Usuarios WHERE 1";
    $params = [];
    if ($cargo && in_array($cargo, ['Estudiante','Docente','Administrador'])) {
        $sql .= " AND cargo = ?";
        $params[] = $cargo;
    }
    if ($q) {
        $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR codigo_usuario LIKE ?)";
        $params[] = "%$q%";
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    $sql .= " ORDER BY fecha_registro DESC LIMIT 10";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($usuarios);
    exit;
}

// ...existing code for editar/eliminar...
