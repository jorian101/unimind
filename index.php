<?php
// INICIAR SESIÓN ANTES DE CUALQUIER OUTPUT
session_start();

// Mostrar errores en entornos locales para depuración (solo localhost)
if ((isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require_once 'utils/SimpleRouter.php';

// Crear router global
$router = new SimpleRouter();
$currentRole = $router->getCurrentRole();
$currentPage = $router->getCurrentPage();

// Base URL dinámico (por ejemplo: '/unimind') para construir rutas absolutas correctas
if (!function_exists('unimind_detect_base')) {
    function unimind_detect_base() {
        $derived = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

        // Probar primero con raíz vacía, luego con /unimind, finalmente el derivado
        $candidates = ['', '/unimind', $derived];
        foreach ($candidates as $c) {
            $swPath = $docroot . ($c === '' ? '' : $c) . '/sw.js';
            $manifestPath = $docroot . ($c === '' ? '' : $c) . '/public/manifest.webmanifest';
            if (file_exists($swPath) && file_exists($manifestPath)) {
                return $c;
            }
        }

        return $derived;
    }
}

$base = unimind_detect_base();

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
    <link rel="manifest" href="<?= $base ?>/public/manifest.webmanifest">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="<?= $base ?>/public/icons/icon.svg">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= $base ?>/public/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= $base ?>/public/icons/icon-512x512.png">
    <link rel="apple-touch-icon" href="<?= $base ?>/public/icons/icon-192x192.png">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Base path global para construcción de URLs -->
    <script>
    // Detectar base path desde ubicación actual (funciona offline y online)
    (function() {
        var pathname = window.location.pathname;
        if (pathname.includes('/unimind/')) {
            window.UNIMIND_BASE = '/unimind';
        } else if (pathname.startsWith('/unimind')) {
            window.UNIMIND_BASE = '/unimind';
        } else {
            window.UNIMIND_BASE = '';
        }
        // Permitir override desde PHP si está disponible
        var phpBase = '<?php echo $base; ?>';
        if (phpBase && phpBase !== '<?php echo $base; ?>') {
            window.UNIMIND_BASE = phpBase;
        }
    })();
    </script>
    
    <?php require_once __DIR__ . '/utils/asset-version.php'; ?>
    <link rel="stylesheet" href="public/css/style.css?v=<?php echo asset_version('public/css/style.css'); ?>">
    <link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
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
                // Registrar SW usando la base dinámica para funcionar en distintos despliegues
                navigator.serviceWorker.register('<?= $base ?>/sw.js')
                    .then(registration => {
                        // Service Worker registrado
                        // Verificar actualizaciones del SW
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // Opcional: mostrar notificación al usuario
                                    if (confirm('Nueva versión de UniMind disponible. ¿Deseas actualizar?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' });
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    })
                    .catch(() => {
                        // Error al registrar service worker (silenciado)
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
            // Service Worker no soportado en este navegador
        }
    </script>
</body>
</html>