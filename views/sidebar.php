<?php
require_once dirname(__DIR__) . '/utils/sidebar-config.php';

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? 'dashboard';
$sidebarProps = getSidebarConfig($currentRole);
?>

<aside class="sidebar" id="sidebar">
    <div>
        <div class="sidebar__header">
            <div class="sidebar__menu-toggle">☰</div>
            <span><?php echo $sidebarProps['title']; ?></span>
        </div>
        <ul class="sidebar__menu">
            <?php foreach ($sidebarProps['menu'] as $item): ?>
                <li class="sidebar__item <?php echo ($item['page'] === $currentPage) ? 'sidebar__item--active' : ''; ?>" 
                    data-page="<?php echo $item['page']; ?>" 
                    data-role="<?php echo $currentRole; ?>">
                    <i><?php echo $item['icon']; ?></i>
                    <span><?php echo $item['label']; ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <!-- Selector de roles para pruebas (BORRAR LUEGO) -->
    <div class="role-selector" style="background: #fff; padding: 10px; margin: 10px;">
        <select id="roleSelector" style="width: 100%; padding: 5px;">
            <option value="estudiante" <?php echo $currentRole === 'estudiante' ? 'selected' : ''; ?>>Estudiante</option>
            <option value="profesor" <?php echo $currentRole === 'profesor' ? 'selected' : ''; ?>>Profesor</option>
            <option value="administrador" <?php echo $currentRole === 'administrador' ? 'selected' : ''; ?>>Administrador</option>
        </select>
    </div>
</aside>
</aside>
