<?php
/**
 * UsuariosModel - Modelo para gestión de usuarios (Administrador)
 * Extiende BaseModel para usar Template Method Pattern
 */
require_once __DIR__ . '/../BaseModel.php';

class UsuariosModel extends BaseModel {
    
    /**
     * Implementación del Template Method: nombre de la tabla
     */
    protected function getTableName(): string {
        return 'Usuarios';
    }
    
    /**
     * Implementación del Template Method: clave primaria
     */
    protected function getPrimaryKey(): string {
        return 'id_usuario';
    }
    
    /**
     * Buscar usuarios con filtros (cargo, búsqueda)
     * 
     * @param string $cargo Cargo a filtrar (Estudiante, Docente, Administrador)
     * @param string $busqueda Término de búsqueda (nombre, apellido, código)
     * @return array Lista de usuarios que cumplen los filtros
     */
    public function buscarUsuarios(string $cargo = '', string $busqueda = ''): array {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE 1";
        $params = [];
        
        // Filtro por cargo
        if ($cargo && in_array($cargo, ['Estudiante', 'Docente', 'Administrador'])) {
            $sql .= " AND cargo = ?";
            $params[] = $cargo;
        }
        
        // Filtro por búsqueda (nombre, apellido, código)
        if ($busqueda) {
            $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR codigo_usuario LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql .= " ORDER BY fecha_registro DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Obtener usuario por código
     * 
     * @param string $codigo_usuario Código del usuario
     * @return array|null Datos del usuario o null si no existe
     */
    public function getByCodigoUsuario(string $codigo_usuario): ?array {
        $sql = "SELECT * FROM {$this->getTableName()} WHERE codigo_usuario = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$codigo_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Crear nuevo usuario con generación automática de código
     * 
     * @param array $data Datos del usuario [nombre, apellido, cargo, password, etc.]
     * @return array ['success' => bool, 'id' => int, 'codigo' => string]
     */
    public function crearUsuario(array $data): array {
        try {
            $this->conn->beginTransaction();
            
            // Insert con código vacío (se generará después)
            $sql = "INSERT INTO {$this->getTableName()} 
                    (nombre, apellido, codigo_usuario, password, cargo, 
                     fecha_nacimiento, genero, fecha_registro) 
                    VALUES (?, ?, '', ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['password'], // Sin encriptar (según requerimiento actual)
                $data['cargo'],
                $data['fecha_nacimiento'] ?? null,
                $data['genero'] ?? null
            ]);
            
            $nuevoId = intval($this->conn->lastInsertId());
            
            // Generar código: YYYY-ID
            $year = date('Y');
            $codigo_generado = $year . '-' . $nuevoId;
            
            $updateSql = "UPDATE {$this->getTableName()} SET codigo_usuario = ? WHERE {$this->getPrimaryKey()} = ?";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->execute([$codigo_generado, $nuevoId]);
            
            // Relaciones opcionales
            if (isset($data['id_curso']) && $data['id_curso']) {
                if ($data['cargo'] === 'Estudiante') {
                    // Inscribir estudiante en curso
                    $stmt = $this->conn->prepare('INSERT INTO Usuario_Curso (id_usuario, id_curso) VALUES (?, ?)');
                    $stmt->execute([$nuevoId, $data['id_curso']]);
                } elseif ($data['cargo'] === 'Docente') {
                    // Asignar docente al curso
                    $stmt = $this->conn->prepare('UPDATE Cursos SET id_profesor = ? WHERE id_curso = ?');
                    $stmt->execute([$nuevoId, $data['id_curso']]);
                }
            }
            
            // Vincular con escuela si se proporciona
            if (isset($data['id_escuela']) && $data['id_escuela']) {
                $stmt = $this->conn->prepare('INSERT IGNORE INTO Usuario_Escuela (id_usuario, id_escuela) VALUES (?, ?)');
                $stmt->execute([$nuevoId, $data['id_escuela']]);
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'id' => $nuevoId,
                'codigo' => $codigo_generado
            ];
            
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Error al crear usuario: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar usuario con manejo condicional de password
     * 
     * @param int $id ID del usuario
     * @param array $data Datos a actualizar
     * @return bool True si se actualizó correctamente
     */
    public function actualizarUsuario(int $id, array $data): bool {
        try {
            // Construir query dinámicamente según si hay password
            $fields = [
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'codigo_usuario' => $data['codigo_usuario'],
                'cargo' => $data['cargo'],
                'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
                'genero' => $data['genero'] ?? null
            ];
            
            $params = array_values($fields);
            
            // Solo actualizar password si se proporciona
            if (isset($data['password']) && $data['password'] !== '') {
                $sql = "UPDATE {$this->getTableName()} 
                        SET nombre = ?, apellido = ?, codigo_usuario = ?, cargo = ?, 
                            fecha_nacimiento = ?, genero = ?, password = ? 
                        WHERE {$this->getPrimaryKey()} = ?";
                $params[] = $data['password'];
            } else {
                $sql = "UPDATE {$this->getTableName()} 
                        SET nombre = ?, apellido = ?, codigo_usuario = ?, cargo = ?, 
                            fecha_nacimiento = ?, genero = ? 
                        WHERE {$this->getPrimaryKey()} = ?";
            }
            
            $params[] = $id;
            
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
            
        } catch (PDOException $e) {
            error_log("Error al actualizar usuario: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un docente tiene cursos asignados
     * 
     * @param int $id_usuario ID del usuario
     * @return int Cantidad de cursos asignados
     */
    public function contarCursosAsignados(int $id_usuario): int {
        $sql = "SELECT COUNT(*) FROM Cursos WHERE id_profesor = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return intval($stmt->fetchColumn());
    }
    
    /**
     * Obtener cargo del usuario
     * 
     * @param int $id_usuario ID del usuario
     * @return string|null Cargo del usuario
     */
    public function getCargo(int $id_usuario): ?string {
        $sql = "SELECT cargo FROM {$this->getTableName()} WHERE {$this->getPrimaryKey()} = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id_usuario]);
        return $stmt->fetchColumn() ?: null;
    }
    
    /**
     * Eliminar usuario usando stored procedure
     * 
     * @param int $id ID del usuario
     * @return array Mensaje del procedimiento almacenado
     */
    public function eliminarUsuario(int $id): array {
        try {
            // Verificar si es docente con cursos asignados
            $cargo = $this->getCargo($id);
            if ($cargo === 'Docente') {
                $cursosAsignados = $this->contarCursosAsignados($id);
                if ($cursosAsignados > 0) {
                    return [
                        'success' => false,
                        'error' => 'El docente tiene cursos asignados. Reasigna o elimina los cursos antes de eliminar al docente.'
                    ];
                }
            }
            
            // Usar stored procedure para eliminación con CASCADE
            $stmt = $this->conn->prepare('CALL sp_eliminar_usuario(?)');
            $stmt->execute([$id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return [
                'success' => true,
                'mensaje' => $result['Mensaje'] ?? 'Usuario eliminado correctamente'
            ];
            
        } catch (PDOException $e) {
            error_log("Error al eliminar usuario: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener lista de cargos disponibles
     * 
     * @return array Lista de cargos
     */
    public function getCargosDisponibles(): array {
        return ['Estudiante', 'Docente', 'Administrador'];
    }
    
    /**
     * Autocompletado para búsqueda de usuarios
     * 
     * @param string $query Término de búsqueda
     * @param int $limit Límite de resultados
     * @return array Lista de usuarios para autocompletado
     */
    public function autocompletar(string $query, int $limit = 10): array {
        $sql = "SELECT id_usuario, nombre, apellido, codigo_usuario, cargo 
                FROM {$this->getTableName()} 
                WHERE nombre LIKE ? OR apellido LIKE ? OR codigo_usuario LIKE ? 
                LIMIT ?";
        
        $stmt = $this->conn->prepare($sql);
        $searchTerm = "%$query%";
        $stmt->bindValue(1, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(2, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(3, $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(4, $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
