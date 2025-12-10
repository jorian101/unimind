<?php
require_once __DIR__ . '/../database/Database.php';
/**
 * ModelFactory - Factory Method Pattern
 * 
 * Centraliza la creación de modelos según el contexto/rol del usuario.
 * Desacopla controllers de la instanciación directa de models.
 * 
 * Beneficios:
 * - Punto único de creación de modelos
 * - Facilita testing con mocks
 * - Permite cambiar implementaciones sin modificar controllers
 */
class ModelFactory {
    /**
     * Crear modelo según rol y tipo
     * 
     * @param string $role Rol del usuario (administrador, profesor, estudiante)
     * @param string $modelType Tipo de modelo (tests, cursos, escuelas, etc.)
     * @return BaseModel|null
     */
    public static function create($role, $modelType) {
        $modelClass = self::getModelClass($role, $modelType);
        
        if ($modelClass && class_exists($modelClass)) {
            // Inyectar la conexión PDO al constructor del modelo si espera una
            try {
                $conn = \Database::getInstance()->getConnection();
            } catch (Exception $e) {
                $conn = null;
            }

            if ($conn !== null) {
                return new $modelClass($conn);
            }

            return new $modelClass();
        }
        
        error_log("ModelFactory: No se encontró modelo para role=$role, type=$modelType");
        return null;
    }

    /**
     * Mapeo de roles y tipos a clases de modelos
     */
    private static function getModelClass($role, $modelType) {
        $modelMap = [
            'administrador' => [
                'tests' => 'TestsModel',
                'cursos' => 'CursosModel',
                'escuelas' => 'EscuelasModel',
                'reports' => 'ReportsModel',
                'escalas' => 'EscalasModel',
                'usuarios' => 'UsuariosModel'
            ],
            'profesor' => [
                'dashboard' => 'DashboardModel',
                'tests' => 'TestModel'
            ],
            'estudiante' => [
                'tests' => 'TestsEstudianteModel'
            ],
            'docente' => [ // Alias para profesor
                'dashboard' => 'DashboardModel',
                'tests' => 'TestModel'
            ]
        ];

        if (!isset($modelMap[$role][$modelType])) {
            return null;
        }

        $className = $modelMap[$role][$modelType];
        
        // Determinar la ruta del archivo según el rol
        $basePath = __DIR__ . '/../models/';
        $paths = [
            "$basePath$role/$className.php",
            "$basePath$className.php",
            "$basePath/administrador/$className.php",
            "$basePath/profesor/$className.php",
            "$basePath/estudiante/$className.php"
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                return $className;
            }
        }

        return null;
    }

    /**
     * Crear modelo de Tests según el rol del usuario activo
     * Detecta automáticamente el rol de la sesión
     */
    public static function createTestsModel() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $role = $_SESSION['user_role'] ?? $_SESSION['id_rol'] ?? 'estudiante';
        
        // Normalizar rol
        if ($role === 'docente') {
            $role = 'profesor';
        }

        return self::create($role, 'tests');
    }

    /**
     * Crear modelo genérico sin especificar rol
     * Útil para modelos compartidos
     */
    public static function createShared($modelType) {
        $sharedModels = [
            'cursos' => 'CursosModel',
            'escuelas' => 'EscuelasModel',
            'usuarios' => 'UsuariosModel'
        ];

        if (isset($sharedModels[$modelType])) {
            $className = $sharedModels[$modelType];
            $path = __DIR__ . "/../models/administrador/$className.php";
            
            if (file_exists($path)) {
                require_once $path;
                try {
                    $conn = Database::getInstance()->getConnection();
                } catch (Exception $e) {
                    $conn = null;
                }

                if ($conn !== null) {
                    return new $className($conn);
                }

                return new $className();
            }
        }

        return null;
    }
}
?>
