<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // TODO: Validar contra base de datos
    // Por ahora, redirigir a la página de inicio del estudiante
    header('Location: ../index.php?role=estudiante&page=inicio');
    exit;
}
?>
