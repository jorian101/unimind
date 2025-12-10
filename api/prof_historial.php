<?php
/**
 * API Endpoint: Historial de Sugerencias Profesor
 * Delegado a ProfesorController
 */
require_once __DIR__ . '/../controllers/ProfesorController.php';

$controller = new ProfesorController();
$controller->handleApiHistorial();
