<?php include 'sidebar.php'; ?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/sidebar.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/pageHeader.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/estudiante/inicio.css?v=<?php echo time(); ?>">

<script src="public/js/main-simple.js?v=<?php echo time(); ?>"></script>
<script>
    
document.addEventListener('DOMContentLoaded', function() {
    const script = document.createElement('script');
    script.src = 'public/js/header.js?v=<?php echo time(); ?>';
    document.head.appendChild(script);
});
</script>

<style>
.layout-wrapper {
    position: relative;
    min-height: 100vh;
}

/* Remove animation conflicts and set initial state based on localStorage */
.main-content {
    margin-left: 280px;
    margin-top: 60px;
    padding: 2rem;
    transition: margin-left 0.3s ease;
    min-height: calc(100vh - 60px);
    font-family: var(--font);
    /* Remove problematic fade animation */
    opacity: 1;
}

/* Ensure collapsed state is applied immediately */
body.sidebar-collapsed .main-content {
    margin-left: 60px !important;
}

/* Apply initial state based on localStorage before page renders */
@media (min-width: 769px) {
    .main-content {
        margin-left: <?php echo (isset($_COOKIE['sidebarCollapsed']) && $_COOKIE['sidebarCollapsed'] === 'true') ? '60px' : '280px'; ?>;
    }
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
    
    body.sidebar-collapsed .main-content {
        margin-left: 60px !important;
    }
}

@media (min-width: 1441px) {
    .main-content {
        margin-left: 320px;
        margin-top: 64px;
        padding: 2.5rem;
        min-height: calc(100vh - 64px);
    }
    
    body.sidebar-collapsed .main-content {
        margin-left: 60px !important;
    }
}

.page-content-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* Add CSS to prevent flash of unstyled content */
.sidebar {
    transition: width 0.3s ease;
}

.sidebar--collapsed {
    width: 60px !important;
}

/* Disable transitions during page load */
.no-transitions * {
    transition: none !important;
}
</style>

<script>
// Apply initial state immediately to prevent flash
(function() {
    const savedCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    const isMobile = window.innerWidth < 768;
    
    if (!isMobile && savedCollapsed) {
        document.documentElement.style.setProperty('--sidebar-width', '60px');
        document.body.classList.add('sidebar-collapsed');
    }
    
    // Add no-transitions class temporarily
    document.body.classList.add('no-transitions');
    
    // Remove no-transitions after a brief delay
    setTimeout(() => {
        document.body.classList.remove('no-transitions');
    }, 100);
})();
</script>