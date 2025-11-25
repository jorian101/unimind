<?php
require_once __DIR__ . '/../database/Database.php';

class UserController {
    private $conn;
    public $lastError = null;
    private $table = 'Usuarios';

    public function __construct() {
        $database = new Database();
        $this->conn = $database->connect();
    }

    // Obtener todos los usuarios
    public function getAllUsuarios() {
        try {
            $stmt = $this->conn->prepare("SELECT id_usuario as id, codigo_usuario, CONCAT(nombre, ' ', apellido) as nombre, email, cargo as rol FROM {$this->table} ORDER BY nombre ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            return [];
        }
    }

    // Obtener roles disponibles
    public function getRoles() {
        return ['Estudiante', 'Docente', 'Administrador'];
    }

    // Procesar acciones POST (crear, editar, eliminar)
    public function handlePost() {
        $action = $_POST['action'] ?? '';
        if ($action === 'delete') {
            return $this->deleteUsuario($_POST['id']);
        }
        $id = $_POST['id'] ?? '';
        $codigo_usuario = $_POST['codigo_usuario'] ?? '';
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $rol = $_POST['rol'] ?? '';
        $password = $_POST['password'] ?? '';
        if ($id) {
            return $this->updateUsuario($id, $codigo_usuario, $nombre, $email, $rol, $password);
        } else {
            return $this->createUsuario($codigo_usuario, $nombre, $email, $rol, $password);
        }
    }

    // Crear usuario
    public function createUsuario($codigo_usuario, $nombre, $email, $rol, $password) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO {$this->table} (codigo_usuario, nombre, apellido, email, cargo, password) VALUES (?, ?, '', ?, ?, ?)");
            $stmt->execute([$codigo_usuario, $nombre, $email, $rol, password_hash($password, PASSWORD_DEFAULT)]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Editar usuario
    public function updateUsuario($id, $codigo_usuario, $nombre, $email, $rol, $password) {
        try {
            $sql = "UPDATE {$this->table} SET codigo_usuario=?, nombre=?, email=?, cargo=?";
            $params = [$codigo_usuario, $nombre, $email, $rol];
            if ($password) {
                $sql .= ", password=?";
                $params[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $sql .= " WHERE id_usuario=?";
            $params[] = $id;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // Eliminar usuario
    public function deleteUsuario($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id_usuario=?");
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

// Si es petición POST, procesar y devolver JSON
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new UserController();
    $result = $controller->handlePost();
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
