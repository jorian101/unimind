<?php
require_once 'utils/Router.php';

$router = new Router();
$currentRole = $router->getCurrentRole();
$currentPage = $router->getCurrentPage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMind - <?php echo ucfirst($currentRole); ?></title>
    <link rel="stylesheet" href="public/css/style.css">
    <link rel="stylesheet" href="public/css/theme.css">
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'views/layout.php'; ?>
        
        <main class="main-content" id="main-content">
            <!-- Page Header dentro del contenido -->
            <?php include 'views/page-header.php'; ?>
            
            <!-- Contenido de la página -->
            <div class="page-content-wrapper">
                <?php echo $router->renderPage(); ?>
            </div>
        </main>
    </div>
</body>
</html>