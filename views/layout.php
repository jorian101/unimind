<?php include 'sidebar.php'; ?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/layout.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/sidebar.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/header.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/pageHeader.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/estudiante/inicio.css?v=<?php echo time(); ?>">

<?php if(($_GET['page'] ?? '') === 'dashboard'): ?>
<link rel="stylesheet" href="views/estudiante/dashboard.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="views/estudiante/tests.css?v=<?php echo time(); ?>">
<?php endif; ?>

<script>
(function() {
    'use strict';
    
    var savedCollapsed = localStorage.getItem("sidebarCollapsed") === "true";
    var isMobile = window.innerWidth < 769;
    
    if (!isMobile && savedCollapsed) {
        document.documentElement.classList.add('sidebar-initially-collapsed');
    }
    
    document.documentElement.classList.add('no-initial-transitions');
})();
</script>

<script src="public/js/main-simple.js?v=<?php echo time(); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const script = document.createElement('script');
    script.src = 'public/js/header.js?v=<?php echo time(); ?>';
    document.head.appendChild(script);
    
    setTimeout(() => {
        document.documentElement.classList.remove('no-initial-transitions');
    }, 100);
});
</script>