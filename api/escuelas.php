<?php
/**
 * API Endpoint: Escuelas
 * Delegado a EscuelasController
 */
require_once __DIR__ . '/../controllers/EscuelasController.php';

$controller = new EscuelasController();
$controller->handleApiRequest();
