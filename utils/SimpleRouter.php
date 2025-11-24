<?php
/**
 * ROUTER CON SOPORTE AUTOMÁTICO
 * ======================================
 * 
 * Este router lee automáticamente la configuración de rutas
 * y maneja breadcrumbs, títulos y validación de páginas.
 * 
 * Los desarrolladores NO necesitan modificar este archivo.
 * Solo deben agregar rutas en utils/routes-config.php
 */

class SimpleRouter {
    private $currentRole;
    private $currentPage;
    private $routes;
    private $routeData;
    
    public function __construct() {
        // Cargar configuración de rutas
        $this->routes = require dirname(__DIR__) . '/utils/routes-config.php';
        
        // Obtener parámetros de la URL
        $this->currentRole = $_GET['role'] ?? 'autenticacion';
        $this->currentPage = $_GET['page'] ?? $this->getDefaultPage($this->currentRole);
        
        // Cargar datos de la ruta actual
        $this->loadRouteData();
    }
    
    /**
     * Carga los datos de configuración de la ruta actual
     */
    private function loadRouteData() {
        if (isset($this->routes[$this->currentRole][$this->currentPage])) {
            $this->routeData = $this->routes[$this->currentRole][$this->currentPage];
        } else {
            $this->routeData = null;
        }
    }
    
    public function getCurrentRole() {
        return $this->currentRole;
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    /**
     * Obtiene el título de la página actual
     */
    public function getPageTitle() {
        return $this->routeData['title'] ?? 'UniMind';
    }
    
    /**
     * Obtiene el breadcrumb de la página actual
     * Si tiene 'parent', construye breadcrumb automáticamente
     */
    public function getBreadcrumb() {
        if (!$this->routeData) {
            return ['Inicio'];
        }
        
        // Si tiene breadcrumb explícito, usarlo
        if (isset($this->routeData['breadcrumb'])) {
            return $this->routeData['breadcrumb'];
        }
        
        // Si tiene parent, construir breadcrumb automáticamente
        if (isset($this->routeData['parent'])) {
            $parentPage = $this->routeData['parent'];
            if (isset($this->routes[$this->currentRole][$parentPage])) {
                $parentData = $this->routes[$this->currentRole][$parentPage];
                $parentBreadcrumb = $parentData['breadcrumb'] ?? ['Inicio', $parentData['title']];
                return array_merge($parentBreadcrumb, [$this->routeData['title']]);
            }
        }
        
        // Por defecto
        return ['Inicio', $this->routeData['title']];
    }
    
    /**
     * Obtiene el tipo de layout (solo para estudiantes)
     */
    public function getLayout() {
        return $this->routeData['layout'] ?? null;
    }
    
    /**
     * Verifica si la ruta actual es de UniMind (para estudiantes)
     */
    public function isUnimindLayout() {
        return $this->currentRole === 'estudiante' && 
               isset($this->routeData['layout']) && 
               $this->routeData['layout'] === 'unimind';
    }
    
    /**
     * Obtiene la ruta del archivo de la vista
     */
    public function getPagePath($role = null, $page = null) {
        $role = $role ?? $this->currentRole;
        $page = $page ?? $this->currentPage;
        
        if (isset($this->routes[$role][$page])) {
            return "views/" . $this->routes[$role][$page]['path'];
        }
        
        // Fallback al sistema antiguo
        return "views/{$role}/{$page}.php";
    }
    
    /**
     * Verifica si la página existe
     */
    public function pageExists($role = null, $page = null) {
        $role = $role ?? $this->currentRole;
        $page = $page ?? $this->currentPage;
        
        // Verificar en la configuración de rutas
        if (isset($this->routes[$role][$page])) {
            $path = "views/" . $this->routes[$role][$page]['path'];
            return file_exists($path);
        }
        
        return false;
    }
    
    /**
     * Carga e incluye la página actual
     */
    public function loadPage() {
        $role = $this->getCurrentRole();
        $page = $this->getCurrentPage();
        
        if ($this->pageExists()) {
            $pagePath = $this->getPagePath();
            include $pagePath;
        } else {
            $this->show404($role, $page);
        }
    }
    
    /**
     * Muestra página 404 para rutas no encontradas
     */
    private function show404($role, $page) {
        $expectedPath = "views/{$role}/{$page}.php";
        echo "
        <div style='text-align: center; padding: 3rem;'>
            <h1>🚧 Página en desarrollo</h1>
            <p><strong>Rol:</strong> {$role}</p>
            <p><strong>Página:</strong> {$page}</p>
            <p>Archivo esperado: <code>{$expectedPath}</code></p>
            <hr style='margin: 2rem auto; max-width: 400px;'>
            <p><strong>Instrucciones para desarrolladores:</strong></p>
            <ol style='text-align: left; max-width: 600px; margin: 1rem auto;'>
                <li>Crea el archivo: <code>{$expectedPath}</code></li>
                <li>Agrega la ruta en: <code>/utils/routes-config.php</code></li>
                <li>Si quieres que aparezca en el menú, agrégala en: <code>utils/sidebar-config.php</code></li>
            </ol>
        </div>";
    }
    
    /**
     * Genera URL para una página
     */
    public function url($page, $role = null, $params = []) {
        $role = $role ?? $this->currentRole;
        $query = http_build_query(array_merge(['role' => $role, 'page' => $page], $params));
        return '?' . $query;
    }
    
    /**
     * Obtiene la página por defecto para un rol
     */
    private function getDefaultPage($role) {
        $defaults = [
            'autenticacion' => 'login',
            'administrador' => 'dashboard',
            'docente' => 'dashboard-profesor',
            'estudiante' => 'dashboard',
        ];
        
        return $defaults[$role] ?? 'login';
    }
}
?>