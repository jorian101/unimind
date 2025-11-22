<?php
/**
 * CONFIGURACIÓN CENTRALIZADA DE RUTAS
 * ====================================
 * 
 * Para agregar una nueva página, simplemente añade una entrada aquí con:
 * - 'path': archivo PHP de la vista (relativo a views/)
 * - 'title': título que aparecerá en el PageHeader
 * - 'breadcrumb': array de migas de pan (opcional)
 * - 'parent': ruta padre para construir breadcrumbs automáticos (opcional)
 * - 'layout': tipo de layout ('unimind' o 'aula-virtual') solo para estudiante
 */

return [
    // ========================================
    // RUTAS DE AUTENTICACIÓN
    // ========================================
    'autenticacion' => [
        'login' => [
            'path' => 'autenticacion/login.php',
            'title' => 'Iniciar Sesión',
            'breadcrumb' => ['Login'],
            'layout' => null, // Sin layout (página standalone)
        ],
    ],

    // ========================================
    // RUTAS DE ADMINISTRADOR
    // ========================================
    'administrador' => [
        'dashboard' => [
            'path' => 'administrador/dashboard.php',
            'title' => 'Dashboard Administrativo',
            'breadcrumb' => ['Inicio', 'Dashboard'],
        ],
        'tests' => [
            'path' => 'administrador/tests.php',
            'title' => 'Gestión de Tests',
            'breadcrumb' => ['Inicio', 'Tests'],
        ],
        'usuarios' => [
            'path' => 'administrador/usuarios.php',
            'title' => 'Gestión de Usuarios',
            'breadcrumb' => ['Inicio', 'Usuarios'],
        ],
        'reportes' => [
            'path' => 'administrador/reportes.php',
            'title' => 'Reportes y Estadísticas',
            'breadcrumb' => ['Inicio', 'Reportes'],
        ],
        'config' => [
            'path' => 'administrador/config.php',
            'title' => 'Configuración del Sistema',
            'breadcrumb' => ['Inicio', 'Configuración'],
        ],
    ],

    // ========================================
    // RUTAS DE DOCENTE
    // ========================================
    'docente' => [
        'dashboard-profesor' => [
            'path' => 'profesor/dashboard-profesor.php',
            'title' => 'Dashboard',
            'breadcrumb' => ['Inicio', 'Dashboard'],
        ],
    ],

    // ========================================
    // RUTAS DE ESTUDIANTE - AULA VIRTUAL
    // ========================================
    'estudiante' => [
        // --- Páginas del Aula Virtual (layout tradicional) ---
        'inicio' => [
            'path' => 'estudiante/inicio.php',
            'title' => 'Inicio',
            'breadcrumb' => ['Inicio'],
            'layout' => 'aula-virtual',
        ],
        'tablero' => [
            'path' => 'estudiante/tablero.php',
            'title' => 'Tablero',
            'breadcrumb' => ['Inicio', 'Tablero'],
            'layout' => 'aula-virtual',
        ],
        'calendario' => [
            'path' => 'estudiante/calendario.php',
            'title' => 'Calendario Académico',
            'breadcrumb' => ['Inicio', 'Calendario'],
            'layout' => 'aula-virtual',
        ],
        'archivos' => [
            'path' => 'estudiante/archivos.php',
            'title' => 'Mis Archivos Privados',
            'breadcrumb' => ['Inicio', 'Archivos'],
            'layout' => 'aula-virtual',
        ],
        'curso-web' => [
            'path' => 'estudiante/curso-web.php',
            'title' => 'Ingeniería Web',
            'breadcrumb' => ['Inicio', 'Mis Cursos', 'Ingeniería Web'],
            'layout' => 'aula-virtual',
        ],
        'tesis' => [
            'path' => 'estudiante/tesis.php',
            'title' => 'Tesis I',
            'breadcrumb' => ['Inicio', 'Mis Cursos', 'Tesis I'],
            'layout' => 'aula-virtual',
        ],
        'filosofia' => [
            'path' => 'estudiante/filosofia.php',
            'title' => 'Filosofía',
            'breadcrumb' => ['Inicio', 'Mis Cursos', 'Filosofía'],
            'layout' => 'aula-virtual',
        ],
        'seguridad' => [
            'path' => 'estudiante/seguridad.php',
            'title' => 'Seguridad Informática',
            'breadcrumb' => ['Inicio', 'Mis Cursos', 'Seguridad Informática'],
            'layout' => 'aula-virtual',
        ],

        // --- Páginas de UniMind (layout moderno) ---
        'dashboard' => [
            'path' => 'estudiante/dashboard.php',
            'title' => 'Dashboard',
            'breadcrumb' => ['Inicio', 'Dashboard'],
            'layout' => 'unimind',
        ],
        'tests' => [
            'path' => 'estudiante/tests.php',
            'title' => 'Evaluaciones',
            'breadcrumb' => ['Inicio', 'Evaluaciones'],
            'layout' => 'unimind',
        ],
        'formulario' => [
            'path' => 'estudiante/formulario.php',
            'title' => 'Formulario de Evaluación',
            'breadcrumb' => ['Inicio', 'Evaluaciones', 'Formulario'],
            'parent' => 'tests', // Define la ruta padre automáticamente
            'layout' => 'unimind',
        ],
        'historial' => [
            'path' => 'estudiante/historial.php',
            'title' => 'Historial de Evaluaciones',
            'breadcrumb' => ['Inicio', 'Historial'],
            'layout' => 'unimind',
        ],
        'recomendaciones' => [
            'path' => 'estudiante/recomendaciones.php',
            'title' => 'Recomendaciones Personalizadas',
            'breadcrumb' => ['Inicio', 'Recomendaciones'],
            'layout' => 'unimind',
        ],
        'calendario-citas' => [
            'path' => 'estudiante/calendario-citas.php',
            'title' => 'Calendario de Citas',
            'breadcrumb' => ['Inicio', 'Calendario de Citas'],
            'layout' => 'unimind',
        ],
    ],
];
