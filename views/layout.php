<?php include 'sidebar.php'; ?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/sidebar.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/pageHeader.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/estudiante/inicio.css?v=<?php echo time(); ?>">

<!-- Apply initial state IMMEDIATELY before any CSS loads -->
<script>
// CRITICAL: Apply state before DOM renders to prevent flash
(function() {
    'use strict';
    
    // Get saved state immediately
    var savedCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    var isMobile = window.innerWidth < 769; // Changed to match CSS breakpoint
    
    if (!isMobile && savedCollapsed) {
        // Add class to html element immediately
        document.documentElement.classList.add('sidebar-initially-collapsed');
    }
    
    // Prevent any transitions during initial load
    document.documentElement.classList.add('no-initial-transitions');
})();
</script>

<style>
.layout-wrapper {
    position: relative;
    min-height: 100vh;
}

/* Restore normal main-content behavior */
.main-content {
    margin-left: 280px;
    margin-top: 60px;
    padding: 2rem;
    transition: margin-left 0.3s ease;
    min-height: calc(100vh - 60px);
    font-family: var(--font);
    opacity: 1;
}

/* Handle initial collapsed state */
.sidebar-initially-collapsed .main-content {
    margin-left: 60px !important;
}

/* Disable all transitions during initial load */
.no-initial-transitions .sidebar,
.no-initial-transitions .main-content,
.no-initial-transitions * {
    transition: none !important;
    animation: none !important;
}

/* Normal state classes */
body.sidebar-collapsed .main-content {
    margin-left: 60px !important;
}

@media (max-width: 390px) {
    .main-content {
        margin-left: 0 !important;
        margin-top: 56px;
        padding: 1rem;
        min-height: calc(100vh - 56px);
    }
    
    .sidebar-initially-collapsed .main-content {
        margin-left: 0 !important;
    }
}

/* Fix 768px breakpoint to behave like mobile */
@media (max-width: 768px) and (min-width: 391px) {
    .main-content {
        margin-left: 0 !important;
        margin-top: 56px;
        padding: 1.5rem;
        min-height: calc(100vh - 56px);
    }
    
    .sidebar-initially-collapsed .main-content {
        margin-left: 0 !important;
    }
}

@media (max-width: 1024px) and (min-width: 769px) {
    .main-content {
        margin-left: 240px;
    }
    
    body.sidebar-collapsed .main-content {
        margin-left: 60px !important;
    }
    
    .sidebar-initially-collapsed .main-content {
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
    
    .sidebar-initially-collapsed .main-content {
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
</style>

<script src="public/js/main-simple.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const script = document.createElement('script');
    script.src = 'public/js/header.js?v=<?php echo time(); ?>';
    document.head.appendChild(script);
    
    // Remove no-transitions class after everything is loaded
    setTimeout(() => {
        document.documentElement.classList.remove('no-initial-transitions');
    }, 100);
});
</script>