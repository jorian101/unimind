<?php
class Database {
    private $host = 'localhost'; // o 'localhost'
    private $db_name = 'db_tests_estres_ansiedad';
    private $username = 'root';
    private $password = ''; // Cambia esto
    private $conn;

    public function connect() {
        $this->conn = null;
        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8';
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            echo 'Error de Conexión: ' . $e->getMessage();
        }
        return $this->conn;
    }
}
?>