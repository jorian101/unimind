<?php
require_once __DIR__ . '/../BaseModel.php';

class CursosModel extends BaseModel {
    protected function getTableName() {
        return 'Cursos';
    }

    protected function getPrimaryKey() {
        return 'id_curso';
    }

    protected function getOrderBy() {
        return 'nombre_curso ASC';
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

    // getById() heredado de BaseModel

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

    // delete() heredado de BaseModel

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

    /**
     * Obtener cursos asignados a un profesor por su id
     * Devuelve array con `id_curso` y `nombre_curso`
     */
    public function getByProfesor($id_profesor) {
        try {
            $stmt = $this->conn->prepare(
                'SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = :id_profesor ORDER BY nombre_curso'
            );
            $stmt->execute([':id_profesor' => $id_profesor]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('CursosModel::getByProfesor error: ' . $e->getMessage());
            return [];
        }
    }
}

?>
