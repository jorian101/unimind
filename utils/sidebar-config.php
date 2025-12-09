<?php
/**
 * CONFIGURACIÓN CENTRALIZADA DEL SIDEBAR
 * ======================================
 * 
 * Para agregar una nueva opción al menú:
 * 1. Añade un item con: 'icon', 'label', 'page'
 * 2. Si quieres submenu, usa: 'submenu' => [array de items]
 * 3. La 'page' debe coincidir con la clave en routes-config.php
 * 
 * IMPORTANTE: El sidebar para estudiantes tiene dos layouts:
 * - 'aula-virtual': Menu tradicional del aula virtual
 * - 'unimind': Menu moderno de salud mental
 */

function getSidebarConfig($role, $currentPage = null) {
    // Detectar si estamos en una página de UniMind (para estudiantes)
    $unimindPages = ['dashboard', 'tests', 'historial', 'formulario', 'recomendaciones', 'calendario-citas'];
    $isUnimind = $currentPage && in_array($currentPage, $unimindPages);
    
    $configs = [
        // ========================================
        // SIDEBAR PARA ADMINISTRADOR
        // ========================================
        'administrador' => [
            'title' => 'UniMind Admin',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Gestión de Tests', 'page' => 'tests'],
                ['icon' => 'fas fa-users', 'label' => 'Usuarios', 'page' => 'usuarios'],
                ['icon' => 'fas fa-book-open', 'label' => 'Cursos/Escuelas', 'page' => 'cursos_escuelas'],
                ['icon' => 'fas fa-chart-bar', 'label' => 'Reportes', 'page' => 'reportes'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Citas', 'page' => 'citas'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],
                // Notification menu removed per request
                ['icon' => 'fas fa-lightbulb', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
            ],
        ],

        // ========================================
        // SIDEBAR PARA DOCENTE
        // ========================================
        'docente' => [
            'title' => 'UniMind Docente',
            'menu' => [
                ['icon' => 'fas fa-chart-line', 'label' => 'Dashboard', 'page' => 'dashboard-profesor'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Tests Disponibles', 'page' => 'tests-profesor'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],
                ['icon' => 'fas fa-paper-plane', 'label' => 'Mis Sugerencias', 'page' => 'sugerencias-profesor'],
            ],
        ],

        // ========================================
        // SIDEBAR PARA ESTUDIANTE
        // ========================================
        'estudiante' => $isUnimind ? [
            // --- LAYOUT UNIMIND (Salud Mental) ---
            'title' => 'UniMind Estudiante',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Evaluaciones', 'page' => 'tests'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Historial de evaluaciones', 'page' => 'historial'],
                ['icon' => 'fas fa-lightbulb', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Calendario de citas', 'page' => 'calendario-citas'],
            ],
        ] : [
            // --- LAYOUT AULA VIRTUAL (Tradicional) ---
            'title' => 'AULA VIRTUAL UNJBG',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Inicio', 'page' => 'inicio'],
                ['icon' => 'fas fa-columns', 'label' => 'Tablero', 'page' => 'tablero'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Calendario', 'page' => 'calendario'],
                ['icon' => 'fas fa-folder-open', 'label' => 'Archivos privados', 'page' => 'archivos'],
                [
                    'icon' => 'fas fa-book', 
                    'label' => 'Mis cursos activos', 
                    'submenu' => [
                        ['icon' => 'fas fa-code', 'label' => 'Ingeniería Web', 'page' => 'curso-web'],
                        ['icon' => 'fas fa-file-alt', 'label' => 'Tesis I', 'page' => 'tesis'],
                        ['icon' => 'fas fa-brain', 'label' => 'Filosofía', 'page' => 'filosofia'],
                        ['icon' => 'fas fa-shield-alt', 'label' => 'Seguridad Informática', 'page' => 'seguridad'],
                    ]
                ],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],
                ['icon' => 'fas fa-columns', 'label' => 'Test de estres y ansiedad', 'page' => 'dashboard'],
            ],
        ],
    ];
    
    return $configs[$role] ?? $configs['estudiante'];
}
?>