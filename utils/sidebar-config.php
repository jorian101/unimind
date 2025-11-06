<?php
function getSidebarConfig($role, $currentPage = null) {
    // UnimindPages es solo para el estudiante:
    $unimindPages = ['dashboard', 'tests', 'historial', 'formulario', 'recomendaciones', 'calendario-citas'];
    $isUnimind = $currentPage && in_array($currentPage, $unimindPages);
    
    $configs = [
        'administrador' => [
            'title' => 'UniMind Admin',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-users', 'label' => 'Usuarios', 'page' => 'usuarios'],
                ['icon' => 'fas fa-chart-bar', 'label' => 'Reportes', 'page' => 'reportes'],
                ['icon' => 'fas fa-cog', 'label' => 'Configuración', 'page' => 'config'],
            ],
        ],
        'profesor' => [
            'title' => 'UniMind Profesor',
            'menu' => [
                ['icon' => 'fas fa-chart-line', 'label' => 'Dashboard', 'page' => 'dashboard-profesor'],
            ],
        ],
        'estudiante' => $isUnimind ? [
            'title' => 'UniMind Estudiante',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Evaluaciones', 'page' => 'tests'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Historial de evaluaciones', 'page' => 'historial'],
                ['icon' => 'fas fa-lightbulb', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Calendario de citas', 'page' => 'calendario-citas'],
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
                ['icon' => 'fas fa-columns', 'label' => 'Test de estres y ansiedad', 'page' => 'dashboard'],
            ],
        ],
    ];
    
    return $configs[$role] ?? $configs['estudiante'];
}
?>