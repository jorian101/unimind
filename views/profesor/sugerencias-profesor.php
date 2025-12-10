<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/sugerencias-profesor.css?v=<?php echo time(); ?>">

<div class="prof-sugerencias">
    <section class="prof-sugerencias__card">
        <div class="prof-sugerencias__header">
            <div>
                <h2 class="prof-sugerencias__title">Mis Sugerencias de Tests</h2>
                <p class="prof-sugerencias__subtitle">Gestiona los tests que has sugerido a tus estudiantes</p>
            </div>
            <div class="prof-sugerencias__stats">
                <div class="prof-sugerencias__stat">
                    <i class="fas fa-paper-plane"></i>
                    <div>
                        <span class="prof-sugerencias__stat-value" id="totalSugerencias">0</span>
                        <span class="prof-sugerencias__stat-label">Total sugerencias</span>
                    </div>
                </div>
                <div class="prof-sugerencias__stat">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <span class="prof-sugerencias__stat-value" id="completadas">0</span>
                        <span class="prof-sugerencias__stat-label">Completadas</span>
                    </div>
                </div>
                <div class="prof-sugerencias__stat">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="prof-sugerencias__stat-value" id="pendientes">0</span>
                        <span class="prof-sugerencias__stat-label">Pendientes</span>
                    </div>
                </div>
            </div>
        </div>

            <div class="prof-sugerencias__filters">
            <div class="prof-sugerencias__search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por test o curso...">
            </div>
            <div class="prof-sugerencias__filter-buttons">
                <button class="prof-sugerencias__filter-btn prof-sugerencias__filter-btn--active" data-filter="all">
                    <i class="fas fa-list"></i> Todas
                </button>
                <button class="prof-sugerencias__filter-btn" data-filter="pendiente">
                    <i class="fas fa-clock"></i> Pendientes
                </button>
                <button class="prof-sugerencias__filter-btn" data-filter="completado">
                    <i class="fas fa-check-circle"></i> Completadas
                </button>
            </div>
        </div>

        <div class="prof-sugerencias__table-container" id="sugerenciasGrid">
            <div class="prof-sugerencias__loading">
                <i class="fas fa-spinner fa-spin"></i> Cargando sugerencias...
            </div>
        </div>
    </section>
</div>



<script>
let sugerencias = [];
let filtroActual = 'all';
let busqueda = '';
let sugerenciaAEliminar = null;

document.addEventListener('DOMContentLoaded', function() {
    cargarSugerencias();
    configurarFiltros();
});

async function cargarSugerencias() {
    const container = document.getElementById('sugerenciasGrid');

    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;

        const response = await fetch(`${baseUrl}/api/sugerencias.php?action=listar`, {
            credentials: 'include'
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Error al cargar sugerencias');
        }

        if (result.success && result.data) {
            sugerencias = result.data;
            actualizarEstadisticas();
            renderSugerencias();
        } else {
            mostrarMensaje('No hay sugerencias registradas', 'info');
        }
    } catch (error) {
        console.error('Error al cargar sugerencias:', error);
        mostrarMensaje('Error al cargar las sugerencias. Por favor, intenta nuevamente.', 'error');
    }
}

function actualizarEstadisticas() {
    const total = sugerencias.length;
    // Contar basándose en el número de estudiantes completados vs total
    let completadas = 0;
    let pendientes = 0;
    
    sugerencias.forEach(sug => {
        if (sug.estudiantes_completados === sug.total_estudiantes && sug.total_estudiantes > 0) {
            completadas++;
        } else {
            pendientes++;
        }
    });

    document.getElementById('totalSugerencias').textContent = total;
    document.getElementById('completadas').textContent = completadas;
    document.getElementById('pendientes').textContent = pendientes;
}

