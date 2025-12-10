<?php
/**
 * CONFIGURACIÓN CENTRALIZADA DEL SIDEBAR
 * ======================================
 * 
 * Para agregar una nueva opción al menú:
 * 1. Añade un item con: 'icon', 'label', 'page'
 * 2. Si quieres submenu, usa: 'submenu' => [array de items]
 * 3. La 'page' debe coincidir con la clave en routes-config.php
 */

function getSidebarConfig($role, $currentPage = null) {
    $configs = [
        // ========================================
        // SIDEBAR PARA ADMINISTRADOR
        // ========================================
        'administrador' => [
            'title' => 'UniMind',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-users', 'label' => 'Usuarios', 'page' => 'usuarios'],
                ['icon' => 'fas fa-book-open', 'label' => 'Cursos/Escuelas', 'page' => 'cursos_escuelas'],                
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Tests', 'page' => 'tests'],                
                ['icon' => 'fas fa-lightbulb', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],                
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Citas', 'page' => 'citas'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],
                ['icon' => 'fas fa-chart-bar', 'label' => 'Reportes', 'page' => 'reportes'],
                // Notification menu removed per request
            ],
        ],

        // ========================================
        // SIDEBAR PARA DOCENTE
        // ========================================
        'docente' => [
            'title' => 'UniMind',
            'menu' => [
                ['icon' => 'fas fa-chart-line', 'label' => 'Dashboard', 'page' => 'dashboard-profesor'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Tests', 'page' => 'tests-profesor'],
                ['icon' => 'fas fa-paper-plane', 'label' => 'Mis Sugerencias', 'page' => 'sugerencias-profesor'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],            
            ],
        ],

        // ========================================
        // SIDEBAR PARA ESTUDIANTE
        // ========================================
        'estudiante' => [
            'title' => 'UniMind',
            'menu' => [
                ['icon' => 'fas fa-home', 'label' => 'Dashboard', 'page' => 'dashboard'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Tests', 'page' => 'tests'],
                ['icon' => 'fas fa-clipboard-list', 'label' => 'Historial', 'page' => 'historial'],
                ['icon' => 'fas fa-lightbulb', 'label' => 'Recomendaciones', 'page' => 'recomendaciones'],
                ['icon' => 'fas fa-calendar-alt', 'label' => 'Calendario de citas', 'page' => 'calendario-citas'],
                ['icon' => 'fas fa-bell', 'label' => 'Notificaciones', 'page' => 'notificaciones'],            
            ],
        ],
    ];
    
    return $configs[$role] ?? $configs['estudiante'];
}
?>