<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

// Ensure `$prof_courses` is available. If not provided by controller, load from DB.
if (!isset($prof_courses)) {
    $prof_courses = [];
    $test_ids = ['estres' => 0, 'ansiedad' => 0];

    if (isset($_SESSION['user_id'])) {
        try {
            require_once __DIR__ . '/../../controllers/ProfesorDashboardController.php';
            $pd = new ProfesorDashboardController();
            $prof_courses = $pd->getCursosPorProfesor((int) $_SESSION['user_id']);
            $test_ids = $pd->getTestIds();
        } catch (Exception $e) {
            $prof_courses = [];
            $test_ids = ['estres' => 0, 'ansiedad' => 0];
        }
    }
}
?>

<?php require_once __DIR__ . '/../../utils/asset-version.php'; ?>
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="public/css/style.css?v=<?php echo asset_version('public/css/style.css'); ?>">
<link rel="stylesheet" href="views/profesor/dashboard.css?v=<?php echo asset_version('views/profesor/dashboard.css'); ?>">

<script>
window.UnimindData = window.UnimindData || {};
window.UnimindData.courses = <?php echo json_encode(array_values($prof_courses)); ?>;
window.UnimindData.tests = <?php echo json_encode(['estres' => intval($test_ids['estres'] ?? 0), 'ansiedad' => intval($test_ids['ansiedad'] ?? 0)]); ?>;
</script>

