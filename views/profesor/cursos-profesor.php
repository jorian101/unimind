<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/cursos-profesor.css?v=<?php echo time(); ?>">

<div class="prof-cursos">
    <section class="prof-cursos__card">
        <div class="prof-cursos__header">
            <div>
                <h2 class="prof-cursos__title">Mis Cursos</h2>
                <p class="prof-cursos__subtitle">Monitorea las estadísticas y métricas de tus cursos</p>
            </div>
            <div class="prof-cursos__stats">
                <div class="prof-cursos__stat">
                    <i class="fas fa-book"></i>
                    <div>
                        <span class="prof-cursos__stat-value" id="totalCursos">0</span>
                        <span class="prof-cursos__stat-label">Total cursos</span>
                    </div>
                </div>
                <div class="prof-cursos__stat">
                    <i class="fas fa-users"></i>
                    <div>
                        <span class="prof-cursos__stat-value" id="totalEstudiantes">0</span>
                        <span class="prof-cursos__stat-label">Estudiantes activos</span>
                    </div>
                </div>
                <div class="prof-cursos__stat">
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <span class="prof-cursos__stat-value" id="promedioTests">0</span>
                        <span class="prof-cursos__stat-label">Promedio tests</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="prof-cursos__filters">
            <div class="prof-cursos__search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por curso o escuela...">
            </div>
            <div class="prof-cursos__filter-buttons">
                <button class="prof-cursos__filter-btn prof-cursos__filter-btn--active" data-filter="all">
                    <i class="fas fa-list"></i> Todos
                </button>
                <button class="prof-cursos__filter-btn" data-filter="active">
                    <i class="fas fa-clipboard-check"></i> Con tests activos
                </button>
                <button class="prof-cursos__filter-btn" data-filter="high-stress">
                    <i class="fas fa-exclamation-triangle"></i> Alto estrés
                </button>
            </div>
        </div>

        <div class="prof-cursos__table-container" id="cursosGrid">
            <div class="prof-cursos__loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando cursos...
            </div>
        </div>
    </section>
</div>

<!-- Modal de Detalles del Curso -->
<div id="detallesModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalCursoNombre">
                <i class="fas fa-graduation-cap"></i> Curso
            </h2>
            <button class="modal-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="modal-body">
            <!-- Información del Curso -->
            <div class="info-box" style="margin-bottom: 1.5rem;">
                <div class="info-box-row">
                    <div class="info-box-item">
                        <i class="fas fa-graduation-cap"></i>
                        <div>
                            <span class="info-box-label">Curso</span>
                            <span class="info-box-value" id="modalCursoNombreInfo">Curso</span>
                        </div>
                    </div>
                    <div class="info-box-item">
                        <i class="fas fa-school"></i>
                        <div>
                            <span class="info-box-label">Escuela</span>
                            <span class="info-box-value" id="modalEscuelaNombre">Escuela</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-chart-pie"></i>
                    <h4>Métricas Generales</h4>
                </div>
                <div class="prof-cursos-modal__metrics">
                    <div class="prof-cursos-modal__metric">
                        <i class="fas fa-users"></i>
                        <div>
                            <span class="prof-cursos-modal__metric-value" id="modalTotalEstudiantes">0</span>
                            <span class="prof-cursos-modal__metric-label">Estudiantes</span>
                        </div>
                    </div>
                    <div class="prof-cursos-modal__metric">
                        <i class="fas fa-clipboard-list"></i>
                        <div>
                            <span class="prof-cursos-modal__metric-value" id="modalTestsRealizados">0</span>
                            <span class="prof-cursos-modal__metric-label">Tests realizados</span>
                        </div>
                    </div>
                    <div class="prof-cursos-modal__metric">
                        <i class="fas fa-clock"></i>
                        <div>
                            <span class="prof-cursos-modal__metric-value" id="modalTestsActivos">0</span>
                            <span class="prof-cursos-modal__metric-label">Tests activos</span>
                        </div>
                    </div>
                    <div class="prof-cursos-modal__metric">
                        <i class="fas fa-percentage"></i>
                        <div>
                            <span class="prof-cursos-modal__metric-value" id="modalTasaCompletitud">0%</span>
                            <span class="prof-cursos-modal__metric-label">Tasa completitud</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Nivel de Estrés y Ansiedad General -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-heartbeat"></i>
                    <h4>Niveles Promedio del Curso</h4>
                </div>
                <div class="prof-cursos-modal__charts-row">
                    <div class="prof-cursos-modal__chart-container">
                        <canvas id="estresGeneralChart"></canvas>
                    </div>
                    <div class="prof-cursos-modal__chart-container">
                        <canvas id="ansiedadGeneralChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Tendencia Mensual -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-chart-line"></i>
                    <h4>Tendencia Mensual</h4>
                </div>
                <div class="prof-cursos-modal__chart-full">
                    <canvas id="tendenciaMensualChart"></canvas>
                </div>
            </div>

            <!-- Distribución de Niveles -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    <h4>Distribución de Niveles</h4>
                </div>
                <div class="prof-cursos-modal__charts-row">
                    <div class="prof-cursos-modal__chart-container">
                        <canvas id="distribucionEstresChart"></canvas>
                    </div>
                    <div class="prof-cursos-modal__chart-container">
                        <canvas id="distribucionAnsiedadChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Estado de Tests Activos -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-tasks"></i>
                    <h4>Tests Activos</h4>
                </div>
                <div id="testsActivosContainer" class="prof-cursos-modal__tests-list">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let cursos = [];
