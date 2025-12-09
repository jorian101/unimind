<?php
/**
 * API Endpoint: Citas Estudiante
 * Delegado a CitasController
 */
require_once __DIR__ . '/../controllers/CitasController.php';

$controller = new CitasController();
$controller->handleApiEstudianteRequest();
