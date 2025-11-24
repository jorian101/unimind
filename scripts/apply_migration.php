<?php
// Usage: php apply_migration.php path/to/migration.sql
if ($argc < 2) {
    echo "Usage: php apply_migration.php path/to/migration.sql\n";
    exit(1);
}

$file = $argv[1];
if (!file_exists($file)) {
    echo "Migration file not found: $file\n";
    exit(1);
}

require_once __DIR__ . '/../database/Database.php';
$sql = file_get_contents($file);
if ($sql === false) {
    echo "Unable to read migration file\n";
    exit(1);
}

$db = new Database();
$conn = $db->connect();
// Execute each statement individually and continue on non-fatal errors
$statements = preg_split('/;\s*\n/', $sql);
$errors = [];
foreach ($statements as $stmt) {
    $stmt = trim($stmt);
    if (!$stmt) continue;
    try {
        $conn->exec($stmt);
    } catch (PDOException $e) {
        // Collect error but continue (useful for idempotent migrations)
        $errors[] = $e->getMessage();
    }
}

if (count($errors) > 0) {
    echo "Migration applied with warnings. Last error: " . end($errors) . "\n";
} else {
    echo "Migration applied: $file\n";
}

exit(0);
