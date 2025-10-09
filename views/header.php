<?php
require_once dirname(__DIR__) . '/utils/sidebar-config.php';

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? ($currentRole === 'estudiante' ? 'inicio' : 'dashboard');
$sidebarProps = getSidebarConfig($currentRole, $currentPage);
$userName = 'Usuario Actual'; // Esto vendría de la sesión/BD
?>

<header id="main-header" class="main-header">
    <div class="sidebar__header">
        <div class="sidebar__menu-toggle">☰</div>
        <span><?php echo $sidebarProps['title']; ?></span>
    </div>
    <div class="main-header__actions">
        <button class="main-header__action-btn" title="Notificaciones">
            <i>🔔</i>
            <span class="main-header__notification-badge">3</span>
        </button>
        
        <div class="main-header__user-info" title="Mi Perfil">
            <span class="main-header__user-name"><?php echo htmlspecialchars($userName); ?></span>
            <div class="main-header__user-avatar">👤</div>
        </div>
    </div>
</header>