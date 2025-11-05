<?php
require_once 'SimpleRouter.php';

header('Content-Type: text/html; charset=utf-8');

$router = new SimpleRouter();

ob_start();
$router->loadPage();
$pageContent = ob_get_clean();

echo '<div class="page-content-wrapper">' . $pageContent . '</div>';
?>