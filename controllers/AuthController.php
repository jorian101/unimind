<?php
session_start();
require_once __DIR__ . '/../database/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_usuario = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($codigo_usuario) || empty($password)) {
        header('Location: ../index.php?error=campos_vacios');
        exit;
    }

    try {
        // Conectar a la base de datos
        $database = new Database();
        $conn = $database->connect();

        $stmt = $conn->prepare("CALL sp_autenticar_usuario_por_codigo(:codigo_usuario)");
        $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor(); 

        if ($usuario && $password === $usuario['password']) {
            $_SESSION['user_id'] = $usuario['id_usuario'];
            $_SESSION['user_name'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
            $_SESSION['user_role'] = strtolower($usuario['cargo']);

            $role = strtolower($usuario['cargo']);
            if ($role === 'estudiante') {
                header('Location: ../index.php?role=estudiante&page=inicio');
            } elseif ($role === 'profesor') {
                header('Location: ../index.php?role=profesor&page=dashboard');
            } elseif ($role === 'admin') {
                header('Location: ../index.php?role=administrador&page=dashboard');
            } else {
                header('Location: ../index.php?error=rol_invalido');
            }
            exit;
        } else {
            header('Location: ../index.php?error=credenciales_invalidas');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Error de autenticación: " . $e->getMessage());
        header('Location: ../index.php?error=error_servidor');
        exit;
    }
}
?>
