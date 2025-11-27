<?php
// run_seed.php — Ejecuta el archivo seed.sql para poblar datos en la base de datos
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_tests_estres_ansiedad';
$sqlFile = __DIR__ . '/seed.sql';

if (!file_exists($sqlFile)) {
    echo "Archivo SQL no encontrado: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    echo "Error de conexión MySQL: " . $mysqli->connect_error . "\n";
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    // Consumir todos los resultados
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo "seed.sql ejecutado correctamente. Datos insertados (si no existían).\n";
} else {
    echo "Error al ejecutar seed.sql: " . $mysqli->error . "\n";
    exit(1);
}

$mysqli->close();

?>