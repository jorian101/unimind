<?php
// INICIAR SESIÓN ANTES DE CUALQUIER OUTPUT
session_start();

require_once 'utils/SimpleRouter.php';

// Crear router global
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
    
    <!-- PWA Meta Tags -->
    <meta name="description" content="Sistema de evaluación y monitoreo de salud mental para estudiantes universitarios">
    <meta name="theme-color" content="#4a90e2">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="UniMind">
    
    <title>UniMind - <?php echo ucfirst($currentRole); ?></title>
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="/public/manifest.webmanifest">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/public/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="/public/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/public/icons/icon-512x512.png">
    <link rel="apple-touch-icon" href="/public/icons/icon-192x192.png">
    
    <!-- Stylesheets -->
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
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(registration => {
                        console.log('✅ Service Worker registrado correctamente:', registration.scope);
                        
                        // Verificar actualizaciones del SW
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    console.log('🔄 Nueva versión disponible. Recarga la página para actualizar.');
                                    // Opcional: mostrar notificación al usuario
                                    if (confirm('Nueva versión de UniMind disponible. ¿Deseas actualizar?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.error('❌ Error al registrar Service Worker:', error);
                    });
            });
            
            // Recargar página cuando el SW se actualiza
            let refreshing = false;
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                if (!refreshing) {
                    refreshing = true;
                    window.location.reload();
                }
            });
        } else {
            console.warn('⚠️ Service Worker no soportado en este navegador');
        }
    </script>
</body>
</html>