function renderSugerencias() {
    const container = document.getElementById('sugerenciasGrid');
    
    let sugerenciasFiltradas = sugerencias.filter(sug => {
        const completada = sug.estudiantes_completados === sug.total_estudiantes && sug.total_estudiantes > 0;
        
        if (filtroActual === 'completado' && !completada) return false;
        if (filtroActual === 'pendiente' && completada) return false;
        if (busqueda) {
            const searchLower = busqueda.toLowerCase();
            const matchTest = sug.nombre_test.toLowerCase().includes(searchLower);
            const matchCurso = (sug.nombre_curso || '').toLowerCase().includes(searchLower);
            if (!matchTest && !matchCurso) return false;
        }
        return true;
    });

    if (sugerenciasFiltradas.length === 0) {
        container.innerHTML = `
            <div class="prof-sugerencias__empty">
                <i class="fas fa-inbox"></i>
                <p>No se encontraron sugerencias</p>
            </div>
        `;
        return;
    }

    const rowsHTML = sugerenciasFiltradas.map(sug => {
        const fechaSugerencia = formatearFecha(sug.fecha_sugerencia);
        const completados = sug.estudiantes_completados || 0;
        const total = sug.total_estudiantes || 0;
        const completada = completados === total && total > 0;
        const estadoClass = completada ? 'prof-sugerencias__status--completado' : 'prof-sugerencias__status--pendiente';
        const estadoText = completada ? 'Completado' : 'Pendiente';
        const estadoIcon = completada ? 'fa-check-circle' : 'fa-clock';

        return `
            <tr class="prof-sugerencias__row">
                <td class="prof-sugerencias__cell">
                    <div class="prof-sugerencias__test">
                        <div class="prof-sugerencias__test-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="prof-sugerencias__test-name">${escapeHtml(sug.nombre_test)}</div>
                        </div>
                    </div>
                </td>
                <td class="prof-sugerencias__cell">
                    <div class="prof-sugerencias__course-badge">
                        <i class="fas fa-book"></i>
                        ${escapeHtml(sug.nombre_curso || 'N/A')}
                    </div>
                </td>
                <td class="prof-sugerencias__cell">
                    <div class="prof-sugerencias__progress">
                        <div class="prof-sugerencias__progress-text">
                            <strong>${completados}</strong> de <strong>${total}</strong>
                        </div>
                        <div class="prof-sugerencias__progress-bar">
                            <div class="prof-sugerencias__progress-fill" style="width: ${total > 0 ? (completados/total*100) : 0}%"></div>
                        </div>
                    </div>
                </td>
                <td class="prof-sugerencias__cell">
                    <div class="prof-sugerencias__date">
                        <div class="prof-sugerencias__date-main">${fechaSugerencia}</div>
                    </div>
                </td>
                <td class="prof-sugerencias__cell">
                    <span class="prof-sugerencias__status ${estadoClass}">
                        <i class="fas ${estadoIcon}"></i>
                        ${estadoText}
                    </span>
                </td>
                <td class="prof-sugerencias__cell">
                    <div class="prof-sugerencias__actions">
                        <button class="prof-sugerencias__btn-detalles" 
                                aria-label="Ver detalles"
                                onclick="verDetalles(${sug.id_curso}, ${sug.id_test}, '${escapeHtml(sug.nombre_test).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_curso || 'N/A').replace(/'/g, "\\'")}')"
                                title="Ver progreso detallado">
                            <i class="fas fa-chart-bar"></i>
                            <span style="margin-left:0.5rem;">Ver Detalles</span>
                        </button>
                        <button class="prof-sugerencias__btn-cancelar" 
                                aria-label="Cancelar sugerencia"
                                onclick="abrirModalCancelar(${sug.id_curso}, ${sug.id_test}, '${escapeHtml(sug.nombre_test).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_curso || 'N/A').replace(/'/g, "\\'")}')"
                                title="Cancelar sugerencia">
                            <i class="fas fa-times-circle"></i>
                            <span style="margin-left:0.5rem;">Cancelar</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');

    container.innerHTML = `
        <table class="prof-sugerencias__table">
            <thead class="prof-sugerencias__table-head">
                <tr class="prof-sugerencias__row">
                    <th class="prof-sugerencias__table-header">Test</th>
                    <th class="prof-sugerencias__table-header">Curso</th>
                    <th class="prof-sugerencias__table-header">Estudiantes Completados</th>
                    <th class="prof-sugerencias__table-header">Fecha Sugerencia</th>
                    <th class="prof-sugerencias__table-header">Estado</th>
                    <th class="prof-sugerencias__table-header">Acción</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHTML}
            </tbody>
        </table>
    `;
}

function configurarFiltros() {
    document.querySelectorAll('.prof-sugerencias__filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.prof-sugerencias__filter-btn').forEach(b => b.classList.remove('prof-sugerencias__filter-btn--active'));
            btn.classList.add('prof-sugerencias__filter-btn--active');
            filtroActual = btn.dataset.filter;
            renderSugerencias();
        });
    });
    document.getElementById('searchInput').addEventListener('input', (e) => {
        busqueda = e.target.value;
        renderSugerencias();
    });
}

function abrirModalCancelar(idCurso, idTest, nombreTest, nombreCurso) {
    sugerenciaAEliminar = { id_curso: idCurso, id_test: idTest };

    const mensaje = `Se cancelará la sugerencia del test <strong>${escapeHtml(nombreTest)}</strong> para el curso <strong>${escapeHtml(nombreCurso)}</strong>. Todos los estudiantes del curso dejarán de ver esta sugerencia.`;

    const htmlContent = `
        <p style="font-size: 1rem; line-height: 1.6;">${mensaje}</p>
    `;

    // Usar el modal reutilizable
    if (window.Modal && typeof window.Modal.show === 'function') {
        window.Modal.show({
            type: 'delete',
            title: 'Confirmar Cancelación',
            html: htmlContent,
            confirmText: 'Cancelar Sugerencia',
            cancelText: 'Volver',
            onConfirm: async () => {
                // onConfirm ejecuta antes de cerrar el modal; delegamos la eliminación
                await confirmarCancelacion();
            }
        });
    } else {
        // Fallback: si no hay modal
        if (confirm(`¿Cancelar la sugerencia del test ${nombreTest} para el curso ${nombreCurso}?`)) {
            confirmarCancelacion();
        } else {
            sugerenciaAEliminar = null;
        }
    }
}

// confirmarCancelacion: cancela sugerencia por curso+test
async function confirmarCancelacion() {
    if (!sugerenciaAEliminar) return;
    
    const { id_curso, id_test } = sugerenciaAEliminar;
    
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;
        
        const response = await fetch(`${baseUrl}/api/sugerencias.php?action=cancelar`, {
            method: 'POST', 
            headers: {'Content-Type':'application/json'}, 
            credentials:'include', 
            body: JSON.stringify({ id_curso: id_curso, id_test: id_test })
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarNotificacion(result.message || 'Sugerencia cancelada correctamente', 'success');
            sugerenciaAEliminar = null;
            await cargarSugerencias();
        } else {
            mostrarNotificacion('Error: ' + (result.message || 'No se pudo cancelar la sugerencia'), 'error');
        }
    } catch (error) {
        console.error('Error al cancelar sugerencia:', error);
        mostrarNotificacion('Error de conexión al cancelar la sugerencia', 'error');
    }
}

function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.getElementById('sugerenciasGrid');
    const iconClass = tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    const colorClass = tipo === 'error' ? 'text-danger' : 'text-info';
    container.innerHTML = `
        <div class="sugerencias__message ${colorClass}">
            <i class="fas ${iconClass}"></i>
            <p>${mensaje}</p>
        </div>
    `;
}

function mostrarNotificacion(mensaje, tipo = 'info') {
    if (window.Toast) {
        window.Toast.show({
            message: mensaje,
            type: tipo,
            duration: 3000
        });
    } else {
        // Fallback mínimo
        const notification = document.createElement('div');
        notification.className = `notification notification-${tipo}`;
        notification.innerHTML = `
            <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${mensaje}</span>
        `;
        document.body.appendChild(notification);
        setTimeout(() => notification.classList.add('show'), 100);
        setTimeout(() => { notification.classList.remove('show'); setTimeout(() => notification.remove(), 300); }, 3000);
    }
}

async function verDetalles(idCurso, idTest, nombreTest, nombreCurso) {
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;
        
        const response = await fetch(`${baseUrl}/api/sugerencias.php?action=detalles&id_curso=${idCurso}&id_test=${idTest}`, {
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.error || 'Error al obtener detalles');
        }
        
        const data = result.data;
        const metricas = data.metricas;
        const info = data.info_general;
        const estudiantes = data.estudiantes;
        const niveles = data.distribucion_niveles;
        const generos = data.distribucion_genero;
        
        // Generar HTML del modal siguiendo estructura de tests.php (administrador)
        const htmlContent = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2>
                        <i class="fas fa-chart-line"></i> Progreso del Curso
                    </h2>
                    <button class="modal-close" onclick="cerrarModalDetalles()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="info-box" style="margin-bottom: 1.5rem;">
                        <div class="info-box-row">
                            <div class="info-box-item">
                                <i class="fas fa-clipboard-list"></i>
                                <div>
                                    <span class="info-box-label">Test</span>
                                    <span class="info-box-value">${escapeHtml(nombreTest)}</span>
                                </div>
                            </div>
                            <div class="info-box-item">
                                <i class="fas fa-book"></i>
                                <div>
                                    <span class="info-box-label">Curso</span>
                                    <span class="info-box-value">${escapeHtml(nombreCurso)}</span>
                                </div>
                            </div>
                        </div>
                        <div class="info-box-row">
                            <div class="info-box-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <span class="info-box-label">Fecha de sugerencia</span>
                                    <span class="info-box-value">${formatearFecha(info.fecha_sugerencia)}</span>
                                </div>
                            </div>
                            <div class="info-box-item">
                                <i class="fas fa-list-ol"></i>
                                <div>
                                    <span class="info-box-label">Número de ítems</span>
                                    <span class="info-box-value">${info.num_items}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                
                <div class="detalles-metricas-grid">
                <div class="metrica-card">
                    <div class="metrica-card-header">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="metrica-card-body">
                        <div class="metrica-value">${metricas.total_estudiantes}</div>
                        <div class="metrica-label">Total Estudiantes</div>
                    </div>
                </div>
                <div class="metrica-card metrica-card--success">
                    <div class="metrica-card-header">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="metrica-card-body">
                        <div class="metrica-value">${metricas.completados}</div>
                        <div class="metrica-label">Completados</div>
                    </div>
                </div>
                <div class="metrica-card metrica-card--warning">
                    <div class="metrica-card-header">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="metrica-card-body">
                        <div class="metrica-value">${metricas.pendientes}</div>
                        <div class="metrica-label">Pendientes</div>
                    </div>
                </div>
                <div class="metrica-card metrica-card--info">
                    <div class="metrica-card-header">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="metrica-card-body">
                        <div class="metrica-value">${metricas.porcentaje_completado}%</div>
                        <div class="metrica-label">Progreso</div>
                    </div>
                    </div>
                </div>
                
                ${metricas.completados > 0 ? `
                <div class="detalles-section">
                    <div class="section-title">
                        <i class="fas fa-chart-bar"></i>
                        <h4>Estadísticas de Puntuaciones</h4>
                    </div>
                    <div class="stats-cards">
                    <div class="stat-card">
                        <div class="stat-card-icon stat-card-icon--avg">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-value">${metricas.promedio_puntuacion}</div>
                            <div class="stat-card-label">Promedio</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon stat-card-icon--min">
                            <i class="fas fa-arrow-down"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-value">${metricas.puntuacion_minima}</div>
                            <div class="stat-card-label">Mínima</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-card-icon stat-card-icon--max">
                            <i class="fas fa-arrow-up"></i>
                        </div>
                        <div class="stat-card-content">
                            <div class="stat-card-value">${metricas.puntuacion_maxima}</div>
                            <div class="stat-card-label">Máxima</div>
                        </div>
                        </div>
                    </div>
                </div>
                
                <div class="detalles-section">
                    <div class="section-title">
                        <i class="fas fa-chart-pie"></i>
                        <h4>Distribución por Nivel de Riesgo</h4>
                    </div>
                    <div class="niveles-container">
                        ${Object.entries(niveles).map(([nivel, count]) => {
                            const pct = metricas.completados > 0 ? ((count / metricas.completados) * 100).toFixed(1) : 0;
                            return `
                                <div class="nivel-item">
                                    <div class="nivel-header">
                                        <span class="nivel-name">${nivel.charAt(0).toUpperCase() + nivel.slice(1)}</span>
                                        <span class="nivel-stats">${count} estudiantes (${pct}%)</span>
                                    </div>
                                    <div class="nivel-bar-wrapper">
                                        <div class="nivel-bar nivel-bar--${nivel}" style="width: ${pct}%"></div>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
                ` : `
                <div class="detalles-empty">
                    <i class="fas fa-inbox"></i>
                    <p>Aún no hay estudiantes que hayan completado este test.</p>
                </div>
                `}
                </div>
            </div>
        `;
        
        // Crear modal overlay siguiendo estructura de tests.php (administrador)
        const modalOverlay = document.createElement('div');
        modalOverlay.className = 'modal-overlay';
        modalOverlay.id = 'detallesModal';
        modalOverlay.innerHTML = htmlContent;
        
        // Cerrar al hacer clic en el overlay
        modalOverlay.addEventListener('click', (e) => {
            if (e.target === modalOverlay) {
                cerrarModalDetalles();
            }
        });
        
        document.body.appendChild(modalOverlay);
        
        // Pequeño delay para animación
        setTimeout(() => {
            modalOverlay.style.display = 'flex';
        }, 10);
        
    } catch (error) {
        console.error('Error al cargar detalles:', error);
        mostrarNotificacion('Error al cargar los detalles: ' + error.message, 'error');
    }
}

function cerrarModalDetalles() {
    const modal = document.getElementById('detallesModal');
    if (modal) {
        modal.style.display = 'none';
        setTimeout(() => modal.remove(), 300);
    }
}

function escapeHtml(text) { if (!text) return ''; const map = {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}; return String(text).replace(/[&<>"']/g, m => map[m]); }

function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2,'0');
    const mes = String(date.getMonth()+1).padStart(2,'0');
    const anio = date.getFullYear();
    const hora = String(date.getHours()).padStart(2,'0');
    const minutos = String(date.getMinutes()).padStart(2,'0');
    return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
}
</script>
