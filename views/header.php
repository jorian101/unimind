<?php
require_once dirname(__DIR__) . '/utils/sidebar-config.php';

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? ($currentRole === 'estudiante' ? 'inicio' : 'dashboard');
$sidebarProps = getSidebarConfig($currentRole, $currentPage);

$userName = session_status() === PHP_SESSION_ACTIVE ? ($_SESSION['user_name'] ?? 'Usuario Actual') : 'Usuario Actual';
// Determinar rol mostrado: preferir la sesión, caer a la ruta actual
$userRoleKey = session_status() === PHP_SESSION_ACTIVE ? ($_SESSION['user_role'] ?? $_SESSION['id_rol'] ?? $currentRole) : $currentRole;
$roleLabels = [
    'estudiante' => 'Estudiante',
    'docente' => 'Docente',
    'administrador' => 'Administrador',
];
$userRoleLabel = $roleLabels[$userRoleKey] ?? (is_string($userRoleKey) ? ucfirst($userRoleKey) : 'Usuario');
?>

<header id="main-header" class="main-header">
    <div class="sidebar__header">
        <div class="sidebar__menu-toggle">☰</div>
        <span><?php echo $sidebarProps['title']; ?></span>
    </div>
    <div class="header-items-right">

        
        <div class="profile-menu-container" id="profileToggle"> 
            <span class="profile-role"><?php echo htmlspecialchars($userRoleLabel); ?></span>
            <span class="profile-icon"><i class="fas fa-user"></i></span>
            <span class="dropdown-arrow">▼</span>
            
            <div class="dropdown-menu" id="profileMenu">
                <div class="menu-header"><?php echo htmlspecialchars($userName); ?></div>
                <a href="#"><i class="fas fa-columns"></i> Tablero</a>
                <a href="#"><i class="fas fa-user-circle"></i> Perfil</a>
                <a href="#"><i class="fas fa-graduation-cap"></i> Calificaciones</a>
                <a href="#"><i class="fas fa-envelope"></i> Mensajes</a>
                <a href="#"><i class="fas fa-cog"></i> Preferencias</a>
                <a href="controllers/Logout.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
            </div>
        </div>
    </div>
</header>