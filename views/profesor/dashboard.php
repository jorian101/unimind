<?php
// Profesor dashboard - restructured to match admin dashboard layout
// Ensure `$prof_courses` is available. If not provided by controller, load from DB.
if (!isset($prof_courses)) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    require_once __DIR__ . '/../../database/Database.php';
    $prof_courses = [];
    if (isset($_SESSION['user_id'])) {
        try {
            $db = new Database();
            $conn = $db->connect();
            $stmt = $conn->prepare('CALL sp_obtener_cursos_por_profesor(:p_id_profesor)');
            $stmt->bindValue(':p_id_profesor', (int) $_SESSION['user_id'], PDO::PARAM_INT);
            $stmt->execute();
            $prof_courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // consume remaining resultsets to avoid issues with subsequent queries
            try {
                while ($stmt->nextRowset()) { /* noop */ }
            } catch (Throwable $e) {
                // Some drivers may not support nextRowset; ignore silently
            }
            // Buscar los ids de test por tipo_test
            $test_ids = ['estres' => 0, 'ansiedad' => 0];
            try {
                $q = $conn->prepare("SELECT id_test, tipo_test FROM Tests WHERE tipo_test IN ('estres','ansiedad') AND estado_test = 'activo'");
                $q->execute();
                $all = $q->fetchAll(PDO::FETCH_ASSOC);
                foreach ($all as $r) {
                    if ($r['tipo_test'] === 'estres') $test_ids['estres'] = (int)$r['id_test'];
                    if ($r['tipo_test'] === 'ansiedad') $test_ids['ansiedad'] = (int)$r['id_test'];
                }
            } catch (Throwable $e) { /* ignore */ }
        } catch (Exception $e) {
            // on error leave $prof_courses as empty array
            $prof_courses = [];
        }
    }
}
?>
<script>
// expose small dataset for the frontend JS
window.UnimindData = window.UnimindData || {};
window.UnimindData.courses = <?php echo json_encode(array_values($prof_courses)); ?>;
window.UnimindData.tests = <?php echo json_encode(['estres' => intval($test_ids['estres'] ?? 0), 'ansiedad' => intval($test_ids['ansiedad'] ?? 0)]); ?>;
</script>
<link rel="stylesheet" href="dashboard.css">
<div class="admin-dashboard" style="min-height:100vh; background:#f6f6f6; padding:0; font-family:'Inter',sans-serif;">
    <div class="dashboard-container" style="max-width:98vw; margin:32px auto; padding:0 2vw; background:#fff; border-radius:24px; box-shadow:0 4px 24px #0002;">
        <h1 class="page-title" style="color:#1f2937; font-size:2.2rem; font-weight:700; margin-bottom:8px; margin-top:0;">Panel del Docente</h1>
        <p class="page-subtitle" style="color:#6b7280; margin-bottom:24px; margin-top:0;">Monitorea el bienestar y sugiere intervenciones a tus estudiantes</p>

        <div class="quick-actions" style="display:flex; gap:16px; margin-bottom:28px; align-items:center;">
            <div style="flex:1;">
                <label for="chartCourseSelect" style="display:block; font-weight:600; color:#374151; margin-bottom:6px;">Ver datos por curso</label>
                <select id="chartCourseSelect" class="form-control" style="width:100%; padding:10px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff;">
                    <option value="">Todos los cursos</option>
                    <?php if (!empty($prof_courses)) foreach ($prof_courses as $c): ?>
                        <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['nombre_curso']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="display:flex; gap:8px;">
                <button id="refreshDashboardBtn" class="btn btn-primary" style="padding:10px 14px; border-radius:10px;">Refrescar</button>
            </div>
        </div>

        <div class="card-container" style="display:flex; gap:20px; margin-bottom:32px;">
            <div class="card" style="flex:1; background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px #0001;">
                <h3 style="margin:0 0 12px 0; color:#374151; font-size:1.05rem;">Sugerir Tests</h3>
                <p style="margin:0 0 12px 0; color:#6b7280;">Envía una sugerencia de test a todo el curso.</p>
                <div style="display:flex; gap:10px; margin-top:6px;">
                    <button class="btn btn-primary btn-suggest" data-test-type="estres" style="padding:10px 12px; border-radius:8px;">Sugerir test de estrés</button>
                    <button class="btn btn-primary btn-suggest" data-test-type="ansiedad" style="padding:10px 12px; border-radius:8px;">Sugerir test de ansiedad</button>
                </div>
            </div>

            <div class="card attention" style="width:320px; background:#fff; border-radius:12px; padding:20px; box-shadow:0 2px 12px #0001; border-left:4px solid #f87171;">
                <div style="display:flex; gap:12px; align-items:center;">
                    <div style="font-size:22px; color:#f87171;">▲</div>
                    <div>
                        <h3 style="margin:0; color:#374151; font-size:1rem;">Niveles altos</h3>
                        <p style="margin:4px 0 0 0; color:#6b7280;">Cursos que requieren atención prioritaria</p>
                    </div>
                </div>
                <div style="margin-top:12px; text-align:right;"><button id="openHighLevelsModal" class="btn btn-link" style="color:#b91c1c; text-decoration:none; background:none; border:none; cursor:pointer;">Ver detalles</button></div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:28px;">
            <div class="card" style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 12px #0001;">
                <h2 style="margin-top:0; color:#111827; font-size:1.1rem;">Evolución Temporal</h2>
                <canvas id="prof-line" style="width:100%; height:180px;"></canvas>
            </div>

            <div class="card" style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 12px #0001;">
                <h2 style="margin-top:0; color:#111827; font-size:1.1rem;">Distribución por Nivel</h2>
                <canvas id="prof-pie" style="width:100%; height:180px;"></canvas>
                <div class="pie-legend" style="display:flex; gap:8px; margin-top:10px;">
                    <div style="display:flex; flex-direction:column; align-items:flex-start;"><span class="legend-val" style="font-weight:700;">-</span><small style="color:#6b7280;">Bajo</small></div>
                    <div style="display:flex; flex-direction:column; align-items:flex-start;"><span class="legend-val" style="font-weight:700;">-</span><small style="color:#6b7280;">Moderado</small></div>
                    <div style="display:flex; flex-direction:column; align-items:flex-start;"><span class="legend-val" style="font-weight:700;">-</span><small style="color:#6b7280;">Alto</small></div>
                </div>
            </div>
        </div>

        <div class="card" style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 12px #0001; margin-bottom:28px;">
            <h2 style="margin-top:0; color:#111827; font-size:1.1rem;">Niveles por Facultad</h2>
            <canvas id="prof-bar" style="width:100%; height:180px;"></canvas>
            <div class="chart-legend" style="margin-top:12px;">
                <span class="legend-item" style="margin-right:12px;"><span style="display:inline-block;width:12px;height:8px;background:#7c3aed;margin-right:6px;vertical-align:middle;"></span> Ansiedad</span>
                <span class="legend-item"><span style="display:inline-block;width:12px;height:8px;background:#6366f1;margin-right:6px;vertical-align:middle;"></span> Estrés</span>
            </div>
        </div>

        <!-- Botón 'Administrar tests' eliminado por solicitud -->
    </div>
