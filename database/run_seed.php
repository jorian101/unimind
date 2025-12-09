<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// run_seed.php — Ejecuta el archivo seed.sql para poblar datos en la base de datos
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_tests_estres_ansiedad';
$sqlFile = __DIR__ . '/seed.sql';

// Preguntar al usuario si desea llenar la base de datos con datos o empezar vacía
echo "¿Desea llenar la base de datos con datos de seed.sql? (s/n): ";
$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) !== 's') {
    echo "Se ha elegido no llenar la base de datos con datos. Saliendo...\n";
    exit(0);
}

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

// Primero importar procedimientos (si existe)
$procFile = __DIR__ . '/procedures.sql';
if (file_exists($procFile)) {
    echo "Importando procedimientos desde procedures.sql...\n";
    $procSql = file_get_contents($procFile);

    // Eliminar líneas DELIMITER completamente
    $procSql = preg_replace('/DELIMITER\s+\/\/\s*/i', '', $procSql);
    $procSql = preg_replace('/DELIMITER\s+;\s*/i', '', $procSql);
    
    // Convertir todos los delimitadores '//' a ';'
    $procSql = preg_replace('/\/\/\s*$/m', ';', $procSql);
    $procSql = str_replace('END //', 'END;', $procSql);

    // Antes de crear, eliminar procedimientos y funciones con el mismo nombre
    preg_match_all('/CREATE\s+PROCEDURE\s+`?([a-zA-Z0-9_]+)`?/i', $procSql, $procMatches);
    preg_match_all('/CREATE\s+FUNCTION\s+`?([a-zA-Z0-9_]+)`?/i', $procSql, $funcMatches);
    
    if (!empty($procMatches[1])) {
        foreach ($procMatches[1] as $procName) {
            $dropSql = "DROP PROCEDURE IF EXISTS `" . $procName . "`;";
            if (!$mysqli->query($dropSql)) {
                echo "Aviso: no se pudo eliminar procedimiento $procName: " . $mysqli->error . "\n";
            }
        }
    }
    
    if (!empty($funcMatches[1])) {
        foreach ($funcMatches[1] as $funcName) {
            $dropSql = "DROP FUNCTION IF EXISTS `" . $funcName . "`;";
            if (!$mysqli->query($dropSql)) {
                echo "Aviso: no se pudo eliminar función $funcName: " . $mysqli->error . "\n";
            }
        }
    }

    if ($mysqli->multi_query($procSql)) {
        do {
            if ($res = $mysqli->store_result()) {
                $res->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
        echo "procedures.sql importado correctamente.\n";
    } else {
        echo "Error al ejecutar procedures.sql: " . $mysqli->error . "\n";
        // Continuar para intentar ejecutar seed, pero informar el error
    }
} else {
    echo "No se encontró procedures.sql, se omite la importación de procedimientos.\n";
}

// Ahora ejecutar seed.sql
echo "Ejecutando seed.sql...\n";
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