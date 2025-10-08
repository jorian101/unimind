<?php
/**
 * Componente Header simplificado
 * Solo contiene notificaciones y perfil, alineado con el sidebar
 */

// Obtener parámetros básicos
$currentRole = $_GET['role'] ?? 'estudiante';
$userName = 'Usuario Actual'; // Esto vendría de la sesión/BD
?>

<header id="main-header" class="main-header">
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