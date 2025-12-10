<?php
/**
 * API Endpoint: Búsqueda de Usuarios
 * Delega al UserController (MVC Pattern)
 */
require_once __DIR__ . '/../controllers/UserController.php';

// Crear instancia del controller y delegar búsqueda
$controller = new UserController();
$controller->handleApiBuscar();
