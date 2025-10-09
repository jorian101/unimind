<?php
require_once dirname(__DIR__) . '/utils/sidebar-config.php';

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? ($currentRole === 'estudiante' ? 'inicio' : 'dashboard');
$sidebarProps = getSidebarConfig($currentRole, $currentPage);
?>

<aside class="sidebar" id="sidebar">
    <div>
        <div class="sidebar__header">
            <div class="sidebar__menu-toggle">☰</div>
            <span><?php echo $sidebarProps['title']; ?></span>
        </div>
        <ul class="sidebar__menu">
            <?php foreach ($sidebarProps['menu'] as $item): ?>
                <?php if (isset($item['submenu'])): ?>
                    <!-- Item with submenu -->
                    <li class="sidebar__item">
                        <i class="<?php echo $item['icon'] ?? ''; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                        <i class="fas fa-chevron-right submenu-toggle"></i>
                        <ul class="submenu">
                            <?php foreach ($item['submenu'] as $subItem): ?>
                                <li class="sidebar__item <?php echo (isset($subItem['page']) && $subItem['page'] === $currentPage) ? 'sidebar__item--active' : ''; ?>" 
                                    data-page="<?php echo $subItem['page']; ?>" 
                                    data-role="<?php echo $currentRole; ?>">
                                    <i class="<?php echo $subItem['icon'] ?? ''; ?>"></i>
                                    <span><?php echo $subItem['label']; ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Regular item -->
                    <li class="sidebar__item <?php echo (isset($item['page']) && $item['page'] === $currentPage) ? 'sidebar__item--active' : ''; ?>" 
                        data-page="<?php echo $item['page'] ?? ''; ?>" 
                        data-role="<?php echo $currentRole; ?>">
                        <i class="<?php echo $item['icon'] ?? ''; ?>"></i>
                        <span><?php echo $item['label']; ?></span>
                    </li>
                <?php endif; ?>
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
<div class="sidebar-overlay" id="sidebar-overlay"></div>

<script>
// Basic submenu toggle (expand/collapse on click)
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.submenu-toggle').forEach(toggle => {
        toggle.addEventListener('click', (e) => {
            e.stopPropagation();
            const parentItem = toggle.closest('.sidebar__item');
            const submenu = parentItem.querySelector('.submenu');
            if (submenu) {
                submenu.classList.toggle('open');
                toggle.classList.toggle('rotated');
            }
        });
    });
});
</script>
