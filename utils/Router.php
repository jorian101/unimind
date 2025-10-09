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
    
    private function getSidebarConfig($role) {
        require_once 'views/sidebar.php';
        return getSidebarConfig($role);
    }

    public function getPageTitle($role, $page) {
        $sidebarConfig = $this->getSidebarConfig($role);
        
        foreach ($sidebarConfig['menu'] as $item) {
            if ($item['page'] === $page) {
                return $item['label'];
            }
        }
        
        return 'UniMind';
    }

    private function getPageConfigs() {
        return [
            'administrador' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'views/administrador/dashboard.php'
                ],
                'usuarios' => [
                    'breadcrumb' => ['Inicio', 'Usuarios'],
                    'file' => 'views/administrador/usuarios.php'
                ],
                'reportes' => [
                    'breadcrumb' => ['Inicio', 'Reportes'],
                    'file' => 'views/administrador/reportes.php'
                ],
                'config' => [
                    'breadcrumb' => ['Inicio', 'Configuración'],
                    'file' => 'views/administrador/config.php'
                ]
            ],
            'profesor' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'views/profesor/dashboard.php'
                ],
                'clases' => [
                    'breadcrumb' => ['Inicio', 'Clases'],
                    'file' => 'views/profesor/clases.php'
                ],
                'reportes' => [
                    'breadcrumb' => ['Inicio', 'Reportes'],
                    'file' => 'views/profesor/reportes.php'
                ]
            ],
            'estudiante' => [
                'dashboard' => [
                    'breadcrumb' => ['Inicio'],
                    'file' => 'views/estudiante/dashboard.php'
                ],
                'tests' => [
                    'breadcrumb' => ['Inicio', 'Tests'],
                    'file' => 'views/estudiante/tests.php'
                ],
                'recomendaciones' => [
                    'breadcrumb' => ['Inicio', 'Recomendaciones'],
                    'file' => 'views/estudiante/recomendaciones.php'
                ],
                'calendario' => [
                    'breadcrumb' => ['Inicio', 'Calendario'],
                    'file' => 'views/estudiante/calendario.php'
                ]
            ]
        ];
    }
    
    public function getBreadcrumbPath($role, $page) {
        $configs = $this->getPageConfigs();
        $breadcrumb = $configs[$role][$page]['breadcrumb'] ?? ['Inicio'];
        
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
        
        $pageFile = $this->getPageFile($role, $page);
        
        if ($pageFile && file_exists($pageFile)) {
            ob_start();
            include $pageFile;
            return ob_get_clean();
        }
        
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

}
?>