<?php
/**
 * EstudianteDashboardController
 * Controlador para el dashboard del estudiante siguiendo patrón MVC
 */

require_once __DIR__ . '/../utils/ModelFactory.php';

class EstudianteDashboardController {
    private $model;

    public function __construct() {
        // Crear el modelo Dashboard vía ModelFactory (patrón Factory)
        $this->model = ModelFactory::create('estudiante', 'dashboard');
        if ($this->model === null) {
            // Fallback directo
            require_once __DIR__ . '/../models/estudiante/DashboardEstudianteModel.php';
            $this->model = new DashboardEstudianteModel();
        }
    }

    /**
     * Obtener todas las estadísticas del dashboard para un estudiante
     * Incluye estrés, ansiedad, global y riesgo emergente
     */
    public function getEstadisticasCompletas(int $idUsuario): array {
        try {
            // Obtener estadísticas base del modelo
            $estadisticas = $this->model->getEstadisticasDashboard($idUsuario);
            
            // Agregar métricas adicionales para estrés
            if ($estadisticas['estres']) {
                $estabilidad_estres = $this->model->getEstabilidadEmocional($idUsuario, 'estres');
                $percentil_curso_estres = $this->model->getPercentilCurso($idUsuario, 'estres');
                
                $estadisticas['estres']['estabilidad'] = $estabilidad_estres;
                $estadisticas['estres']['percentil_curso'] = $percentil_curso_estres;
            }
            
            // Agregar métricas adicionales para ansiedad
            if ($estadisticas['ansiedad']) {
                $estabilidad_ansiedad = $this->model->getEstabilidadEmocional($idUsuario, 'ansiedad');
                $percentil_curso_ansiedad = $this->model->getPercentilCurso($idUsuario, 'ansiedad');
                
                $estadisticas['ansiedad']['estabilidad'] = $estabilidad_ansiedad;
                $estadisticas['ansiedad']['percentil_curso'] = $percentil_curso_ansiedad;
            }
            
            // Detectar riesgo emergente
            $riesgo = $this->model->detectarRiesgoEmergente($idUsuario);
            $estadisticas['riesgo_emergente'] = $riesgo;
            
            // Calcular estado general basado en niveles
            $nivel_estres = $estadisticas['estres']['nivel_calculado'] ?? null;
            $nivel_ansiedad = $estadisticas['ansiedad']['nivel_calculado'] ?? null;
            
            $estado_general = 'Sin datos';
            $requiere_atencion = false;
            
            if ($nivel_estres || $nivel_ansiedad) {
                $niveles_orden = [
                    'normal' => 1,
                    'leve' => 2,
                    'moderado' => 3,
                    'alto' => 4,
                    'severo' => 5
                ];
                
                $orden_estres = $niveles_orden[$nivel_estres] ?? 0;
                $orden_ansiedad = $niveles_orden[$nivel_ansiedad] ?? 0;
                $orden_max = max($orden_estres, $orden_ansiedad);
                
                $estado_general = array_search($orden_max, $niveles_orden) ?: 'normal';
                $requiere_atencion = ($orden_max >= 4 || $riesgo['tiene_riesgo']);
            }
            
            $estadisticas['global']['estado_general'] = ucfirst($estado_general);
            $estadisticas['global']['requiere_atencion'] = $requiere_atencion;
            
            return $estadisticas;
            
        } catch (Exception $e) {
            error_log("Error en EstudianteDashboardController::getEstadisticasCompletas: " . $e->getMessage());
            
            // Retornar estructura vacía en caso de error
            return [
                'estres' => null,
                'ansiedad' => null,
                'global' => [
                    'total_tests' => 0,
                    'dias_ultimo_test' => null,
                    'total_tests_estres' => 0,
                    'total_tests_ansiedad' => 0,
                    'estado_general' => 'Sin datos',
                    'requiere_atencion' => false
                ],
                'riesgo_emergente' => [
                    'tiene_riesgo' => false,
                    'num_casos' => 0,
                    'casos' => []
                ]
            ];
        }
    }

    /**
     * Obtener historial detallado de aplicaciones
     */
    public function getHistorial(int $idUsuario, string $tipoTest, string $fechaInicio, string $fechaFin): array {
        try {
            if (!in_array($tipoTest, ['estres', 'ansiedad'])) {
                throw new Exception('Tipo de test inválido');
            }
            
            return $this->model->getHistorialDetallado($idUsuario, $tipoTest, $fechaInicio, $fechaFin);
        } catch (Exception $e) {
            error_log("Error en EstudianteDashboardController::getHistorial: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener métricas de estabilidad emocional
     */
    public function getEstabilidad(int $idUsuario, string $tipoTest): ?array {
        try {
            if (!in_array($tipoTest, ['estres', 'ansiedad'])) {
                throw new Exception('Tipo de test inválido');
            }
            
            return $this->model->getEstabilidadEmocional($idUsuario, $tipoTest);
        } catch (Exception $e) {
            error_log("Error en EstudianteDashboardController::getEstabilidad: " . $e->getMessage());
            return null;
        }
    }
}
