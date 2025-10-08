<?php
/**
 * Layout principal que incluye sidebar + header fijo
 * Componentes modulares y reutilizables
 */
?>

<!-- Sidebar independiente -->
<?php include 'sidebar.php'; ?>

<!-- Header fijo alineado con sidebar -->
<?php include 'header.php'; ?>

<!-- CSS necesarios para el layout -->
<link rel="stylesheet" href="public/css/theme.css">
<link rel="stylesheet" href="views/sidebar.css">
<link rel="stylesheet" href="views/header.css">
<link rel="stylesheet" href="views/page-header.css">

<!-- JavaScript necesario para el layout -->
<script src="public/js/main.js"></script>
<script src="public/js/header.js"></script>
<script src="public/js/page-header.js"></script>

<style>
/* Layout con sidebar y header fijo */
.layout-wrapper {
    position: relative;
    min-height: 100vh;
}

/* Contenido principal responsive automático - considerando header fijo */
.main-content {
    margin-left: 280px;
    margin-top: 60px; /* Altura del header fijo */
    padding: 2rem;
    transition: margin-left 0.3s ease;
    min-height: calc(100vh - 60px);
    font-family: var(--font);
}

.sidebar-collapsed .main-content {
    margin-left: 60px;
}

@media (max-width: 390px) {
    .main-content {
        margin-left: 0;
        margin-top: 56px;
        padding: 1rem;
        min-height: calc(100vh - 56px);
    }
}

@media (max-width: 768px) and (min-width: 391px) {
    .main-content {
        margin-left: 0;
        margin-top: 56px;
        padding: 1.5rem;
        min-height: calc(100vh - 56px);
    }
}

@media (max-width: 1024px) and (min-width: 769px) {
    .main-content {
        margin-left: 240px;
    }
    
    .sidebar-collapsed .main-content {
        margin-left: 60px;
    }
}

@media (min-width: 1441px) {
    .main-content {
        margin-left: 320px;
        margin-top: 64px;
        padding: 2.5rem;
        min-height: calc(100vh - 64px);
    }
    
    .sidebar-collapsed .main-content {
        margin-left: 60px;
    }
}

/* El page-header ahora va dentro del contenido */
.page-content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}
</style>