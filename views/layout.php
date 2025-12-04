<?php 
require_once __DIR__ . '/../utils/asset-version.php';
include 'sidebar.php'; 
?>

<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="views/layout.css?v=<?php echo asset_version('views/layout.css'); ?>">
<link rel="stylesheet" href="views/sidebar.css?v=<?php echo asset_version('views/sidebar.css'); ?>">
<link rel="stylesheet" href="views/header.css?v=<?php echo asset_version('views/header.css'); ?>">
<link rel="stylesheet" href="views/pageHeader.css?v=<?php echo asset_version('views/pageHeader.css'); ?>">
<link rel="stylesheet" href="views/estudiante/inicio.css?v=<?php echo asset_version('views/estudiante/inicio.css'); ?>">
<link rel="stylesheet" href="public/css/modal.css?v=<?php echo asset_version('public/css/modal.css'); ?>">

<?php if(($_GET['page'] ?? '') === 'dashboard'): ?>
<link rel="stylesheet" href="views/estudiante/dashboard.css?v=<?php echo asset_version('views/estudiante/dashboard.css'); ?>">
<link rel="stylesheet" href="views/estudiante/tests.css?v=<?php echo asset_version('views/estudiante/tests.css'); ?>">
<?php endif; ?>

<script>
// Base path global para construcción de URLs (detectar desde pathname si no está definido)
if (!window.UNIMIND_BASE) {
    var pathname = window.location.pathname;
    if (pathname.includes('/unimind/') || pathname.startsWith('/unimind')) {
        window.UNIMIND_BASE = '/unimind';
    } else {
        window.UNIMIND_BASE = '';
    }
}
<?php if (isset($base) && $base): ?>
// Override desde PHP si está disponible
window.UNIMIND_BASE = '<?php echo $base; ?>';
<?php endif; ?>
</script>

<script>
(function() {
    'use strict';
    
    var savedCollapsed = localStorage.getItem("sidebarCollapsed");
    var isMobile = window.innerWidth < 768; 
    
    if (!isMobile) {
        var shouldBeCollapsed = savedCollapsed === "false" ? false : true;
        
        if (shouldBeCollapsed) {
            document.documentElement.classList.add('sidebar-initially-collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
        
        if (savedCollapsed === null) {
            localStorage.setItem("sidebarCollapsed", "true");
        }
    }
    
    document.documentElement.classList.add('no-initial-transitions');
})();
</script>

<script src="public/js/modal.js?v=<?php echo asset_version('public/js/modal.js'); ?>"></script>
<script src="public/js/main-simple.js?v=<?php echo asset_version('public/js/main-simple.js'); ?>"></script>
<script src="public/js/idb-wrapper.js?v=<?php echo asset_version('public/js/idb-wrapper.js'); ?>"></script>
<script src="public/js/pwa-sync.js?v=<?php echo asset_version('public/js/pwa-sync.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const script = document.createElement('script');
    script.src = 'public/js/header.js?v=<?php echo asset_version('public/js/header.js'); ?>';
    document.head.appendChild(script);
    
    setTimeout(() => {
        document.documentElement.classList.remove('no-initial-transitions');
    }, 100);
});
</script>