let filtroActual = 'all';
let busqueda = '';
let charts = {}; // Almacena las instancias de los gráficos

document.addEventListener('DOMContentLoaded', function() {
    cargarCursos();
    configurarFiltros();
});

async function cargarCursos() {
    const container = document.getElementById('cursosGrid');

    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;

        console.log('Cargando cursos desde:', `${baseUrl}/api/prof_metrics.php?action=cursos`);

        const response = await fetch(`${baseUrl}/api/prof_metrics.php?action=cursos`, {
            credentials: 'include'
        });

        console.log('Response status:', response.status);
        const result = await response.json();
        console.log('Response data:', result);

        if (!response.ok) {
            throw new Error(result.message || 'Error al cargar cursos');
        }

        if (result.success && result.data) {
            cursos = result.data;
            console.log('Cursos cargados:', cursos.length);
            
            if (cursos.length === 0) {
                mostrarMensaje('No tienes cursos asignados. Contacta al administrador para que te asigne cursos.', 'info');
                return;
            }
            
            actualizarEstadisticas();
            renderCursos();
        } else {
            console.warn('No se encontraron cursos en la respuesta');
            mostrarMensaje('No hay cursos registrados', 'info');
        }
    } catch (error) {
        console.error('Error al cargar cursos:', error);
        mostrarMensaje('Error al cargar los cursos: ' + error.message, 'error');
    }
}

function actualizarEstadisticas() {
    const totalCursos = cursos.length;
    const totalEstudiantes = cursos.reduce((sum, curso) => sum + (parseInt(curso.total_estudiantes) || 0), 0);
    const totalTests = cursos.reduce((sum, curso) => sum + (parseInt(curso.tests_completados) || 0), 0);
    const promedioTests = totalCursos > 0 ? Math.round(totalTests / totalCursos) : 0;

    document.getElementById('totalCursos').textContent = totalCursos;
    document.getElementById('totalEstudiantes').textContent = totalEstudiantes;
    document.getElementById('promedioTests').textContent = promedioTests;
}

