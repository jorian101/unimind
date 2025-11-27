<?php
// dashboard-profesor.php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

// 1. Lógica de Backend: Consultar Base de Datos
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$prof_courses = [];
$test_map = ['estres' => null, 'ansiedad' => null];

try {
    require_once __DIR__ . '/../../database/Database.php';
    
    if (isset($_SESSION['user_id'])) {
        $db = new Database();
        $conn = $db->connect();

        // A. Consultar los cursos asignados al profesor (ID y Nombre)
        $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = :id_profesor');
        $stmt->bindParam(':id_profesor', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $prof_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // B. Consultar IDs dinámicos de los Tests (para no hardcodear IDs)
        // Buscamos el ID del test cuyo nombre contiene 'estres'
        $stmt = $conn->prepare("SELECT id_test FROM Tests WHERE LOWER(nombre) LIKE '%estres%' LIMIT 1");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) $test_map['estres'] = (int)$r['id_test'];

        // Buscamos el ID del test cuyo nombre contiene 'ansiedad'
        $stmt = $conn->prepare("SELECT id_test FROM Tests WHERE LOWER(nombre) LIKE '%ansiedad%' LIMIT 1");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) $test_map['ansiedad'] = (int)$r['id_test'];
    }
} catch (Exception $e) {
    // En producción podrías loguear el error, aquí evitamos romper la vista
    error_log("Error DB Dashboard: " . $e->getMessage());
}
?>

<link rel="stylesheet" href="views/profesor/dashboard.css?v=<?php echo time(); ?>">

<script>
    // Inyectamos los datos de la BD en una variable global segura para que el JS externo la lea
    window.UnimindData = {
        courses: <?php echo json_encode($prof_courses ?: []); ?>,
        tests: <?php echo json_encode($test_map); ?>
    };
</script>

<main class="prof-dashboard-container" id="profDashboard">
    <div class="page-header">
        <div class="header-left">
            <h1><i class="fas fa-chalkboard-teacher"></i> Panel del Docente</h1>
            <p class="subtitle">Monitorea el bienestar de tu aula</p>
        </div>
    </div>

    <section class="recommendations-row">
        <h2 class="section-title">Recomendaciones</h2>
        <p class="section-subtitle">Recursos y estrategias para apoyar a tus salones</p>
        
        <div class="recommendations-cards">
            <div class="rec-card rec-card--stress">
                <div class="rec-top">
                    <div class="icon-box"><i class="fas fa-chart-line"></i></div>
                    <div class="rec-content">
                        <h3>Sugerir test de estrés</h3>
                        <p>Medir estrés de aula</p>
                    </div>
                </div>
                <div class="rec-actions">
                    <button class="btn-ver-mas btn-suggest" data-test-type="estres">
                        Sugerir <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="rec-card rec-card--anxiety">
                <div class="rec-top">
                    <div class="icon-box"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="rec-content">
                        <h3>Sugerir test de ansiedad</h3>
                        <p>Medir ansiedad de aula</p>
                    </div>
                </div>
                <div class="rec-actions">
                    <button class="btn-ver-mas btn-suggest" data-test-type="ansiedad">
                        Sugerir <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>

            <div class="rec-card rec-card--alert">
                <div class="rec-top">
                    <div class="icon-box"><i class="fas fa-bell"></i></div>
                    <div class="rec-content">
                        <h3>Niveles altos</h3>
                        <p>Acciones recomendadas</p>
                    </div>
                </div>
                <div class="rec-actions">
                    <button class="btn-ver-mas" onclick="window.location.href='?role=docente&page=dashboard-profesor'">
                        Ver Más <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <div id="suggestModal" class="modal">
        <div class="modal-content">
            <span class="close" id="closeSuggestModal">&times;</span>
            <h2><i class="fas fa-paper-plane"></i> Enviar Sugerencia</h2>
            
            <form id="suggestForm">
                <div class="form-group">
                    <label for="selectCourse">Selecciona el Curso:</label>
                    <select id="selectCourse" name="curso_id" required class="form-control">
                        <option value="" disabled selected>-- Cargando cursos... --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="displayTestName">Test a sugerir:</label>
                    <input type="text" id="displayTestName" disabled class="form-control-static">
                    <input type="hidden" id="hiddenTestId" name="test_id">
                    <input type="hidden" id="hiddenTestType" name="test_type">
                </div>

                <button type="submit" class="btn-primary" style="margin-top: 1rem;">Enviar Sugerencia</button>
            </form>
            
            <div id="suggestMsg" class="modal-msg"></div>
        </div>
    </div>

    <section class="charts-row">
        <div class="charts-grid">
            <div class="card chart-card chart-card--line">
                <h3>Evolución Temporal</h3>
                <small>Promedio por fecha</small>
                <div class="chart"><canvas id="prof-line"></canvas></div>
            </div>
            <div class="card chart-card chart-card--pie">
                <h3>Distribución por Nivel</h3>
                <small>Últimas aplicaciones</small>
                <div class="chart chart-pie-wrap">
                    <canvas id="prof-pie" width="140" height="140"></canvas>
                    <div class="pie-legend">
                        <div><span class="legend-color" style="background:#34D399"></span>Bajo</div>
                        <div><span class="legend-color" style="background:#FBBF24"></span>Medio</div>
                        <div><span class="legend-color" style="background:#F87171"></span>Alto</div>
                    </div>
                </div>
            </div>
            <div class="card chart-card chart-card--bar fullwidth">
                <h3>Niveles por Facultad</h3>
                <small>Comparativo</small>
                <div class="chart chart-bar-wrap"><canvas id="prof-bar"></canvas></div>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/prof-dashboard.js?v=<?php echo time(); ?>"></script>
<script src="/public/js/prof-charts.js?v=<?php echo time(); ?>"></script>
