<?php
// clean_database.php — Pide confirmación, borra la base de datos y vuelve a crearla ejecutando db.sql
$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'db_tests_estres_ansiedad';
$sqlFile = __DIR__ . '/db.sql';

// Confirmación interactiva
echo "ADVERTENCIA: Esto eliminará la base de datos '$dbName' y todos sus datos.\n";
echo "Escribe 'yes' para continuar: ";
$handle = fopen("php://stdin", "r");
$line = trim(fgets($handle));
fclose($handle);
if ($line !== 'yes') {
    echo "Operación cancelada por el usuario.\n";
    exit(0);
}

// Conectar a MySQL (sin seleccionar BD)
$mysqli = new mysqli($host, $user, $pass);
if ($mysqli->connect_errno) {
    echo "Error de conexión MySQL: " . $mysqli->connect_error . "\n";
    exit(1);
}

// Drop database
$dropSql = "DROP DATABASE IF EXISTS `$dbName`;";
if (!$mysqli->query($dropSql)) {
    echo "Error al dropear la base de datos: " . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}
echo "Base de datos '$dbName' eliminada (si existía).\n";

// Ejecutar db.sql para recrear la base y tablas
if (!file_exists($sqlFile)) {
    echo "Archivo SQL no encontrado: $sqlFile\n";
    $mysqli->close();
    exit(1);
}

$sql = file_get_contents($sqlFile);
if (!$mysqli->multi_query($sql)) {
    echo "Error al ejecutar db.sql: " . $mysqli->error . "\n";
    $mysqli->close();
    exit(1);
}

// Consumir todos los resultados
do {
    if ($res = $mysqli->store_result()) {
        $res->free();
    }
} while ($mysqli->more_results() && $mysqli->next_result());

echo "db.sql ejecutado correctamente. Base de datos '$dbName' recreada.\n";

$mysqli->close();
?>
