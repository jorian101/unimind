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
    <div class="header-items-right">
        <div class="header-icons">
            <i class="icon fas fa-globe-americas"></i>
            <i class="icon fas fa-bell"></i>
            <i class="icon fas fa-comment"></i>
        </div>
        
        <div class="profile-menu-container" id="profileToggle"> 
            <span class="profile-icon"><i class="fas fa-user"></i></span>
            <span class="dropdown-arrow">▼</span>
            
            <div class="dropdown-menu" id="profileMenu">
                <div class="menu-header"><?php echo htmlspecialchars($userName); ?></div>
                <a href="#"><i class="fas fa-columns"></i> Tablero</a>
                <a href="#"><i class="fas fa-user-circle"></i> Perfil</a>
                <a href="#"><i class="fas fa-graduation-cap"></i> Calificaciones</a>
                <a href="#"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="#"><i class="fas fa-cog"></i> Preferencias</a>
                <a href="#"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </div>
</header>