function renderCursos() {
    const container = document.getElementById('cursosGrid');
    
    let cursosFiltrados = cursos.filter(curso => {
        if (filtroActual === 'active' && (!curso.tests_activos || parseInt(curso.tests_activos) === 0)) {
            return false;
        }
        if (filtroActual === 'high-stress') {
            const estres = parseFloat(curso.nivel_estres_promedio || 0);
            if (estres < 3) return false;
        }
        if (busqueda) {
            const searchLower = busqueda.toLowerCase();
            const matchCurso = curso.nombre_curso.toLowerCase().includes(searchLower);
            const matchEscuela = (curso.nombre_escuela || '').toLowerCase().includes(searchLower);
            if (!matchCurso && !matchEscuela) return false;
        }
        return true;
    });

    if (cursosFiltrados.length === 0) {
        container.innerHTML = `
            <div class="prof-cursos__empty">
                <i class="fas fa-inbox"></i>
                <p>No se encontraron cursos</p>
            </div>
        `;
        return;
    }

    const rowsHTML = cursosFiltrados.map(curso => {
        const totalEstudiantes = parseInt(curso.total_estudiantes) || 0;
        const testsCompletados = parseInt(curso.tests_completados) || 0;
        const testsActivos = parseInt(curso.tests_activos) || 0;
        const nivelEstres = parseFloat(curso.nivel_estres_promedio) || 0;
        const nivelAnsiedad = parseFloat(curso.nivel_ansiedad_promedio) || 0;
        
        const estresClass = nivelEstres >= 4 ? 'prof-cursos__level--critical' : 
                           nivelEstres >= 3 ? 'prof-cursos__level--high' : 
                           nivelEstres >= 2 ? 'prof-cursos__level--medium' : 'prof-cursos__level--low';
        const ansiedadClass = nivelAnsiedad >= 4 ? 'prof-cursos__level--critical' : 
                             nivelAnsiedad >= 3 ? 'prof-cursos__level--high' : 
                             nivelAnsiedad >= 2 ? 'prof-cursos__level--medium' : 'prof-cursos__level--low';

        return `
            <tr class="prof-cursos__row">
                <td class="prof-cursos__cell">
                    <div class="prof-cursos__curso">
                        <div class="prof-cursos__curso-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <div class="prof-cursos__curso-name">${escapeHtml(curso.nombre_curso)}</div>
                            <div class="prof-cursos__curso-escuela">${escapeHtml(curso.nombre_escuela || 'N/A')}</div>
                        </div>
                    </div>
                </td>
                <td class="prof-cursos__cell prof-cursos__cell--center prof-cursos__students-value">
                    ${totalEstudiantes}
                </td>
                <td class="prof-cursos__cell prof-cursos__cell--center">
                    <div class="prof-cursos__tests-info">
                        <span class="prof-cursos__tests-completed">${testsCompletados} completados</span>
                        ${testsActivos > 0 ? `<span class="prof-cursos__tests-active">${testsActivos} activos</span>` : ''}
                    </div>
                </td>
                <td class="prof-cursos__cell prof-cursos__cell--center prof-cursos__nivel-estres-value">
                    ${nivelEstres.toFixed(1)}
                </td>
                <td class="prof-cursos__cell prof-cursos__cell--center prof-cursos__nivel-ansiedad-value">
                    ${nivelAnsiedad.toFixed(1)}
                </td>
                <td class="prof-cursos__cell">
                    <button class="prof-cursos__btn-detalles" 
                            aria-label="Ver detalles"
                            onclick="verDetalles(${curso.id_curso})"
                            title="Ver estadísticas detalladas">
                        <i class="fas fa-chart-bar"></i>
                        Ver Detalles
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    container.innerHTML = `
        <table class="prof-cursos__table">
            <thead class="prof-cursos__table-head">
                <tr class="prof-cursos__row">
                    <th class="prof-cursos__table-header">Curso</th>
                    <th class="prof-cursos__table-header prof-cursos__table-header--center">Estudiantes</th>
                    <th class="prof-cursos__table-header prof-cursos__table-header--center">Tests</th>
                    <th class="prof-cursos__table-header prof-cursos__table-header--center">Nivel Estrés</th>
                    <th class="prof-cursos__table-header prof-cursos__table-header--center">Nivel Ansiedad</th>
                    <th class="prof-cursos__table-header">Acciones</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHTML}
            </tbody>
        </table>
    `;
}

function configurarFiltros() {
    document.querySelectorAll('.prof-cursos__filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.prof-cursos__filter-btn').forEach(b => 
                b.classList.remove('prof-cursos__filter-btn--active'));
            this.classList.add('prof-cursos__filter-btn--active');
            filtroActual = this.dataset.filter;
            renderCursos();
        });
    });

    document.getElementById('searchInput').addEventListener('input', function(e) {
        busqueda = e.target.value;
        renderCursos();
    });
}

async function verDetalles(idCurso) {
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;

        const response = await fetch(`${baseUrl}/api/prof_metrics.php?action=detalle_curso&id_curso=${idCurso}`, {
            credentials: 'include'
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
            throw new Error(result.message || 'Error al cargar detalles');
        }

        const data = result.data;
        
        // Actualizar información general del modal
        document.getElementById('modalCursoNombre').innerHTML = `<i class="fas fa-graduation-cap"></i> ${data.nombre_curso}`;
        document.getElementById('modalCursoNombreInfo').textContent = data.nombre_curso;
        document.getElementById('modalEscuelaNombre').textContent = data.nombre_escuela;
        document.getElementById('modalTotalEstudiantes').textContent = data.total_estudiantes;
        document.getElementById('modalTestsRealizados').textContent = data.tests_completados;
        document.getElementById('modalTestsActivos').textContent = data.tests_activos;
        
        const tasaCompletitud = data.total_estudiantes > 0 && data.tests_sugeridos > 0
            ? Math.round((data.tests_completados / (data.tests_sugeridos * data.total_estudiantes)) * 100)
            : 0;
        document.getElementById('modalTasaCompletitud').textContent = tasaCompletitud + '%';

        // Destruir gráficos anteriores si existen
        Object.values(charts).forEach(chart => chart.destroy());
        charts = {};

        // Crear gráficos
        crearGraficoEstresGeneral(data.nivel_estres_promedio);
        crearGraficoAnsiedadGeneral(data.nivel_ansiedad_promedio);
        crearGraficoTendenciaMensual(data.tendencia_mensual);
        crearGraficoDistribucionEstres(data.distribucion_estres);
        crearGraficoDistribucionAnsiedad(data.distribucion_ansiedad);
        
        // Renderizar tests activos
        renderTestsActivos(data.tests_activos_detalle);

        // Mostrar modal
        const modal = document.getElementById('detallesModal');
        modal.classList.add('prof-cursos-modal--show');
        modal.classList.add('active');

    } catch (error) {
        console.error('Error al cargar detalles:', error);
        mostrarMensaje('Error al cargar los detalles del curso.', 'error');
    }
}

function crearGraficoEstresGeneral(nivel) {
    const ctx = document.getElementById('estresGeneralChart').getContext('2d');
    const nivelNum = parseFloat(nivel) || 0;
    
    charts.estresGeneral = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Nivel Actual', 'Restante'],
            datasets: [{
                data: [nivelNum, 5 - nivelNum],
                backgroundColor: [
                    nivelNum >= 4 ? '#dc3545' : nivelNum >= 3 ? '#fd7e14' : nivelNum >= 2 ? '#ffc107' : '#28a745',
                    '#e9ecef'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: `Nivel de Estrés: ${nivelNum.toFixed(1)}/5`,
                    font: { size: 14, weight: 'bold' }
                }
            },
            cutout: '70%'
        }
    });
}

function crearGraficoAnsiedadGeneral(nivel) {
    const ctx = document.getElementById('ansiedadGeneralChart').getContext('2d');
    const nivelNum = parseFloat(nivel) || 0;
    
    charts.ansiedadGeneral = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Nivel Actual', 'Restante'],
            datasets: [{
                data: [nivelNum, 5 - nivelNum],
                backgroundColor: [
                    nivelNum >= 4 ? '#dc3545' : nivelNum >= 3 ? '#fd7e14' : nivelNum >= 2 ? '#ffc107' : '#28a745',
                    '#e9ecef'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: `Nivel de Ansiedad: ${nivelNum.toFixed(1)}/5`,
                    font: { size: 14, weight: 'bold' }
                }
            },
            cutout: '70%'
        }
    });
}

function crearGraficoTendenciaMensual(tendencia) {
    const ctx = document.getElementById('tendenciaMensualChart').getContext('2d');
    
    if (!tendencia || tendencia.length === 0) {
        ctx.font = '14px Arial';
        ctx.fillStyle = '#6c757d';
        ctx.textAlign = 'center';
        ctx.fillText('No hay datos suficientes', ctx.canvas.width / 2, ctx.canvas.height / 2);
        return;
    }

    const meses = tendencia.map(t => t.mes);
    const estres = tendencia.map(t => parseFloat(t.nivel_estres) || 0);
    const ansiedad = tendencia.map(t => parseFloat(t.nivel_ansiedad) || 0);

    charts.tendenciaMensual = new Chart(ctx, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [
                {
                    label: 'Nivel de Estrés',
                    data: estres,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Nivel de Ansiedad',
                    data: ansiedad,
                    borderColor: '#6f42c1',
                    backgroundColor: 'rgba(111, 66, 193, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        font: { size: 12 }
                    }
                },
                title: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 5,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
}

function crearGraficoDistribucionEstres(distribucion) {
    const ctx = document.getElementById('distribucionEstresChart').getContext('2d');
    
    if (!distribucion) {
        distribucion = { bajo: 0, moderado: 0, alto: 0, severo: 0 };
    }

    charts.distribucionEstres = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Bajo', 'Moderado', 'Alto', 'Severo'],
            datasets: [{
                label: 'Cantidad de estudiantes',
                data: [
                    parseInt(distribucion.bajo) || 0,
                    parseInt(distribucion.moderado) || 0,
                    parseInt(distribucion.alto) || 0,
                    parseInt(distribucion.severo) || 0
                ],
                backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Distribución de Niveles de Estrés',
                    font: { size: 12, weight: 'bold' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
}

function crearGraficoDistribucionAnsiedad(distribucion) {
    const ctx = document.getElementById('distribucionAnsiedadChart').getContext('2d');
    
    if (!distribucion) {
        distribucion = { bajo: 0, moderado: 0, alto: 0, severo: 0 };
    }

    charts.distribucionAnsiedad = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Bajo', 'Moderado', 'Alto', 'Severo'],
            datasets: [{
                label: 'Cantidad de estudiantes',
                data: [
                    parseInt(distribucion.bajo) || 0,
                    parseInt(distribucion.moderado) || 0,
                    parseInt(distribucion.alto) || 0,
                    parseInt(distribucion.severo) || 0
                ],
                backgroundColor: ['#28a745', '#ffc107', '#fd7e14', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Distribución de Niveles de Ansiedad',
                    font: { size: 12, weight: 'bold' }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
}

function renderTestsActivos(tests) {
    const container = document.getElementById('testsActivosContainer');
    
    if (!tests || tests.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>No hay tests activos</h3>
                <p>Todos los tests han sido completados o no hay tests sugeridos para este curso</p>
            </div>
        `;
        return;
    }

    const testsHTML = tests.map(test => {
        const completados = parseInt(test.estudiantes_completados) || 0;
        const total = parseInt(test.total_estudiantes) || 0;
        const porcentaje = total > 0 ? Math.round((completados / total) * 100) : 0;

        return `
            <div class="prof-cursos-modal__test-item">
                <div class="prof-cursos-modal__test-info">
                    <i class="fas fa-clipboard-list"></i>
                    <div>
                        <div class="prof-cursos-modal__test-name">${escapeHtml(test.nombre_test)}</div>
                        <div class="prof-cursos-modal__test-date">Sugerido: ${formatearFecha(test.fecha_sugerencia)}</div>
                    </div>
                </div>
                <div class="prof-cursos-modal__test-progress">
                    <div class="prof-cursos-modal__test-progress-text">${completados} de ${total} completados</div>
                    <div class="prof-cursos-modal__test-progress-bar">
                        <div class="prof-cursos-modal__test-progress-fill" style="width: ${porcentaje}%"></div>
                    </div>
                    <div class="prof-cursos-modal__test-progress-percent">${porcentaje}%</div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = testsHTML;
}

function cerrarModal() {
    const modal = document.getElementById('detallesModal');
    modal.classList.remove('prof-cursos-modal--show');
    modal.classList.remove('active');
    
    // Destruir gráficos al cerrar el modal
    Object.values(charts).forEach(chart => chart.destroy());
    charts = {};
}

// Cerrar modal al hacer clic fuera de él
document.getElementById('detallesModal').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});

function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.getElementById('cursosGrid');
    const iconos = {
        info: 'fa-info-circle',
        error: 'fa-exclamation-circle',
        success: 'fa-check-circle'
    };
    container.innerHTML = `
        <div class="prof-cursos__message prof-cursos__message--${tipo}">
            <i class="fas ${iconos[tipo]}"></i>
            <p>${mensaje}</p>
        </div>
    `;
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const opciones = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('es-ES', opciones);
}
</script>
