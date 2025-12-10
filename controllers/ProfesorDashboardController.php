<?php
/**
 * ProfesorDashboardController
 * Controlador ligero para el dashboard del profesor
 */

require_once __DIR__ . '/../utils/ModelFactory.php';

class ProfesorDashboardController {
    private $model;

    public function __construct() {
        // Crear el modelo Dashboard vía ModelFactory (patrón Factory)
        $this->model = ModelFactory::create('profesor', 'dashboard');
        if ($this->model === null) {
            // Fallback directo
            require_once __DIR__ . '/../models/profesor/DashboardModel.php';
            $db = Database::getInstance()->getConnection();
            $this->model = new DashboardModel($db);
        }
    }

    public function getCursosPorProfesor(int $idProfesor): array {
        return $this->model->getCursosPorProfesor($idProfesor);
    }

    public function getTestIds(): array {
        // Devuelve ids por tipo: ['estres'=>id, 'ansiedad'=>id]
        $tests = $this->model->getAllTestsConDetalles();
        $out = ['estres' => 0, 'ansiedad' => 0];
        foreach ($tests as $t) {
            $tipo = strtolower($t['nombre'] ?? '');
            if (strpos($tipo, 'estres') !== false || strpos($t['nombre'] ?? '', 'estres') !== false) {
                $out['estres'] = (int)$t['id_test'];
            }
            if (strpos($tipo, 'ansiedad') !== false || strpos($t['nombre'] ?? '', 'ansiedad') !== false) {
                $out['ansiedad'] = (int)$t['id_test'];
            }
        }
        return $out;
    }

    public function getTestsDetalles(): array {
        return $this->model->getAllTestsConDetalles();
    }
}

?>
