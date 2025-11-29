<?php
// api/citas-admin.php
header('Content-Type: application/json');
require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();

// Recibe fecha en formato YYYY-MM-DD
$fecha = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');

$stmt = $conn->prepare('
    SELECT c.id_cita, c.fecha_cita, c.motivo, c.estado, u.nombre, u.apellido
    FROM Citas c
    JOIN Usuarios u ON c.id_alumno = u.id_usuario
    WHERE DATE(c.fecha_cita) = ?
    ORDER BY c.fecha_cita ASC
');
$stmt->execute([$fecha]);
$citas = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $citas[] = [
        'id' => $row['id_cita'],
        'title' => $row['nombre'] . ' ' . $row['apellido'],
        'start' => $row['fecha_cita'],
        'motivo' => $row['motivo'],
        'estado' => $row['estado']
    ];
}
echo json_encode($citas);
