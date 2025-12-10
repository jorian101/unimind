<?php
/**
 * Vista Dashboard Estudiante - Patrón MVC
 * Los datos se cargan desde el Controller y se exponen a JavaScript
 */

// Cargar datos del dashboard usando el Controller
if (!isset($dashboard_data)) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    $dashboard_data = [];
    
    if (isset($_SESSION['id_usuario'])) {
        try {
            require_once __DIR__ . '/../../controllers/EstudianteDashboardController.php';
            $controller = new EstudianteDashboardController();
            $dashboard_data = $controller->getEstadisticasCompletas((int) $_SESSION['id_usuario']);
        } catch (Exception $e) {
            error_log("Error cargando dashboard estudiante: " . $e->getMessage());
            // Fallback: estructura vacía
            $dashboard_data = [
                'estres' => null,
                'ansiedad' => null,
                'global' => [
                    'total_tests' => 0,
                    'dias_ultimo_test' => null,
                    'total_tests_estres' => 0,
                    'total_tests_ansiedad' => 0,
                    'estado_general' => 'Sin datos',
                    'requiere_atencion' => false
                ],
                'riesgo_emergente' => [
                    'tiene_riesgo' => false,
                    'num_casos' => 0,
                    'casos' => []
                ]
            ];
        }
    }
}

require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>

<script>
// Exponer datos del dashboard al frontend usando patrón similar al dashboard de profesor
window.UnimindData = window.UnimindData || {};
window.UnimindData.dashboard = <?php echo json_encode($dashboard_data); ?>;
</script>

<link rel="stylesheet" href="views/estudiante/dashboard.css?v=<?php echo time(); ?>">

<main class="dashboard-container" id="dashboard">
    <!-- Contenido se carga dinámicamente con JavaScript -->
</main>

<script src="public/js/dashboard.js?v=<?php echo time(); ?>"></script>