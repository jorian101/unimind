<?php
/**
 * API Endpoint: Usuarios
 * Delega todas las operaciones al UserController (MVC Pattern)
 */
require_once __DIR__ . '/../controllers/UserController.php';

// Crear instancia del controller y delegar el request
$controller = new UserController();
$controller->handleApiRequest();
