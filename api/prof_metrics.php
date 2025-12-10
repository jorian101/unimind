<?php
/**
 * API Endpoint: Métricas de Profesor
 * Delegado a ProfesorController
 */
require_once __DIR__ . '/../controllers/ProfesorController.php';

$controller = new ProfesorController();
$controller->handleApiMetricsRequest();
