<?php
require_once __DIR__ . '/../../database/Database.php';

class CursosModel {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    /**
     * Obtener todos los cursos con información de escuela y profesor
     */
    public function getAllWithDetails() {
        try {
            $sql = "SELECT c.*, e.nombre_escuela, u.nombre AS profesor_nombre, u.apellido AS profesor_apellido 
                    FROM Cursos c
                    JOIN Escuelas e ON c.id_escuela = e.id_escuela
                    JOIN Usuarios u ON c.id_profesor = u.id_usuario
                    ORDER BY c.nombre_curso";
            $stmt = $this->conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('CursosModel::getAllWithDetails error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un curso por ID
     */
    public function getById($id) {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM Cursos WHERE id_curso = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('CursosModel::getById error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Crear un nuevo curso
     */
    public function create($data) {
        try {
            $stmt = $this->conn->prepare(
                'INSERT INTO Cursos (nombre_curso, id_escuela, id_profesor) 
                 VALUES (:nombre, :id_escuela, :id_profesor)'
            );
            return $stmt->execute([
                ':nombre' => $data['nombre_curso'],
                ':id_escuela' => $data['id_escuela'],
                ':id_profesor' => $data['id_profesor']
            ]);
        } catch (PDOException $e) {
            error_log('CursosModel::create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualizar un curso existente
     */
    public function update($id, $data) {
        try {
            $stmt = $this->conn->prepare(
                'UPDATE Cursos 
                 SET nombre_curso = :nombre, id_escuela = :id_escuela, id_profesor = :id_profesor 
                 WHERE id_curso = :id'
            );
            return $stmt->execute([
                ':id' => $id,
                ':nombre' => $data['nombre_curso'],
                ':id_escuela' => $data['id_escuela'],
                ':id_profesor' => $data['id_profesor']
            ]);
        } catch (PDOException $e) {
            error_log('CursosModel::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Eliminar un curso
     */
    public function delete($id) {
        try {
            $stmt = $this->conn->prepare('DELETE FROM Cursos WHERE id_curso = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('CursosModel::delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtener todos los profesores (usuarios con rol profesor)
     */
    public function getProfesores() {
        try {
            $stmt = $this->conn->query(
                "SELECT id_usuario, nombre, apellido 
                 FROM Usuarios 
                 WHERE rol = 'profesor' 
                 ORDER BY nombre, apellido"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('CursosModel::getProfesores error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
