<?php
$host = 'localhost';
$dbname = 'db_tests_estres_ansiedad';
$username = 'root';
$password = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo("Conecto");
} catch (\PDOException $e) {
    http_response_code(500);
    die("Error de conexión: " . $e->getMessage());
}
?>