<?php
require_once __DIR__ . '/../BaseModel.php';

class EscuelasModel extends BaseModel {
    protected function getTableName() {
        return 'Escuelas';
    }

    protected function getPrimaryKey() {
        return 'id_escuela';
    }

    protected function getOrderBy() {
        return 'nombre_escuela ASC';
    }

    // getAll() y getById() heredados de BaseModel

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