</div>

<!-- Suggest Modal (opens when clicking .btn-suggest) -->
<div id="suggestModal" class="modal" aria-hidden="true" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); z-index:1200;">
    <div class="modal-content" role="dialog" aria-modal="true" style="background:#fff; border-radius:10px; padding:18px; width:480px; max-width:96vw; position:relative;">
        <button id="closeSuggestModal" aria-label="Cerrar" style="position:absolute; right:10px; top:10px; background:transparent; border:0; font-size:18px;">×</button>
        <h3 style="margin-top:0;">Sugerir Test</h3>
        <form id="suggestForm">
            <div style="margin-bottom:10px;">
                <label for="selectCourse" style="display:block; font-weight:600; margin-bottom:6px;">Curso destino</label>
                <select id="selectCourse" name="curso_id" style="width:100%; padding:8px; border-radius:6px; border:1px solid #e5e7eb;">
                    <option value="">-- Selecciona un curso --</option>
                    <?php foreach ($prof_courses as $c): ?>
                        <option value="<?php echo $c['id_curso']; ?>"><?php echo htmlspecialchars($c['nombre_curso']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:10px;">
                <label style="display:block; font-weight:600; margin-bottom:6px;">Test</label>
                <input id="displayTestName" type="text" disabled style="width:100%; padding:8px; border-radius:6px; border:1px solid #e5e7eb; background:#f9fafb;" />
                <input id="hiddenTestId" type="hidden" name="id_test" />
                <input id="hiddenTestType" type="hidden" name="test_type" />
            </div>

            <div id="suggestMsg" class="modal-msg" role="status" aria-live="polite" style="display:none; margin-bottom:8px;"></div>

            <div style="display:flex; gap:8px; justify-content:flex-end;">
                <button type="button" id="cancelSuggest" class="btn btn-secondary" style="padding:8px 12px; border-radius:8px; background:#f3f4f6;">Cancelar</button>
                <button type="submit" class="btn btn-primary" style="padding:8px 12px; border-radius:8px;">Enviar sugerencia</button>
            </div>
        </form>
    </div>
</div>

<!-- High Levels Modal -->
<div id="highLevelsModal" class="modal" aria-hidden="true" style="display:none; position:fixed; inset:0; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); z-index:1300;">
    <div class="modal-content" role="dialog" aria-modal="true" style="background:#fff; border-radius:10px; padding:18px; width:520px; max-width:96vw; position:relative;">
        <button id="closeHighLevelsModal" aria-label="Cerrar" style="position:absolute; right:10px; top:10px; background:transparent; border:0; font-size:18px;">×</button>
        <h3 style="margin-top:0;">Cursos con Promedios Más Altos</h3>
        <div id="highLevelsMsg" style="margin-bottom:10px; display:none;"></div>
        <div id="highLevelsTableWrap">
            <table id="highLevelsTable" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f3f4f6;">
                        <th style="padding:8px; text-align:left;">Curso</th>
                        <th style="padding:8px; text-align:center;">Promedio Estrés</th>
                        <th style="padding:8px; text-align:center;">Promedio Ansiedad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="3" style="padding:12px; text-align:center; color:#888;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="public/js/prof-dashboard.js"></script>
<script src="public/js/prof-charts.js"></script>
<script>
// Refrescar todo el dashboard al hacer click en el botón
document.getElementById('refreshDashboardBtn').addEventListener('click', function() {
    window.location.reload();
});
</script>
<script>
// High Levels Modal logic
const openHighLevelsModalBtn = document.getElementById('openHighLevelsModal');
const highLevelsModal = document.getElementById('highLevelsModal');
const closeHighLevelsModalBtn = document.getElementById('closeHighLevelsModal');
const highLevelsTable = document.getElementById('highLevelsTable');
const highLevelsMsg = document.getElementById('highLevelsMsg');

function showHighLevelsModal() {
    highLevelsModal.style.display = 'flex';
    document.body.classList.add('no-scroll');
    highLevelsMsg.style.display = 'none';
    // Fetch data from backend
    fetch('api/prof_metrics.php?top_courses=1')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.top_courses) {
                highLevelsMsg.textContent = 'No se pudo obtener los datos.';
                highLevelsMsg.style.display = 'block';
                highLevelsTable.querySelector('tbody').innerHTML = '<tr><td colspan="3" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
                return;
            }
            const rows = data.top_courses.map(row =>
                `<tr>
                    <td style=\"padding:8px;\">${row.nombre_curso}</td>
                    <td style=\"padding:8px; text-align:center; color:#6366f1; font-weight:600;\">${row.promedio_estres ?? '-'}</td>
                    <td style=\"padding:8px; text-align:center; color:#7c3aed; font-weight:600;\">${row.promedio_ansiedad ?? '-'}</td>
                </tr>`
            ).join('');
            highLevelsTable.querySelector('tbody').innerHTML = rows || '<tr><td colspan="3" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
        })
        .catch(() => {
            highLevelsMsg.textContent = 'Error al consultar los datos.';
            highLevelsMsg.style.display = 'block';
            highLevelsTable.querySelector('tbody').innerHTML = '<tr><td colspan="3" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
        });
}

openHighLevelsModalBtn.addEventListener('click', showHighLevelsModal);
closeHighLevelsModalBtn.addEventListener('click', () => {
    highLevelsModal.style.display = 'none';
    document.body.classList.remove('no-scroll');
});
window.addEventListener('keydown', e => {
    if (e.key === 'Escape' && highLevelsModal.style.display === 'flex') {
        highLevelsModal.style.display = 'none';
        document.body.classList.remove('no-scroll');
    }
});
highLevelsModal.addEventListener('click', e => {
    if (e.target === highLevelsModal) {
        highLevelsModal.style.display = 'none';
        document.body.classList.remove('no-scroll');
    }
});
</script>