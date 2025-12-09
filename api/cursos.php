<?php
/**
 * API Endpoint: Cursos
 * Delegado a CursosController
 */
require_once __DIR__ . '/../controllers/CursosController.php';

$controller = new CursosController();
$controller->handleApiRequest();