<main class="admin-dashboard">
    <div class="dashboard-container">
        <div class="page-header">
            <div class="header-content">
                <h1 class="page-title"><i class="fas fa-chalkboard-user"></i> Panel del Docente</h1>
                <p class="page-subtitle">Monitorea el bienestar y sugiere intervenciones a tus estudiantes</p>
            </div>
            <div class="header-actions">
                <button id="refreshDashboardBtn" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Refrescar
                </button>
            </div>
        </div>

        <div class="quick-actions">
            <div class="filter-group">
                <label for="chartCourseSelect"><i class="fas fa-filter"></i> Ver datos por curso</label>
                <div class="select-wrapper">
                    <select id="chartCourseSelect" class="form-control select-filter">
                        <option value="">📚 Todos los cursos</option>
                        <?php if (!empty($prof_courses)) foreach ($prof_courses as $c): ?>
                            <option value="<?php echo $c['id_curso']; ?>">📖 <?php echo htmlspecialchars($c['nombre_curso']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <i class="fas fa-chevron-down select-icon"></i>
                </div>
            </div>
        </div>

        <section class="cards-row">
            <article class="card">
                <h3>Sugerir Tests</h3>
                <small>Envía una sugerencia de test a un curso.</small>
                <div style="display:flex; flex-wrap:wrap; gap:0.75rem; margin-top:1rem;">
                    <button class="btn btn-primary btn-suggest-modal">
                        <i class="fas fa-plus-circle"></i> Sugerir Test
                    </button>
                </div>
            </article>

            <article class="card attention" style="border-left:5px solid var(--pri-600); background: linear-gradient(90deg, var(--bg-100) 80%, var(--pri-50) 100%);">
                <div style="display:flex; gap:0.75rem; align-items:center;">
                    <div style="font-size:1.5rem; color:var(--pri-600);">⚠</div>
                    <div>
                        <h3 style="margin:0; font-size:1rem;">Niveles Altos</h3>
                        <small>Cursos que requieren atención prioritaria</small>
                    </div>
                </div>
                <div id="riskSummary" style="margin-top:0.75rem; padding:0.5rem; background:var(--pri-100); border-radius:6px; display:none;">
                    <div style="font-size:1.5rem; font-weight:700; color:var(--pri-600);"><span id="totalRiskCount">0</span></div>
                    <div style="font-size:0.8rem; color:var(--var-700);">estudiantes en riesgo</div>
                </div>
                <div style="margin-top:0.75rem; text-align:right;">
                    <button id="openHighLevelsModal" class="btn btn-link">Ver detalles</button>
                </div>
            </article>
        </section>

        <section class="charts-grid">
            <article class="card">
                <h2>Evolución Temporal</h2>
                <canvas id="prof-line"></canvas>
            </article>

            <article class="card">
                <h2>Distribución por Nivel</h2>
                <canvas id="prof-pie"></canvas>
                <div class="pie-legend">
                    <div><span class="legend-val">-</span><small>Bajo</small></div>
                    <div><span class="legend-val">-</span><small>Moderado</small></div>
                    <div><span class="legend-val">-</span><small>Alto</small></div>
                </div>
            </article>
        </section>

        <section class="card" style="margin-bottom:1.75rem;">
            <h2>Niveles por Facultad</h2>
            <canvas id="prof-bar"></canvas>
            <div class="chart-legend">
                <span class="legend-item"><span></span> Ansiedad</span>
                <span class="legend-item"><span></span> Estrés</span>
            </div>
        </section>

        <section class="card" style="margin-bottom:1.75rem;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h2>Historial de Sugerencias</h2>
                <button id="refreshHistorialBtn" class="btn btn-secondary" style="padding:0.6rem 1rem; font-size:0.9rem;">Refrescar</button>
            </div>
            <div class="table-wrapper">
                <table id="historialTable">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Test</th>
                            <th style="text-align:center;">Estudiantes Sugeridos</th>
                            <th style="text-align:center;">Completaron</th>
                            <th style="text-align:center;">Tasa Completitud</th>
                            <th style="text-align:center;">Estado</th>
                            <th>Última Sugerencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="7" style="padding:0.75rem; text-align:center; color:#888;">Cargando...</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>

<!-- Suggest Modal (opens when clicking .btn-suggest) -->
<div id="suggestModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <button class="close" id="closeSuggestModal" aria-label="Cerrar">×</button>
        <h3>Sugerir Test</h3>
        <form id="suggestForm">
            <div>
                <label for="selectCourse">Curso destino</label>
                <div class="select-wrapper">
                    <select id="selectCourse" name="curso_id" class="form-control select-filter">
                        <option value="">-- Selecciona un curso --</option>
                    </select>
                    <i class="fas fa-chevron-down select-icon"></i>
                </div>
            </div>
            <div>
                <label for="selectTest">Test</label>
                <div class="select-wrapper">
                    <select id="selectTest" name="id_test" class="form-control select-filter">
                        <option value="">-- Selecciona un test --</option>
                    </select>
                    <i class="fas fa-chevron-down select-icon"></i>
                </div>
            </div>
            <div id="suggestMsg" class="modal-msg" role="status" aria-live="polite" style="display:none;"></div>
            <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                <button type="button" id="cancelSuggest" class="btn btn-secondary">Cancelar</button>
                <button type="submit" class="btn btn-primary">Enviar sugerencia</button>
            </div>
        </form>
    </div>
</div>

<!-- High Levels Modal -->
<div id="highLevelsModal" class="modal" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true">
        <button class="close" id="closeHighLevelsModal" aria-label="Cerrar">×</button>
        <h3>Cursos con Promedios Más Altos</h3>
        <div id="highLevelsMsg" style="display:none;"></div>
        <div class="table-wrapper">
            <table id="highLevelsTable">
                <thead>
                    <tr>
                        <th>Curso</th>
                        <th style="text-align:center;">Estudiantes</th>
                        <th style="text-align:center;">En Riesgo</th>
                        <th style="text-align:center;">Promedio Estrés</th>
                        <th style="text-align:center;">Promedio Ansiedad</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="5" style="padding:0.75rem; text-align:center; color:#888;">Cargando...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="public/js/prof-dashboard.js?v=<?php echo asset_version('public/js/prof-dashboard.js'); ?>"></script>
<script src="public/js/prof-charts.js?v=<?php echo asset_version('public/js/prof-charts.js'); ?>"></script>

<script>
// Load history and setup additional functionality
document.addEventListener('DOMContentLoaded', function() {
    // Load history on page load
    loadHistorial();

    // Refresh dashboard button
    document.getElementById('refreshDashboardBtn')?.addEventListener('click', () => {
        window.location.reload();
    });

    // Refresh history button
    document.getElementById('refreshHistorialBtn')?.addEventListener('click', loadHistorial);

    // Setup high levels modal
    setupHighLevelsModal();

    // Sugerir Test modal logic
    setupSuggestTestModal();
});
// Sugerir Test Modal: permite elegir test y curso
function setupSuggestTestModal() {
    const openBtn = document.querySelector('.btn-suggest-modal');
    const modal = document.getElementById('suggestModal');
    const closeBtn = document.getElementById('closeSuggestModal');
    const cancelBtn = document.getElementById('cancelSuggest');
    const selectCourse = document.getElementById('selectCourse');
    const selectTest = document.getElementById('selectTest');
    const form = document.getElementById('suggestForm');
    const msg = document.getElementById('suggestMsg');

    if (!openBtn || !modal || !closeBtn || !form || !selectCourse || !selectTest) return;

    openBtn.addEventListener('click', () => {
        // Limpiar selects
        selectCourse.value = '';
        selectTest.value = '';
        msg.style.display = 'none';
        // Cargar cursos (ya están en window.UnimindData.courses)
        selectCourse.innerHTML = '<option value="">-- Selecciona un curso --</option>';
        (window.UnimindData.courses || []).forEach(c => {
            selectCourse.innerHTML += `<option value="${c.id_curso}">${c.nombre_curso}</option>`;
        });
        // Cargar tests disponibles (AJAX)
        selectTest.innerHTML = '<option value="">Cargando tests...</option>';
        fetch('api/cursos.php?action=tests_disponibles')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !Array.isArray(data.tests)) {
                    selectTest.innerHTML = '<option value="">No hay tests disponibles</option>';
                    return;
                }
                selectTest.innerHTML = '<option value="">-- Selecciona un test --</option>';
                data.tests.forEach(t => {
                    selectTest.innerHTML += `<option value="${t.id_test}">${t.nombre}</option>`;
                });
            })
            .catch(() => {
                selectTest.innerHTML = '<option value="">Error al cargar tests</option>';
            });
        // Mostrar modal
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    closeBtn.addEventListener('click', closeModal);
    cancelBtn.addEventListener('click', closeModal);
    window.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.style.display === 'flex') closeModal();
    });
    modal.addEventListener('click', e => {
        if (e.target === modal) closeModal();
    });

    // Enviar sugerencia
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        msg.style.display = 'none';
        const curso_id = selectCourse.value;
        const id_test = selectTest.value;
        if (!curso_id || !id_test) {
            msg.textContent = 'Selecciona un curso y un test.';
            msg.style.display = 'block';
            return;
        }
        // Enviar sugerencia (AJAX)
        fetch('api/sugerencias.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ curso_id, id_test })
        })
        .then(r => r.json())
        .then(data => {
            msg.textContent = data.mensaje || (data.success ? 'Sugerencia enviada.' : 'No se pudo enviar la sugerencia.');
            msg.style.display = 'block';
            if (data.success) {
                setTimeout(() => { closeModal(); loadHistorial(); }, 1200);
            }
        })
        .catch(() => {
            msg.textContent = 'Error al enviar sugerencia.';
            msg.style.display = 'block';
        });
    });
}

