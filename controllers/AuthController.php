<?php
/**
 * AuthController - Refactorizado con Strategy Pattern
 * 
 * Usa AuthStrategy para manejar redirecciones basadas en roles
 * Desacopla lógica de autenticación de lógica de redirección
 */
session_start();
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../utils/AuthStrategy.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $codigo_usuario = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validar campos vacíos
    if (empty($codigo_usuario) || empty($password)) {
        header('Location: ../index.php?error=campos_vacios');
        exit;
    }

    try {
        // Validar credenciales usando AuthHelper
        $usuario = AuthHelper::validateCredentials($codigo_usuario, $password);

        if ($usuario) {
            // Configurar sesión
            AuthHelper::setupSession($usuario);

            // Crear contexto de autenticación con Strategy pattern
            $authContext = AuthenticationContext::createFromRole($usuario['cargo']);

            if ($authContext) {
                // Redirigir usando la estrategia apropiada
                $authContext->redirect();
            } else {
                header('Location: ../index.php?error=rol_invalido');
                exit;
            }
        } else {
            header('Location: ../index.php?error=credenciales_invalidas');
            exit;
        }
    } catch (Exception $e) {
        error_log("Error de autenticación: " . $e->getMessage());
        header('Location: ../index.php?error=error_servidor');
        exit;
    }
}
?>

