<?php include 'sidebar.php'; ?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="public/css/theme.css">
<link rel="stylesheet" href="views/sidebar.css">
<link rel="stylesheet" href="views/header.css">
<link rel="stylesheet" href="views/page-header.css">

<script src="public/js/main-simple.js?v=<?php echo time(); ?>"></script>
<script src="public/js/header.js"></script>

<style>
.layout-wrapper {
    position: relative;
    min-height: 100vh;
}

.main-content {
    margin-left: 280px;
    margin-top: 60px;
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

.page-content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
}
</style>