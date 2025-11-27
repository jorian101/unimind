<?php
// create_database.php — Ejecuta el archivo db.sql para crear la base de datos y tablas
$host = 'localhost';
$user = 'root';
$pass = '';
$sqlFile = __DIR__ . '/db.sql';

if (!file_exists($sqlFile)) {
    echo "Archivo SQL no encontrado: $sqlFile\n";
    exit(1);
}

$sql = file_get_contents($sqlFile);
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_errno) {
    echo "Error de conexión MySQL: " . $mysqli->connect_error . "\n";
    exit(1);
}

if ($mysqli->multi_query($sql)) {
    // Consumir todos los resultados para completar la ejecución
    do {
        if ($res = $mysqli->store_result()) {
            $res->free();
        }
    } while ($mysqli->more_results() && $mysqli->next_result());

    echo "db.sql ejecutado correctamente. Base de datos y tablas creadas (si no existían).\n";
} else {
    echo "Error al ejecutar db.sql: " . $mysqli->error . "\n";
    exit(1);
}

$mysqli->close();

?>