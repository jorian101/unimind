<?php
/**
 * API Endpoint: Notificaciones
 * Delegado a NotificationsController
 */
require_once __DIR__ . '/../controllers/NotificationsController.php';

$controller = new NotificationsController();
$controller->handleApiRequest();
