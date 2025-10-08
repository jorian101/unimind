<?php
function getSidebarConfig($role) {
    $configs = [
        'administrador' => [
            'title' => 'UniMind Admin',
            'menu' => [
                ['icon' => '🏠', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => '👥', 'label' => 'Usuarios', 'page' => 'usuarios'],
                ['icon' => '📊', 'label' => 'Reportes', 'page' => 'reportes'],
                ['icon' => '⚙️', 'label' => 'Configuración', 'page' => 'config'],
            ],
        ],
        'profesor' => [
            'title' => 'UniMind Profesor',
            'menu' => [
                ['icon' => '🏠', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => '📚', 'label' => 'Resumen de clases', 'page' => 'clases'],
                ['icon' => '📊', 'label' => 'Reportes de clases', 'page' => 'reportes'],
            ],
        ],
        'estudiante' => [
            'title' => 'UniMind Estudiante',
            'menu' => [
                ['icon' => '🏠', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => '📝', 'label' => 'Tests y evaluaciones', 'page' => 'tests'],
                ['icon' => '💡', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
                ['icon' => '📅', 'label' => 'Calendario de citas', 'page' => 'calendario'],
            ],
        ],
    ];
    
    return $configs[$role] ?? $configs['estudiante'];
}

$currentRole = $_GET['role'] ?? 'estudiante';
$currentPage = $_GET['page'] ?? 'dashboard';
$sidebarProps = getSidebarConfig($currentRole);
?>

<aside class="sidebar">
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
