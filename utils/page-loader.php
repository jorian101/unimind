<?php
require_once '../utils/Router.php';

// Este archivo maneja las peticiones AJAX para cargar contenido sin recargar página
header('Content-Type: text/html; charset=utf-8');

$router = new Router();
echo $router->renderPage();
?>