<?php
/**
 * Strategy Pattern para Autenticación y Redirección
 * 
 * Desacopla la lógica de redirección basada en roles
 * Facilita agregar nuevos roles sin modificar AuthController
 */

/**
 * Interfaz para estrategias de redirección
 */
interface RedirectStrategy {
    public function getRedirectUrl();
    public function getRoleName();
}

/**
 * Estrategia para Estudiantes
 */
class EstudianteRedirectStrategy implements RedirectStrategy {
    public function getRedirectUrl() {
        return '../index.php?role=estudiante&page=dashboard';
    }

    public function getRoleName() {
        return 'estudiante';
    }
}

/**
 * Estrategia para Profesores/Docentes
 */
class ProfesorRedirectStrategy implements RedirectStrategy {
    public function getRedirectUrl() {
        return '../index.php?role=docente&page=dashboard-profesor';
    }

    public function getRoleName() {
        return 'docente';
    }
}

/**
 * Estrategia para Administradores
 */
class AdministradorRedirectStrategy implements RedirectStrategy {
    public function getRedirectUrl() {
        return '../index.php?role=administrador&page=dashboard';
    }

    public function getRoleName() {
        return 'administrador';
    }
}

/**
 * Context: Manejador de Autenticación
 */
class AuthenticationContext {
    private $strategy;

    /**
     * Factory method para crear estrategia según rol
     */
    public static function createFromRole($role) {
        $context = new self();
        $normalizedRole = strtolower(trim($role));

        switch ($normalizedRole) {
            case 'estudiante':
                $context->setStrategy(new EstudianteRedirectStrategy());
                break;
            case 'docente':
            case 'profesor':
                $context->setStrategy(new ProfesorRedirectStrategy());
                break;
            case 'administrador':
            case 'admin':
                $context->setStrategy(new AdministradorRedirectStrategy());
                break;
            default:
                return null;
        }

        return $context;
    }

    public function setStrategy(RedirectStrategy $strategy) {
        $this->strategy = $strategy;
    }

    public function redirect() {
        if ($this->strategy === null) {
            header('Location: ../index.php?error=rol_invalido');
            exit;
        }

        header('Location: ' . $this->strategy->getRedirectUrl());
        exit;
    }

    public function getRoleName() {
        return $this->strategy ? $this->strategy->getRoleName() : null;
    }
}

/**
 * Clase helper para operaciones de autenticación
 */
class AuthHelper {
    /**
     * Configurar sesión de usuario autenticado
     */
    public static function setupSession($usuario) {
        // Mantener compatibilidad: algunos lugares usan 'user_id' y otros 'id_usuario'
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['user_name'] = $usuario['nombre'] . ' ' . $usuario['apellido'];
        $_SESSION['user_role'] = strtolower($usuario['cargo']);
        $_SESSION['id_rol'] = strtolower($usuario['cargo']);
        $_SESSION['cargo'] = $usuario['cargo'];
    }

    /**
     * Validar credenciales con la base de datos
     */
    public static function validateCredentials($codigo_usuario, $password) {
        try {
            $conn = Database::getInstance()->getConnection();
            
            $stmt = $conn->prepare("CALL sp_autenticar_usuario_por_codigo(:codigo_usuario)");
            $stmt->bindParam(':codigo_usuario', $codigo_usuario, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            // Validar password (en producción usar password_verify con hash)
            if ($usuario && $password === $usuario['password']) {
                return $usuario;
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error validando credenciales: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Limpiar sesión
     */
    public static function clearSession() {
        session_unset();
        session_destroy();
    }
}
?>
