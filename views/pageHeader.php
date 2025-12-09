<?php
/**
 * PAGE HEADER CON DETECCIÓN AUTOMÁTICA DE BREADCRUMBS
 * ==================================================
 * 
 * Este componente ahora detecta automáticamente el breadcrumb
 * desde la configuración de rutas. Los desarrolladores pueden:
 * 
 * 1. Dejar que se detecte automáticamente (recomendado):
 *    renderPageHeader();
 * 
 * 2. Personalizar solo el título:
 *    renderPageHeader('Mi Título Personalizado');
 * 
 * 3. Personalizar título y breadcrumb:
 *    renderPageHeader('Mi Título', ['Inicio', 'Paso 1', 'Paso 2']);
 */

function renderPageHeader($title = null, $breadcrumb = null) {
    // Obtener el router global si está disponible
    global $router;
    
    // Si no se proporciona título, usar el del router
    if ($title === null && isset($router)) {
        $title = $router->getPageTitle();
    } elseif ($title === null) {
        $title = 'UniMind';
    }
    
    // Si no se proporciona breadcrumb, usar el del router
    if ($breadcrumb === null && isset($router)) {
        $breadcrumb = $router->getBreadcrumb();
    }
    
    // Construir breadcrumb dinámico desde el historial de navegación
    $breadcrumbHtml = '';
    
    // La sesión ya está iniciada en index.php
    // Verificar que la sesión esté activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // DEBUG: Verificar estado de la sesión
    $sessionId = session_id();
    $sessionStatus = session_status();
    
    // Detectar si viene desde el sidebar
    $fromSidebar = isset($_GET['from_sidebar']) && $_GET['from_sidebar'] == '1';
    
    // Si viene del sidebar, reiniciar el historial (las páginas del sidebar son raíz)
    if ($fromSidebar) {
        $_SESSION['nav_history'] = [];
        error_log("NAVEGACIÓN DESDE SIDEBAR - Historial reiniciado");
    }
    
    // Inicializar historial si no existe
    if (!isset($_SESSION['nav_history'])) {
        $_SESSION['nav_history'] = [];
    }
    
    $currentRole = $_GET['role'] ?? 'estudiante';
    $currentPage = $_GET['page'] ?? 'inicio';
    $currentTitle = $title;
    
    // Detectar si viene desde el breadcrumb
    $fromBreadcrumb = isset($_GET['from_breadcrumb']) && $_GET['from_breadcrumb'] == '1';
    
    // DEBUG: Ver estado actual
    error_log("=== BREADCRUMB DEBUG ===");
    error_log("Session ID: $sessionId");
    error_log("Session Status: $sessionStatus");
    error_log("Página actual: $currentPage");
    error_log("Título actual: $currentTitle");
    error_log("From Sidebar: " . ($fromSidebar ? 'SI' : 'NO'));
    error_log("From Breadcrumb: " . ($fromBreadcrumb ? 'SI' : 'NO'));
    error_log("Historial ANTES: " . json_encode($_SESSION['nav_history']));
    
    if ($fromBreadcrumb) {
        // Si viene del breadcrumb, encontrar la página en el historial y cortar ahí
        $foundIndex = -1;
        foreach ($_SESSION['nav_history'] as $index => $item) {
            if ($item['page'] === $currentPage && $item['role'] === $currentRole) {
                $foundIndex = $index;
                break;
            }
        }
        
        if ($foundIndex !== -1) {
            // Cortar el historial hasta ese punto (inclusive)
            $_SESSION['nav_history'] = array_slice($_SESSION['nav_history'], 0, $foundIndex + 1);
            error_log("NAVEGACIÓN DESDE BREADCRUMB - Historial cortado en índice $foundIndex");
        }
    } else {
        // Navegación normal - agregar al historial
        // Obtener la última página del historial
        $lastPage = end($_SESSION['nav_history']);
        reset($_SESSION['nav_history']); // Resetear el puntero interno
        
        // Solo agregar si NO es la misma página que la última
        if (!$lastPage || $lastPage['page'] !== $currentPage) {
            // Agregar página al historial
            $_SESSION['nav_history'][] = [
                'role' => $currentRole,
                'page' => $currentPage,
                'title' => $currentTitle
            ];
            error_log("PÁGINA NUEVA - Agregada al historial");
        } else {
            error_log("MISMA PÁGINA - No se agrega");
        }
    }
    
    // Limitar el historial a máximo 5 páginas
    if (count($_SESSION['nav_history']) > 5) {
        array_shift($_SESSION['nav_history']);
    }
    
    error_log("Historial DESPUÉS: " . json_encode($_SESSION['nav_history']));
    error_log("Total items en breadcrumb: " . count($_SESSION['nav_history']));
    
    // Construir breadcrumb desde el historial
    if (count($_SESSION['nav_history']) > 0) {
        $breadcrumbHtml = '<nav class="page-header__breadcrumb-nav">';
        foreach ($_SESSION['nav_history'] as $index => $item) {
            $isLast = $index === count($_SESSION['nav_history']) - 1;
            $class = $isLast ? 'page-header__breadcrumb-item--current' : 'page-header__breadcrumb-item--navigable';
            
            error_log("Breadcrumb item $index: {$item['title']} (isLast: " . ($isLast ? 'SI' : 'NO') . ")");
            
            if ($isLast) {
                // Último item (actual) - no clickeable
                $breadcrumbHtml .= '<span class="page-header__breadcrumb-item ' . $class . '">' . htmlspecialchars($item['title']) . '</span>';
            } else {
                // Items anteriores - clickeables con parámetro from_breadcrumb
                $url = '?role=' . urlencode($item['role']) . '&page=' . urlencode($item['page']) . '&from_breadcrumb=1';
                $breadcrumbHtml .= '<a href="' . $url . '" class="page-header__breadcrumb-item ' . $class . '">' . htmlspecialchars($item['title']) . '</a>';
            }
            
            if (!$isLast) {
                $breadcrumbHtml .= '<span class="page-header__breadcrumb-separator">›</span>';
            }
        }
        $breadcrumbHtml .= '</nav>';
    }
    error_log("======================");
    
    echo '
    <header id="page-header" class="page-header">
        <div class="page-header__content">
            ' . $breadcrumbHtml . '
            <div class="page-header__title-section">
                <h1 class="page-header__title">' . htmlspecialchars($title) . '</h1>
            </div>
        </div>
        <div class="page-header__actions" id="page-header-actions">

        </div>
    </header>
    <script src="public/js/notifications.js"></script>
    ';
}
?>