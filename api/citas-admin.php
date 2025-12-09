<?php
/**
 * API Endpoint: Citas Admin
 * Delegado a CitasController
 */
require_once __DIR__ . '/../controllers/CitasController.php';

$controller = new CitasController();
$controller->handleApiAdminGet();
