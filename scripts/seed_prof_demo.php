<?php
/**
 * Seeder PHP para poblar datos demo usados por el dashboard del profesor.
 * Uso (desde la raíz del proyecto):
 *   php scripts/seed_prof_demo.php
 */

require_once __DIR__ . '/../database/Database.php';

$db = new Database();
$conn = $db->connect();

if (!$conn) {
    echo "No se pudo conectar a la base de datos.\n";
    exit(1);
}

try {
    $sql = file_get_contents(__DIR__ . '/seed_prof_demo.sql');
    if ($sql === false) {
        throw new Exception('No se encontró seed_prof_demo.sql');
    }

    echo "Ejecutando seed_prof_demo.sql...\n";
    $conn->exec($sql);
    echo "Seed ejecutado correctamente.\n";
    echo "Verifica la tabla Cursos, Usuarios y Aplicaciones.\n";
} catch (PDOException $e) {
    echo "Error de base de datos: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

?>
