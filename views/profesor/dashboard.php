<?php
// Profesor dashboard - restructured to match admin dashboard layout
// Ensure `$prof_courses` is available. If not provided by controller, load from DB.
if (!isset($prof_courses)) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $prof_courses = [];
    $test_ids = ['estres' => 0, 'ansiedad' => 0];

    if (isset($_SESSION['user_id'])) {
        try {
            require_once __DIR__ . '/../../controllers/ProfesorDashboardController.php';
            $pd = new ProfesorDashboardController();
            $prof_courses = $pd->getCursosPorProfesor((int) $_SESSION['user_id']);
            $test_ids = $pd->getTestIds();
        } catch (Exception $e) {
            // fallback: dejar variables por defecto
            $prof_courses = [];
            $test_ids = ['estres' => 0, 'ansiedad' => 0];
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
                <div id="riskSummary" style="margin-top:12px; padding:8px; background:#fef2f2; border-radius:6px; display:none;">
                    <div style="font-size:1.5rem; font-weight:700; color:#dc2626;"><span id="totalRiskCount">0</span></div>
                    <div style="font-size:0.8rem; color:#6b7280;">estudiantes en riesgo</div>
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

        <!-- Historial de Sugerencias -->
        <div class="card" style="background:#fff; border-radius:12px; padding:18px; box-shadow:0 2px 12px #0001; margin-bottom:28px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
                <h2 style="margin:0; color:#111827; font-size:1.1rem;">Historial de Sugerencias</h2>
                <button id="refreshHistorialBtn" class="btn btn-secondary" style="padding:6px 12px; border-radius:8px; font-size:0.9rem;">Refrescar</button>
            </div>
            <div id="historialTableWrap" style="overflow-x:auto;">
                <table id="historialTable" style="width:100%; border-collapse:collapse; font-size:0.9rem;">
                    <thead>
                        <tr style="background:#f3f4f6;">
                            <th style="padding:8px; text-align:left;">Curso</th>
                            <th style="padding:8px; text-align:left;">Test</th>
                            <th style="padding:8px; text-align:center;">Estudiantes Sugeridos</th>
                            <th style="padding:8px; text-align:center;">Completaron</th>
                            <th style="padding:8px; text-align:center;">Tasa Completitud</th>
                            <th style="padding:8px; text-align:center;">Estado</th>
                            <th style="padding:8px; text-align:left;">Última Sugerencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="padding:12px; text-align:center; color:#888;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
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
                        <th style="padding:8px; text-align:center;">Estudiantes</th>
                        <th style="padding:8px; text-align:center;">En Riesgo</th>
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

// Cargar historial de sugerencias
function loadHistorial() {
    const table = document.getElementById('historialTable');
    if (!table) return;
    
    fetch('api/prof_historial.php?limite=10')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.data) {
                table.querySelector('tbody').innerHTML = '<tr><td colspan="7" style="padding:12px; text-align:center; color:#888;">No hay datos disponibles</td></tr>';
                return;
            }
            
            const rows = data.data.map(item => {
                const tasa = parseFloat(item.tasa_completitud || 0);
                const tasaColor = tasa >= 70 ? '#059669' : (tasa >= 40 ? '#f59e0b' : '#dc2626');
                const estadoBadge = item.estado === 'pendiente' 
                    ? '<span style="background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; font-size:0.8rem;">Pendiente</span>'
                    : '<span style="background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:4px; font-size:0.8rem;">Visto</span>';
                    
                return `<tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:8px;">${item.nombre_curso || '-'}</td>
                    <td style="padding:8px;">${item.nombre_test || '-'}</td>
                    <td style="padding:8px; text-align:center;">${item.estudiantes_sugeridos || 0}</td>
                    <td style="padding:8px; text-align:center;">${item.estudiantes_completaron || 0}</td>
                    <td style="padding:8px; text-align:center;">
                        <span style="font-weight:600; color:${tasaColor};">${tasa.toFixed(1)}%</span>
                    </td>
                    <td style="padding:8px; text-align:center;">${estadoBadge}</td>
                    <td style="padding:8px;">${item.ultima_sugerencia ? new Date(item.ultima_sugerencia).toLocaleDateString('es-ES', {year: 'numeric', month: 'short', day: 'numeric'}) : '-'}</td>
                </tr>`;
            }).join('');
            
            table.querySelector('tbody').innerHTML = rows || '<tr><td colspan="7" style="padding:12px; text-align:center; color:#888;">No hay sugerencias registradas</td></tr>';
        })
        .catch(err => {
            console.error('Error cargando historial:', err);
            table.querySelector('tbody').innerHTML = '<tr><td colspan="7" style="padding:12px; text-align:center; color:#dc2626;">Error al cargar datos</td></tr>';
        });
}

// Cargar al inicio
document.addEventListener('DOMContentLoaded', loadHistorial);

// Botón refrescar historial
const refreshHistorialBtn = document.getElementById('refreshHistorialBtn');
if (refreshHistorialBtn) {
    refreshHistorialBtn.addEventListener('click', loadHistorial);
}
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
                highLevelsTable.querySelector('tbody').innerHTML = '<tr><td colspan="5" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
                return;
            }
            
            // Calcular total de estudiantes en riesgo
            let totalRiesgo = 0;
            data.top_courses.forEach(row => {
                totalRiesgo += parseInt(row.estudiantes_riesgo || 0);
            });
            
            // Actualizar el resumen en la tarjeta principal
            const riskSummary = document.getElementById('riskSummary');
            const totalRiskCount = document.getElementById('totalRiskCount');
            if (riskSummary && totalRiskCount) {
                totalRiskCount.textContent = totalRiesgo;
                riskSummary.style.display = totalRiesgo > 0 ? 'block' : 'none';
            }
            
            const rows = data.top_courses.map(row => {
                const riesgo = parseInt(row.estudiantes_riesgo || 0);
                const total = parseInt(row.total_estudiantes || 0);
                const pctRiesgo = total > 0 ? Math.round((riesgo / total) * 100) : 0;
                const riesgoColor = pctRiesgo > 30 ? '#dc2626' : (pctRiesgo > 15 ? '#f59e0b' : '#6b7280');
                
                return `<tr>
                    <td style="padding:8px;">${row.nombre_curso}</td>
                    <td style="padding:8px; text-align:center;">${total}</td>
                    <td style="padding:8px; text-align:center; color:${riesgoColor}; font-weight:600;">${riesgo} (${pctRiesgo}%)</td>
                    <td style="padding:8px; text-align:center; color:#6366f1; font-weight:600;">${row.promedio_estres ?? '-'}</td>
                    <td style="padding:8px; text-align:center; color:#7c3aed; font-weight:600;">${row.promedio_ansiedad ?? '-'}</td>
                </tr>`;
            }).join('');
            highLevelsTable.querySelector('tbody').innerHTML = rows || '<tr><td colspan="5" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
        })
        .catch(() => {
            highLevelsMsg.textContent = 'Error al consultar los datos.';
            highLevelsMsg.style.display = 'block';
            highLevelsTable.querySelector('tbody').innerHTML = '<tr><td colspan="5" style="padding:12px; text-align:center; color:#888;">Sin datos</td></tr>';
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