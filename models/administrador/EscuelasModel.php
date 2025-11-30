<?php
require_once __DIR__ . '/../../database/Database.php';

class EscuelasModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Obtener todas las escuelas ordenadas por nombre
     */
    public function getAll() {
        try {
            $stmt = $this->conn->query('SELECT * FROM Escuelas ORDER BY nombre_escuela');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EscuelasModel::getAll error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener una escuela por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM Escuelas WHERE id_escuela = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('EscuelasModel::getById error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear una nueva escuela
     */
    public function create($data) {
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO Escuelas (nombre_escuela, telefono) VALUES (:nombre, :telefono)'
            );
            return $stmt->execute([
                ':nombre' => $data['nombre_escuela'],
                ':telefono' => $data['telefono'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('EscuelasModel::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar una escuela existente
     */
    public function update($id, $data) {
        try {
            $stmt = $this->conn->prepare(
                'UPDATE Escuelas SET nombre_escuela = :nombre, telefono = :telefono WHERE id_escuela = :id'
            );
            return $stmt->execute([
                ':id' => $id,
                ':nombre' => $data['nombre_escuela'],
                ':telefono' => $data['telefono'] ?? null
            ]);
        } catch (PDOException $e) {
            error_log('EscuelasModel::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar una escuela
     */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare('DELETE FROM Escuelas WHERE id_escuela = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('EscuelasModel::delete error: ' . $e->getMessage());
            return false;
        }
    }
}

?>