// Load suggestion history
function loadHistorial() {
    const table = document.getElementById('historialTable');
    if (!table) return;

    fetch('api/prof_historial.php?limite=10')
        .then(r => r.json())
        .then(data => {
            if (!data.success || !data.data) {
                table.querySelector('tbody').innerHTML = '<tr><td colspan="7" style="padding:0.75rem; text-align:center; color:#888;">No hay datos disponibles</td></tr>';
                return;
            }

            const rows = data.data.map(item => {
                const tasa = parseFloat(item.tasa_completitud || 0);
                const estadoBadge = item.estado === 'pendiente'
                    ? '<span style="background:var(--bg-300); color:var(--var-700); padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem;">Pendiente</span>'
                    : '<span style="background:var(--acc-100); color:var(--acc-700); padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem;">Visto</span>';

                return `<tr>
                    <td>${item.nombre_curso || '-'}</td>
                    <td>${item.nombre_test || '-'}</td>
                    <td style="text-align:center;">${item.estudiantes_sugeridos || 0}</td>
                    <td style="text-align:center;">${item.estudiantes_completaron || 0}</td>
                    <td style="text-align:center; font-weight:600;">${tasa.toFixed(1)}%</td>
                    <td style="text-align:center;">${estadoBadge}</td>
                    <td>${item.ultima_sugerencia ? new Date(item.ultima_sugerencia).toLocaleDateString('es-ES', {year: 'numeric', month: 'short', day: 'numeric'}) : '-'}</td>
                </tr>`;
            }).join('');

            table.querySelector('tbody').innerHTML = rows || '<tr><td colspan="7" style="padding:0.75rem; text-align:center; color:#888;">No hay sugerencias registradas</td></tr>';
        })
        .catch(err => {
            console.error('Error cargando historial:', err);
            table.querySelector('tbody').innerHTML = '<tr><td colspan="7" style="padding:0.75rem; text-align:center; color:var(--pri-600);">Error al cargar datos</td></tr>';
        });
}

