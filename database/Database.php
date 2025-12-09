<?php
/**
 * Database Singleton Pattern
 * Garantiza una única instancia de conexión PDO en toda la aplicación
 * Evita múltiples conexiones innecesarias
 */
class Database {
    private static $instance = null;
    private $conn;
    
    private $host = 'localhost'; 
    private $db_name = 'db_tests_estres_ansiedad';
    private $username = 'root';
    private $password = '';

    /**
     * Constructor privado para prevenir instanciación directa
     */
    private function __construct() {
        try {
            $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->db_name . ';charset=utf8';
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log('Error de Conexión Database: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prevenir clonación de la instancia
     */
    private function __clone() {}

    /**
     * Prevenir deserialización de la instancia
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }

    /**
     * Obtener la instancia única de Database
     * @return Database
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener la conexión PDO
     * @return PDO
     */
    public function getConnection() {
        return $this->conn;
    }

    /**
     * DEPRECATED: Método mantenido para retrocompatibilidad
     * Use getInstance()->getConnection() en su lugar
     * @deprecated
     */
    public function connect() {
        return $this->conn;
    }
}
?>