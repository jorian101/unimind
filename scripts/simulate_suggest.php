<?php
// Simple script to simulate a professor suggesting a test for a course
// Usage: php simulate_suggest.php <id_curso> "Test name" [num_items]
if ($argc < 3) {
    echo "Usage: php simulate_suggest.php <id_curso> \"Test name\" [num_items]\n";
    exit(1);
}

$id_curso = (int)$argv[1];
$test_name = $argv[2];
$num_items = isset($argv[3]) ? (int)$argv[3] : 5;

require_once __DIR__ . '/../database/Database.php';
$db = new Database();
$conn = $db->connect();

// choose a professor id from DB (first professor) or use 1
$prof = 1;

// create test if not exists
$chk = $conn->prepare('SELECT id_test FROM Tests WHERE nombre = :nombre LIMIT 1');
$chk->execute([':nombre' => $test_name]);
$row = $chk->fetch(PDO::FETCH_ASSOC);
if ($row) {
    $id_test = (int)$row['id_test'];
    echo "Using existing test id: $id_test\n";
} else {
    $ins = $conn->prepare('INSERT INTO Tests (nombre, descripcion, num_items) VALUES (:nombre, :descripcion, :num_items)');
    $ins->execute([':nombre' => $test_name, ':descripcion' => 'Demo test', ':num_items' => $num_items]);
    $id_test = (int)$conn->lastInsertId();
    echo "Created test id: $id_test\n";
}

// get students in course
$stmt = $conn->prepare('SELECT id_usuario FROM Usuario_Curso WHERE id_curso = :id_curso');
$stmt->execute([':id_curso' => $id_curso]);
$students = $stmt->fetchAll(PDO::FETCH_COLUMN);
if (!$students) {
    echo "No students found for course $id_curso\n";
    exit(1);
}

$insApp = $conn->prepare('INSERT INTO Aplicaciones (id_usuario, id_test, fecha_aplicacion, sugerido_por, origen) VALUES (:id_usuario, :id_test, NOW(), :sugerido_por, :origen)');
$insNot = $conn->prepare('INSERT INTO Notificaciones (id_usuario_destino, mensaje, metadata, leido, creado_en) VALUES (:id_usuario_destino, :mensaje, :metadata, 0, NOW())');

$count = 0;
foreach ($students as $stu) {
    // skip if pending exists
    $chk2 = $conn->prepare('SELECT id_aplicacion FROM Aplicaciones WHERE id_usuario = :id_usuario AND id_test = :id_test AND resultado_nivel IS NULL LIMIT 1');
    $chk2->execute([':id_usuario' => $stu, ':id_test' => $id_test]);
    if ($chk2->fetch()) continue;
    $insApp->execute([':id_usuario' => $stu, ':id_test' => $id_test, ':sugerido_por' => $prof, ':origen' => 'simulacion']);
    $insNot->execute([':id_usuario_destino' => $stu, ':mensaje' => 'Profesor sugiere test: '.$test_name, ':metadata' => json_encode(['id_test' => $id_test, 'id_curso' => $id_curso])]);
    $count++;
}

echo "Inserted $count aplicaciones and notifications.\n";
