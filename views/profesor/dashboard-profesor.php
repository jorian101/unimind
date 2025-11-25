<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>

<link rel="stylesheet" href="views/profesor/dashboard.css?v=<?php echo time(); ?>">
<?php
// Embedir lista de cursos y tests relevantes para que el JS pueda enviar sugerencias
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$prof_courses = [];
$test_map = ['estres' => null, 'ansiedad' => null];
try {
    require_once __DIR__ . '/../../database/Database.php';
    if (isset($_SESSION['user_id'])) {
        $db = new Database();
        $conn = $db->connect();
        $stmt = $conn->prepare('SELECT id_curso, nombre_curso FROM Cursos WHERE id_profesor = :id_profesor');
        $stmt->bindParam(':id_profesor', $_SESSION['user_id'], PDO::PARAM_INT);
        $stmt->execute();
        $prof_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // buscar tests por nombre (primer match)
        $stmt = $conn->prepare("SELECT id_test, nombre FROM Tests WHERE LOWER(nombre) LIKE '%estres%' LIMIT 1");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) $test_map['estres'] = (int)$r['id_test'];

        $stmt = $conn->prepare("SELECT id_test, nombre FROM Tests WHERE LOWER(nombre) LIKE '%ansiedad%' LIMIT 1");
        $stmt->execute();
        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($r) $test_map['ansiedad'] = (int)$r['id_test'];
    }
} catch (Exception $e) {
    // no bloquear la vista si falla
}
?>
<script>
window.__PROF_SUGGEST = {
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

    <!-- Mantener únicamente las opciones de sugerir y la tarjeta 'Niveles altos' -->
    <section class="recommendations-row">
        <h2>Recomendaciones</h2>
        <p class="subtitle">Recursos y estrategias para apoyar a tus salones</p>
        <div class="recommendations-cards">
            <div class="rec-card rec-card--stress" data-test-id="1" aria-label="Sugerir test de estrés">
                <div class="icon" aria-hidden="true">📈</div>
                <div class="rec-content">
                    <h3>Sugerir test de estrés</h3>
                    <p class="muted">Medir estrés de aula</p>
                </div>
                <div class="rec-actions">
                    <button class="btn-primary btn-suggest" data-test="estres" aria-label="Sugerir test de estrés">Sugerir</button>
                </div>
            </div>

            <div class="rec-card rec-card--anxiety" data-test-id="2" aria-label="Sugerir test de ansiedad">
                <div class="icon" aria-hidden="true">⚠️</div>
                <div class="rec-content">
                    <h3>Sugerir test de ansiedad</h3>
                    <p class="muted">Medir ansiedad de aula</p>
                </div>
                <div class="rec-actions">
                    <button class="btn-primary btn-suggest" data-test="ansiedad" aria-label="Sugerir test de ansiedad">Sugerir</button>
                </div>
            </div>

            <div class="rec-card rec-card--alert" data-test-id="3" aria-label="Niveles altos - acciones recomendadas">
                <div class="icon" aria-hidden="true">🔔</div>
                <div class="rec-content">
                    <h3>Niveles altos</h3>
                    <p class="muted">Acciones recomendadas cuando hay niveles altos</p>
                </div>
                <div class="rec-actions">
                    <button class="btn-outline" onclick="window.location.href='?role=docente&page=dashboard-profesor'" aria-label="Ver más niveles altos">Ver Más</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para sugerir test a curso -->
    <div id="suggestModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" id="closeSuggestModal">&times;</span>
            <h2>Sugerir Test a Curso</h2>
            <form id="suggestForm">
                <label for="selectCourse">Curso:</label>
                <select id="selectCourse" name="curso" required></select>
                <label for="selectTest">Test:</label>
                <select id="selectTest" name="test" required>
                    <option value="estres">Test de Estrés</option>
                    <option value="ansiedad">Test de Ansiedad</option>
                </select>
                <button type="submit" class="btn-primary">Sugerir</button>
            </form>
            <div id="suggestMsg" class="modal-msg"></div>
        </div>
    </div>

    <div id="profMsg" class="prof-msg"></div>

    <!-- Gráficos (estáticos, maqueta visual) -->
    <section class="charts-row">
            <div class="charts-grid">
                    <div class="card chart-card chart-card--line">
                            <h3>Evolución Temporal</h3>
                            <small>Promedio por fecha</small>
                            <div class="chart">
                                    <canvas id="prof-line" aria-label="Evolución temporal" role="img"></canvas>
                            </div>
                    </div>

                    <div class="card chart-card chart-card--pie">
                        <h3>Distribución por Nivel de Riesgo</h3>
                        <small>Últimas aplicaciones</small>
                        <div class="chart chart-pie-wrap">
                            <canvas id="prof-pie" aria-label="Distribución por nivel" role="img" width="140" height="140"></canvas>
                            <div class="pie-legend">
                                <div><span class="legend-color" style="background:#34D399"></span><strong>Bajo</strong><small class="legend-val">—</small></div>
                                <div><span class="legend-color" style="background:#FBBF24"></span><strong>Moderado</strong><small class="legend-val">—</small></div>
                                <div><span class="legend-color" style="background:#F87171"></span><strong>Alto</strong><small class="legend-val">—</small></div>
                            </div>
                        </div>
                    </div>

                    <div class="card chart-card chart-card--bar fullwidth">
                            <h3>Niveles por Facultad</h3>
                            <small>Comparativo</small>
                            <div class="chart chart-bar-wrap">
                                    <canvas id="prof-bar" aria-label="Niveles por Facultad" role="img"></canvas>
                            </div>
                    </div>
            </div>
    </section>
</main>

<?php
// Fin de vista (se eliminaron secciones excepto sugerencias y 'Niveles altos')
?>
<script src="/unimind/public/js/prof-suggest.js?v=<?php echo time(); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/public/js/prof-charts.js?v=<?php echo time(); ?>"></script>
