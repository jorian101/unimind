<?php
require_once 'utils/SimpleRouter.php';

$router = new SimpleRouter();
$currentRole = $router->getCurrentRole();
$currentPage = $router->getCurrentPage();

// Si es la página de login, cargar directamente sin layout
if ($currentRole === 'autenticacion' && $currentPage === 'login') {
    include 'views/autenticacion/login.php';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniMind - <?php echo ucfirst($currentRole); ?></title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="public/css/style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="public/css/theme.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="layout-wrapper">
        <?php include 'views/layout.php'; ?>
        
        <main class="main-content" id="main-content">
            <!-- Contenido de la página con header incluido -->
            <div class="page-content-wrapper">
                <?php $router->loadPage(); ?>
            </div>
        </main>
    </div>
</body>
</html>