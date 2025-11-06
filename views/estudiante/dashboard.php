<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>

<link rel="stylesheet" href="views/estudiante/dashboard.css?v=<?php echo time(); ?>">

<main class="dashboard-container" id="dashboard">
    <!-- Contenido se carga dinámicamente con JavaScript -->
</main>

<script src="public/js/dashboard.js?v=<?php echo time(); ?>"></script>