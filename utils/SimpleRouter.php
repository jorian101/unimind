<?php
class SimpleRouter {
    private $currentRole;
    private $currentPage;
    
    public function __construct() {
        $this->currentRole = $_GET['role'] ?? 'estudiante';
        $this->currentPage = $_GET['page'] ?? ($this->currentRole === 'estudiante' ? 'inicio' : 'dashboard');
    }
    
    public function getCurrentRole() {
        return $this->currentRole;
    }
    
    public function getCurrentPage() {
        return $this->currentPage;
    }
    
    public function getPagePath($role, $page) {
        return "views/{$role}/{$page}.php";
    }
    
    public function pageExists($role, $page) {
        return file_exists($this->getPagePath($role, $page));
    }
    
    public function loadPage() {
        $role = $this->getCurrentRole();
        $page = $this->getCurrentPage();
        $pagePath = $this->getPagePath($role, $page);
        
        if ($this->pageExists($role, $page)) {
            include $pagePath;
        } else {
            $this->show404($role, $page);
        }
    }
    
    private function show404($role, $page) {
        echo "
        <div style='text-align: center; padding: 3rem;'>
            <h1>🚧 Página en desarrollo</h1>
            <p><strong>Rol:</strong> {$role}</p>
            <p><strong>Página:</strong> {$page}</p>
            <p>Archivo esperado: <code>views/{$role}/{$page}.php</code></p>
        </div>";
    }
}
?>