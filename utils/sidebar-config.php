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
?>