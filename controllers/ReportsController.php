<?php
/**
 * ReportsController
 * Controlador para operaciones de resumen y reportes usados en el dashboard
 */

require_once __DIR__ . '/../utils/ModelFactory.php';

class ReportsController {
    private $model;

    public function __construct() {
        $this->model = ModelFactory::create('administrador', 'reports');
        if ($this->model === null) {
            // Fallback: intentar incluir directamente
            require_once __DIR__ . '/../models/administrador/ReportsModel.php';
            $this->model = new ReportsModel();
        }
    }

    /**
     * Obtener conteos para widgets del dashboard
     * @return array ['usuarios'=>int,'cursos'=>int,'escuelas'=>int,'tests'=>int]
     */
    public function getSummaryCounts(): array {
        try {
            return $this->model->getSummaryCounts();
        } catch (Exception $e) {
            error_log('ReportsController::getSummaryCounts: ' . $e->getMessage());
            return ['usuarios'=>0,'cursos'=>0,'escuelas'=>0,'tests'=>0];
        }
    }

    /**
     * Obtener actividad reciente para el dashboard
     * @param int $limit
     * @return array
     */
    public function getActividadReciente(int $limit = 5): array {
        try {
            return $this->model->getActividadReciente($limit);
        } catch (Exception $e) {
            error_log('ReportsController::getActividadReciente: ' . $e->getMessage());
            return [];
        }
    }
}

?>
