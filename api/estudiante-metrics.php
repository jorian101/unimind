<?php
/**
 * API: Métricas de Estudiante
 * Endpoint para obtener métricas detalladas de estrés y ansiedad de un estudiante
 */

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../database/Database.php';

// Verificar que se recibió el ID del estudiante
$id_estudiante = $_GET['id_estudiante'] ?? null;

if (!$id_estudiante) {
    echo json_encode(['error' => 'ID de estudiante requerido']);
    exit;
}

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Obtener las últimas aplicaciones de tests de estrés y ansiedad
    $stmt = $conn->prepare("
        SELECT 
            t.tipo_test,
            a.puntuacion_total,
            a.resultado_nivel,
            a.fecha_aplicacion,
            t.nombre as nombre_test
        FROM Aplicaciones a
        JOIN Tests t ON a.id_test = t.id_test
        WHERE a.id_usuario = :id_usuario
        AND t.tipo_test IN ('estres', 'ansiedad')
        AND a.puntuacion_total IS NOT NULL
        ORDER BY a.fecha_aplicacion DESC
    ");
    $stmt->execute([':id_usuario' => $id_estudiante]);
    $aplicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Organizar datos por tipo
    $estresData = [];
    $ansiedadData = [];
    
    foreach ($aplicaciones as $app) {
        $data = [
            'puntuacion' => (int)$app['puntuacion_total'],
            'nivel' => $app['resultado_nivel'],
            'fecha' => $app['fecha_aplicacion'],
            'test' => $app['nombre_test']
        ];
        
        if ($app['tipo_test'] === 'estres') {
            $estresData[] = $data;
        } else if ($app['tipo_test'] === 'ansiedad') {
            $ansiedadData[] = $data;
        }
    }
    
    // Calcular estadísticas
    $estresActual = !empty($estresData) ? $estresData[0]['puntuacion'] : 0;
    $ansiedadActual = !empty($ansiedadData) ? $ansiedadData[0]['puntuacion'] : 0;
    
    // Calcular promedios históricos (excluyendo el último)
    $estresPromedio = 0;
    if (count($estresData) > 1) {
        $sum = 0;
        for ($i = 1; $i < count($estresData); $i++) {
            $sum += $estresData[$i]['puntuacion'];
        }
        $estresPromedio = round($sum / (count($estresData) - 1));
    }
    
    $ansiedadPromedio = 0;
    if (count($ansiedadData) > 1) {
        $sum = 0;
        for ($i = 1; $i < count($ansiedadData); $i++) {
            $sum += $ansiedadData[$i]['puntuacion'];
        }
        $ansiedadPromedio = round($sum / (count($ansiedadData) - 1));
    }
    
    // Calcular tendencia (comparar actual con promedio previo)
    $estresTendencia = 'neutral';
    if ($estresPromedio > 0) {
        $diff = $estresActual - $estresPromedio;
        if ($diff > 5) $estresTendencia = 'aumentando';
        else if ($diff < -5) $estresTendencia = 'mejorando';
    }
    
    $ansiedadTendencia = 'neutral';
    if ($ansiedadPromedio > 0) {
        $diff = $ansiedadActual - $ansiedadPromedio;
        if ($diff > 5) $ansiedadTendencia = 'aumentando';
        else if ($diff < -5) $ansiedadTendencia = 'mejorando';
    }
    
    // Contar total de tests realizados
    $stmtTotal = $conn->prepare("
        SELECT COUNT(*) as total 
        FROM Aplicaciones 
        WHERE id_usuario = :id_usuario 
        AND puntuacion_total IS NOT NULL
    ");
    $stmtTotal->execute([':id_usuario' => $id_estudiante]);
    $totalTests = (int)$stmtTotal->fetchColumn();
    
    // Días desde último test
    $diasUltimoTest = 0;
    if (!empty($aplicaciones)) {
        $ultimaFecha = new DateTime($aplicaciones[0]['fecha_aplicacion']);
        $hoy = new DateTime();
        $interval = $hoy->diff($ultimaFecha);
        $diasUltimoTest = $interval->days;
    }
    
    // Preparar datos para gráficas (últimos 5 tests de cada tipo)
    $estresHistorico = array_slice($estresData, 0, 5);
    $ansiedadHistorico = array_slice($ansiedadData, 0, 5);
    
    // Revertir para mostrar cronológicamente
    $estresHistorico = array_reverse($estresHistorico);
    $ansiedadHistorico = array_reverse($ansiedadHistorico);
    
    $response = [
        'success' => true,
        'data' => [
            'estres' => [
                'actual' => $estresActual,
                'nivel' => !empty($estresData) ? $estresData[0]['nivel'] : 'No evaluado',
                'promedio' => $estresPromedio,
                'tendencia' => $estresTendencia,
                'historico' => $estresHistorico
            ],
            'ansiedad' => [
                'actual' => $ansiedadActual,
                'nivel' => !empty($ansiedadData) ? $ansiedadData[0]['nivel'] : 'No evaluado',
                'promedio' => $ansiedadPromedio,
                'tendencia' => $ansiedadTendencia,
                'historico' => $ansiedadHistorico
            ],
            'resumen' => [
                'total_tests' => $totalTests,
                'dias_ultimo_test' => $diasUltimoTest,
                'tiene_datos' => !empty($aplicaciones)
            ]
        ]
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Error en estudiante-metrics.php: " . $e->getMessage());
    echo json_encode([
        'error' => 'Error al obtener métricas',
        'message' => $e->getMessage()
    ]);
}
