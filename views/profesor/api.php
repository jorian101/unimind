<?php
// 1. Establecer encabezado de respuesta como JSON
header('Content-Type: application/json');

// 2. Incluir dependencias
require_once 'database/Database.php';
require_once 'models/profesor/DashboardModel.php';

// 3. Iniciar sesión y obtener ID del profesor
// session_start();
// $id_profesor_logueado = $_SESSION['id_usuario'] ?? null;
$id_profesor_logueado = 1; // ID (quemado) para pruebas

if (!$id_profesor_logueado) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// 4. Inicializar Conexión y Modelo
$database = new Database();
$db = $database->connect();
$dashboardModel = new DashboardModel($db);

// 5. Determinar el curso a cargar (automáticamente el primero)
$id_curso_seleccionado = null;
$nombre_curso_seleccionado = "Sin cursos asignados";
$lista_cursos = $dashboardModel->getCursosPorProfesor($id_profesor_logueado);

if (!empty($lista_cursos)) {
    $id_curso_seleccionado = $lista_cursos[0]['id_curso'];
    $nombre_curso_seleccionado = $lista_cursos[0]['nombre_curso'];
}

// 6. Manejar solicitudes POST (Sugerir Test)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id_test = $input['id_test'] ?? 0;
    
    // El curso ID se toma del curso automático, no de la entrada
    if ($id_test > 0 && $id_curso_seleccionado > 0) {
        $dashboardModel->sugerirTestACurso($id_curso_seleccionado, $id_test);
        echo json_encode(['success' => true, 'message' => 'Test sugerido correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la solicitud o sin curso asignado']);
    }
    exit;
}

// 7. Manejar solicitudes GET (Obtener datos del Dashboard)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $datos = [
        'id_curso_seleccionado' => (int)$id_curso_seleccionado,
        'nombre_curso_seleccionado' => $nombre_curso_seleccionado,
        'conteo_niveles_altos' => 0,
        'data_temporal' => [],
        'data_riesgo' => [],
        'data_escuelas' => [] // Este es global
    ];

    if ($id_curso_seleccionado) {
        $datos['conteo_niveles_altos'] = $dashboardModel->getConteoNivelesAltosPorCurso($id_curso_seleccionado);
        
        $raw_data_temporal = $dashboardModel->getEvolucionTemporalPorCurso($id_curso_seleccionado);
        $raw_data_riesgo = $dashboardModel->getDistribucionRiesgoPorCurso($id_curso_seleccionado);

        // --- Procesar Riesgo ---
        $chart_labels_risk_html = []; $chart_labels_risk_js = []; $chart_data_risk = [];
        $colores_riesgo = ['Mínimo/Bajo' => '#34d399', 'Medio/Moderado' => '#f59e0b', 'Alto/Severo' => '#ef4444', 'Alto' => '#ef4444', 'Sin Nivel' => '#9ca3af'];
        $colores_riesgo_ordenados = [];
        $total_riesgo = array_sum(array_column($raw_data_riesgo, 'conteo'));
        foreach ($raw_data_riesgo as $row) {
            $nivel_actual = $row['nivel_riesgo'] ?? 'Sin Nivel'; $porcentaje = ($total_riesgo > 0) ? round(($row['conteo'] / $total_riesgo) * 100) : 0;
            $label_html = $nivel_actual . ' (' . $porcentaje . '%)';
            $chart_labels_risk_html[] = ['label' => $label_html, 'color' => $colores_riesgo[$nivel_actual] ?? '#9ca3af'];
            $chart_labels_risk_js[] = $nivel_actual; $chart_data_risk[] = $row['conteo'];
            $colores_riesgo_ordenados[] = $colores_riesgo[$nivel_actual] ?? '#9ca3af';
        }
        $datos['data_riesgo'] = ['labels_html' => $chart_labels_risk_html, 'labels_js' => $chart_labels_risk_js, 'data' => $chart_data_risk, 'colors' => $colores_riesgo_ordenados];

        // --- Procesar Temporal ---
        $chart_labels_temporal = []; $chart_data_temporal_stress = []; $chart_data_temporal_anxiety = []; $map_temporal = [];
        foreach ($raw_data_temporal as $row) {
            $semana_parts = explode('-', $row['etiqueta_temporal']); $semana_num = $semana_parts[1] ?? 'N/A';
            $map_temporal[$row['etiqueta_temporal']]['label'] = 'Sem ' . $semana_num;
            if (stripos($row['nombre_test'], 'Estrés') !== false) { $map_temporal[$row['etiqueta_temporal']]['estres'] = (float)$row['promedio_puntuacion']; }
            if (stripos($row['nombre_test'], 'Ansiedad') !== false) { $map_temporal[$row['etiqueta_temporal']]['ansiedad'] = (float)$row['promedio_puntuacion']; }
        }
        foreach ($map_temporal as $sem) {
            $chart_labels_temporal[] = $sem['label']; $chart_data_temporal_stress[] = $sem['estres'] ?? 0; $chart_data_temporal_anxiety[] = $sem['ansiedad'] ?? 0;
        }
        $datos['data_temporal'] = ['labels' => $chart_labels_temporal, 'stress' => $chart_data_temporal_stress, 'anxiety' => $chart_data_temporal_anxiety];
    }
    
    // --- Procesar Escuelas (Global) ---
    $raw_data_escuelas = $dashboardModel->getComparativaEscuelas();
    $chart_labels_faculty = []; $chart_data_faculty_stress = []; $chart_data_faculty_anxiety = []; $map_escuelas = [];
    foreach ($raw_data_escuelas as $row) {
        $map_escuelas[$row['nombre_escuela']]['label'] = $row['nombre_escuela'];
        if (stripos($row['nombre_test'], 'Estrés') !== false) { $map_escuelas[$row['nombre_escuela']]['estres'] = (float)$row['promedio_puntuacion']; }
        if (stripos($row['nombre_test'], 'Ansiedad') !== false) { $map_escuelas[$row['nombre_escuela']]['ansiedad'] = (float)$row['promedio_puntuacion']; }
    }
    foreach ($map_escuelas as $esc) {
        $chart_labels_faculty[] = $esc['label']; $chart_data_faculty_stress[] = $esc['estres'] ?? 0; $chart_data_faculty_anxiety[] = $esc['ansiedad'] ?? 0;
    }
    $datos['data_escuelas'] = ['labels' => $chart_labels_faculty, 'stress' => $chart_data_faculty_stress, 'anxiety' => $chart_data_faculty_anxiety];
    
    // 8. Enviar respuesta JSON
    echo json_encode($datos);
    exit;
}
?>