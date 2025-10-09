<?php
function getSidebarConfig($role, $currentPage = null) {
    // Only these specific pages should show UniMind sidebar
    $unimindPages = ['dashboard', 'tests', 'recomendaciones', 'calendario-citas'];
    $isUnimind = $currentPage && in_array($currentPage, $unimindPages);
    
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
        'estudiante' => $isUnimind ? [
            'title' => 'UniMind Estudiante',
            'menu' => [
                ['icon' => '🏠', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => '📝', 'label' => 'Tests y evaluaciones', 'page' => 'tests'],
                ['icon' => '💡', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
                ['icon' => '📅', 'label' => 'Calendario de citas', 'page' => 'calendario-citas'],
            ],
        ] : [
            'title' => 'AULA VIRTUAL UNJBG',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Inicio', 'page' => 'inicio'],
                ['icon' => 'fas fa-columns', 'label' => 'Tablero', 'page' => 'tablero'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Calendario', 'page' => 'calendario'],
                ['icon' => 'fas fa-folder-open', 'label' => 'Archivos privados', 'page' => 'archivos'],
                ['icon' => 'fas fa-book', 'label' => 'Mis cursos activos', 'submenu' => [
                    ['icon' => 'fas fa-code', 'label' => 'Ingeniería Web', 'page' => 'curso-web'],
                    ['icon' => 'fas fa-file-alt', 'label' => 'Tesis I', 'page' => 'tesis'],
                    ['icon' => 'fas fa-brain', 'label' => 'Filosofía', 'page' => 'filosofia'],
                    ['icon' => 'fas fa-shield-alt', 'label' => 'Seguridad Informática', 'page' => 'seguridad'],
                ]],
                ['icon' => 'fas fa-columns', 'label' => 'Test de estres y personalidad', 'page' => 'dashboard'],
            ],
        ],
    ];
    
    return $configs[$role] ?? $configs['estudiante'];
}
?>