<?php
/**
 * API Endpoint: Sugerencias de Tests (Profesores)
 * Delegado a SugerenciasController
 */
require_once __DIR__ . '/../controllers/SugerenciasController.php';

$controller = new SugerenciasController();
$controller->handleApiRequest();