// Setup high levels modal
function setupHighLevelsModal() {
    const openBtn = document.getElementById('openHighLevelsModal');
    const modal = document.getElementById('highLevelsModal');
    const closeBtn = document.getElementById('closeHighLevelsModal');
    const table = document.getElementById('highLevelsTable');
    const msg = document.getElementById('highLevelsMsg');

    openBtn?.addEventListener('click', () => {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        msg.style.display = 'none';

        fetch('api/prof_metrics.php?top_courses=1')
            .then(r => r.json())
            .then(data => {
                if (!data.success || !data.top_courses) {
                    msg.textContent = 'No se pudo obtener los datos.';
                    msg.style.display = 'block';
                    table.querySelector('tbody').innerHTML = '<tr><td colspan="5" style="padding:0.75rem; text-align:center; color:#888;">Sin datos</td></tr>';
                    return;
                }

                let totalRiesgo = 0;
                data.top_courses.forEach(row => {
                    totalRiesgo += parseInt(row.estudiantes_riesgo || 0);
                });

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

                    return `<tr>
                        <td>${row.nombre_curso}</td>
                        <td style="text-align:center;">${total}</td>
                        <td style="text-align:center; font-weight:600;">${riesgo} (${pctRiesgo}%)</td>
                        <td style="text-align:center; font-weight:600;">${row.promedio_estres ?? '-'}</td>
                        <td style="text-align:center; font-weight:600;">${row.promedio_ansiedad ?? '-'}</td>
                    </tr>`;
                }).join('');
                table.querySelector('tbody').innerHTML = rows || '<tr><td colspan="5" style="padding:0.75rem; text-align:center; color:#888;">Sin datos</td></tr>';
            })
            .catch(() => {
                msg.textContent = 'Error al consultar los datos.';
                msg.style.display = 'block';
                table.querySelector('tbody').innerHTML = '<tr><td colspan="5" style="padding:0.75rem; text-align:center; color:#888;">Sin datos</td></tr>';
            });
    });

    function closeModal() {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }

    closeBtn?.addEventListener('click', closeModal);

    window.addEventListener('keydown', e => {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });

    modal.addEventListener('click', e => {
        if (e.target === modal) {
            closeModal();
        }
    });
}
</script>