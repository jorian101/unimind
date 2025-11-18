<?php
require_once dirname(__DIR__) . '/utils/sidebar-config.php';

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? ($currentRole === 'estudiante' ? 'inicio' : 'dashboard');
$sidebarProps = getSidebarConfig($currentRole, $currentPage);
?>

<aside class="sidebar" id="sidebar">
    <div>
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
    
    <!-- Role selector removed: not needed anymore -->
</aside>
<div class="sidebar-overlay" id="sidebar-overlay"></div>
