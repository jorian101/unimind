<?php
/**
 * API Controller para Dashboard de Estudiante
 * Endpoint: /api/dashboard/estudiante
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Headers para API REST
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejo de preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario']) || $_SESSION['cargo'] !== 'Estudiante') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado. Debe iniciar sesión como estudiante.',
        'redirect' => '../index.php?role=estudiante&page=login'
    ]);
    exit;
}

require_once __DIR__ . '/../models/estudiante/DashboardEstudianteModel.php';

try {
    $model = new DashboardEstudianteModel();
    $id_usuario = $_SESSION['id_usuario'];
    
    // Obtener acción
    $action = $_GET['action'] ?? 'estadisticas';
    
    switch ($action) {
        case 'estadisticas':
            // Obtener estadísticas completas del dashboard
            $estadisticas = $model->getEstadisticasDashboard($id_usuario);
            
            // Agregar métricas adicionales
            if ($estadisticas['estres']) {
                $estabilidad_estres = $model->getEstabilidadEmocional($id_usuario, 'estres');
                $percentil_curso_estres = $model->getPercentilCurso($id_usuario, 'estres');
                
                $estadisticas['estres']['estabilidad'] = $estabilidad_estres;
                $estadisticas['estres']['percentil_curso'] = $percentil_curso_estres;
            }
            
            if ($estadisticas['ansiedad']) {
                $estabilidad_ansiedad = $model->getEstabilidadEmocional($id_usuario, 'ansiedad');
                $percentil_curso_ansiedad = $model->getPercentilCurso($id_usuario, 'ansiedad');
                
                $estadisticas['ansiedad']['estabilidad'] = $estabilidad_ansiedad;
                $estadisticas['ansiedad']['percentil_curso'] = $percentil_curso_ansiedad;
            }
            
            // Detectar riesgo emergente
            $riesgo = $model->detectarRiesgoEmergente($id_usuario);
            $estadisticas['riesgo_emergente'] = $riesgo;
            
            // Calcular estado general
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
            
            echo json_encode([
                'success' => true,
                'data' => $estadisticas
            ]);
            break;
            
        case 'historial':
            // Obtener historial detallado
            $tipo_test = $_GET['tipo_test'] ?? 'estres';
            $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-6 months'));
            $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');
            
            if (!in_array($tipo_test, ['estres', 'ansiedad'])) {
                throw new Exception('Tipo de test inválido');
            }
            
            $historial = $model->getHistorialDetallado($id_usuario, $tipo_test, $fecha_inicio, $fecha_fin);
            
            echo json_encode([
                'success' => true,
                'data' => $historial
            ]);
            break;
            
        case 'estabilidad':
            // Obtener métricas de estabilidad emocional
            $tipo_test = $_GET['tipo_test'] ?? 'estres';
            
            if (!in_array($tipo_test, ['estres', 'ansiedad'])) {
                throw new Exception('Tipo de test inválido');
            }
            
            $estabilidad = $model->getEstabilidadEmocional($id_usuario, $tipo_test);
            
            echo json_encode([
                'success' => true,
                'data' => $estabilidad
            ]);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    error_log("Error en DashboardController: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos del dashboard: ' . $e->getMessage()
    ]);
}
