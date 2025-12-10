<?php
require_once __DIR__ . '/../database/Database.php';

/**
 * BaseModel - Template Method Pattern
 * 
 * Clase base abstracta que define el esqueleto de operaciones comunes
 * para todos los modelos. Reduce duplicación y garantiza consistencia.
 * 
 * Patrón Template Method: Define la estructura de algoritmos en la clase base,
 * permitiendo que las subclases redefinan pasos específicos sin cambiar la estructura.
 */
abstract class BaseModel {
    protected $conn;
    protected $table;
    public $lastError = null;

    /**
     * Constructor: Obtiene la conexión Singleton
     */
    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
        $this->initialize();
    }

    /**
     * Hook method: Permite a las subclases realizar inicialización adicional
     */
    protected function initialize() {
        // Override en subclases si es necesario
    }

    /**
     * Template Method: Obtener todos los registros
     * Las subclases pueden sobrescribir getTableName() y getOrderBy()
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM {$this->getTableName()} ORDER BY {$this->getOrderBy()}";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }

    /**
     * Template Method: Obtener por ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM {$this->getTableName()} WHERE {$this->getPrimaryKey()} = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return null;
        }
    }

    /**
     * Template Method: Eliminar registro
     */
    public function delete($id) {
        try {
            $query = "DELETE FROM {$this->getTableName()} WHERE {$this->getPrimaryKey()} = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return false;
        }
    }

    /**
     * Helper: Manejo centralizado de errores
     */
    protected function handleError($method, PDOException $e) {
        $this->lastError = $e->getMessage();
        error_log("$method error: " . $e->getMessage());
    }

    /**
     * Helper: Begin transaction
     */
    protected function beginTransaction() {
        return $this->conn->beginTransaction();
    }

    /**
     * Helper: Commit transaction
     */
    protected function commit() {
        return $this->conn->commit();
    }

    /**
     * Helper: Rollback transaction
     */
    protected function rollback() {
        return $this->conn->rollBack();
    }

    /**
     * Primitive operations - Deben ser implementadas por subclases
     */
    abstract protected function getTableName();
    abstract protected function getPrimaryKey();
    
    /**
     * Hook con implementación por defecto
     */
    protected function getOrderBy() {
        return $this->getPrimaryKey() . ' ASC';
    }
}
?>
