<?php
class Router {
    private $routes = [];
    private $currentRole = 'estudiante';
    
    public function __construct() {
        $this->currentRole = $_GET['role'] ?? 'estudiante';
    }
    
    public function getCurrentRole() {
        return $this->currentRole;
    }
    
    public function getCurrentPage() {
        return $_GET['page'] ?? 'dashboard';
    }
    
    /**
     * Obtener configuración del sidebar para sincronizar títulos
     */
    private function getSidebarConfig($role) {
        require_once 'views/sidebar.php';
        return getSidebarConfig($role);
    }
    
    /**
     * Obtener título dinámico desde el sidebar
     */
    public function getPageTitle($role, $page) {
        $sidebarConfig = $this->getSidebarConfig($role);
        
        // Buscar el título en el menú del sidebar
        foreach ($sidebarConfig['menu'] as $item) {
            if ($item['page'] === $page) {
                return $item['label'];
            }
        }
        
        // Fallback si no se encuentra
        return 'UniMind';
    }
    
    /**
     * Configuración centralizada de páginas por rol
     * Aquí los colegas pueden agregar nuevas páginas fácilmente
     */
    private function getPageConfigs() {
        return [
            'administrador' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'pages/admin/dashboard.php'
                ],
                'usuarios' => [
                    'breadcrumb' => ['Inicio', 'Usuarios'],
                    'file' => 'pages/admin/usuarios.php'
                ],
                'reportes' => [
                    'breadcrumb' => ['Inicio', 'Reportes'],
                    'file' => 'pages/admin/reportes.php'
                ],
                'config' => [
                    'breadcrumb' => ['Inicio', 'Configuración'],
                    'file' => 'pages/admin/config.php'
                ]
            ],
            'profesor' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'pages/profesor/dashboard.php'
                ],
                'clases' => [
                    'breadcrumb' => ['Inicio', 'Clases'],
                    'file' => 'pages/profesor/clases.php'
                ],
                'reportes' => [
                    'breadcrumb' => ['Inicio', 'Reportes'],
                    'file' => 'pages/profesor/reportes.php'
                ]
            ],
            'estudiante' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'pages/estudiante/dashboard.php'
                ],
                'tests' => [
                    'breadcrumb' => ['Inicio', 'Tests'],
                    'file' => 'pages/estudiante/tests.php'
                ],
                'recomendaciones' => [
                    'breadcrumb' => ['Inicio', 'Recomendaciones'],
                    'file' => 'pages/estudiante/recomendaciones.php'
                ],
                'calendario' => [
                    'breadcrumb' => ['Inicio', 'Calendario'],
                    'file' => 'pages/estudiante/calendario.php'
                ]
            ]
        ];
    }
    
    public function getBreadcrumbPath($role, $page) {
        $configs = $this->getPageConfigs();
        $breadcrumb = $configs[$role][$page]['breadcrumb'] ?? ['Inicio'];
        
        // Si es página padre (dashboard), solo mostrar el último elemento
        if ($page === 'dashboard') {
            return [end($breadcrumb)];
        }
        
        return $breadcrumb;
    }
    
    public function getPageFile($role, $page) {
        $configs = $this->getPageConfigs();
        return $configs[$role][$page]['file'] ?? null;
    }
    
    public function renderPage() {
        $role = $this->getCurrentRole();
        $page = $this->getCurrentPage();
        
        // Intentar cargar archivo específico de la página
        $pageFile = $this->getPageFile($role, $page);
        
        if ($pageFile && file_exists($pageFile)) {
            ob_start();
            include $pageFile;
            return ob_get_clean();
        }
        
        // Fallback al contenido por defecto si no existe el archivo
        return $this->getDefaultContent($role, $page);
    }
    
    /**
     * Contenido por defecto cuando no existe archivo específico
     * Los colegas pueden crear sus archivos PHP y este se usará como fallback
     */
    private function getDefaultContent($role, $page) {
        $pageTitle = $this->getPageTitle($role, $page);
        
        return "
        <div class='page-content'>
            <div class='page-placeholder'>
                <h2>🚧 {$pageTitle}</h2>
                <p>Esta página está en desarrollo.</p>
                <div class='dev-info'>
                    <h4>Para desarrolladores:</h4>
                    <p><strong>Rol:</strong> {$role}</p>
                    <p><strong>Página:</strong> {$page}</p>
                    <p><strong>Archivo esperado:</strong> <code>{$this->getPageFile($role, $page)}</code></p>
                    <p>Crea este archivo para personalizar el contenido de esta página.</p>
                </div>
            </div>
        </div>
        
        <style>
        .page-placeholder {
            text-align: center;
            padding: 3rem;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e2e8f0;
            margin: 2rem 0;
        }
        
        .dev-info {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: left;
            margin-top: 2rem;
            border: 1px solid #e2e8f0;
        }
        
        .dev-info code {
            background: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-family: monospace;
            color: #475569;
        }
        </style>
        ";
    }
    
    /**
     * Método para que los colegas registren nuevas páginas fácilmente
     */
    public function addPage($role, $page, $config) {
        // Esta función permitirá agregar páginas dinámicamente en el futuro
        // Por ahora, las páginas se configuran en getPageConfigs()
    }
}
?>