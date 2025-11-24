<?php
session_start();

// Solo permitir en localhost
if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
    die('No autorizado');
}

echo "<h1>Debug de Sesión</h1>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n\n";
echo "Contenido de \$_SESSION:\n";
print_r($_SESSION);
echo "\n\nCookies:\n";
print_r($_COOKIE);
echo "</pre>";
?>
