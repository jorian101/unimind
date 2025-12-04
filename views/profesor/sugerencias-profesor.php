<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/sugerencias-profesor.css?v=<?php echo time(); ?>">

<div class="sugerencias">
    <section class="sugerencias__card">
        <div class="sugerencias__header">
            <div>
                <h2 class="sugerencias__title">Mis Sugerencias de Tests</h2>
                <p class="sugerencias__subtitle">Gestiona los tests que has sugerido a tus estudiantes</p>
            </div>
            <div class="sugerencias__stats">
                <div class="sugerencias__stat">
                    <i class="fas fa-paper-plane"></i>
                    <div>
                        <span class="sugerencias__stat-value" id="totalSugerencias">0</span>
                        <span class="sugerencias__stat-label">Total sugerencias</span>
                    </div>
                </div>
                <div class="sugerencias__stat">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <span class="sugerencias__stat-value" id="completadas">0</span>
                        <span class="sugerencias__stat-label">Completadas</span>
                    </div>
                </div>
                <div class="sugerencias__stat">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="sugerencias__stat-value" id="pendientes">0</span>
                        <span class="sugerencias__stat-label">Pendientes</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="sugerencias__filters">
            <div class="sugerencias__search">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por estudiante, test o curso...">
            </div>
            <div class="sugerencias__filter-buttons">
                <button class="sugerencias__filter-btn sugerencias__filter-btn--active" data-filter="all">
                    <i class="fas fa-list"></i> Todas
                </button>
                <button class="sugerencias__filter-btn" data-filter="pendiente">
                    <i class="fas fa-clock"></i> Pendientes
                </button>
                <button class="sugerencias__filter-btn" data-filter="completado">
                    <i class="fas fa-check-circle"></i> Completadas
                </button>
            </div>
        </div>

        <div class="sugerencias__table-container" id="sugerenciasGrid">
            <div class="sugerencias__loading">
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
    const completadas = sugerencias.filter(s => s.completado).length;
    const pendientes = total - completadas;

    document.getElementById('totalSugerencias').textContent = total;
    document.getElementById('completadas').textContent = completadas;
    document.getElementById('pendientes').textContent = pendientes;
}

function renderSugerencias() {
    const container = document.getElementById('sugerenciasGrid');
    
    let sugerenciasFiltradas = sugerencias.filter(sug => {
        if (filtroActual === 'completado' && !sug.completado) return false;
        if (filtroActual === 'pendiente' && sug.completado) return false;
        if (busqueda) {
            const searchLower = busqueda.toLowerCase();
            const matchTest = sug.nombre_test.toLowerCase().includes(searchLower);
            const matchEstudiante = sug.nombre_estudiante.toLowerCase().includes(searchLower);
            const matchCurso = (sug.nombre_curso || '').toLowerCase().includes(searchLower);
            const matchCodigo = (sug.codigo_usuario || '').toLowerCase().includes(searchLower);
            if (!matchTest && !matchEstudiante && !matchCurso && !matchCodigo) return false;
        }
        return true;
    });

    if (sugerenciasFiltradas.length === 0) {
        container.innerHTML = `
            <div class="sugerencias__empty">
                <i class="fas fa-inbox"></i>
                <p>No se encontraron sugerencias</p>
            </div>
        `;
        return;
    }

    const rowsHTML = sugerenciasFiltradas.map(sug => {
        const fechaSugerencia = formatearFecha(sug.fecha_sugerencia);
        const fechaUltima = formatearFecha(sug.fecha_ultima_sugerencia);
        const esMultiple = sug.profesores_ids.length > 1;
        const estadoClass = sug.completado ? 'sugerencias__status--completado' : 'sugerencias__status--pendiente';
        const estadoText = sug.completado ? 'Completado' : 'Pendiente';
        const estadoIcon = sug.completado ? 'fa-check-circle' : 'fa-clock';

        return `
            <tr class="sugerencias__row">
                <td class="sugerencias__cell">
                    <div class="sugerencias__test">
                        <div class="sugerencias__test-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="sugerencias__test-name">${escapeHtml(sug.nombre_test)}</div>
                            <div class="sugerencias__test-meta">${sug.num_items} ítems</div>
                        </div>
                    </div>
                </td>
                <td class="sugerencias__cell">
                    <div class="sugerencias__student">
                        <div class="sugerencias__student-name">${escapeHtml(sug.nombre_estudiante)}</div>
                        <div class="sugerencias__student-code">${escapeHtml(sug.codigo_usuario)}</div>
                    </div>
                </td>
                <td class="sugerencias__cell">
                    <div class="sugerencias__course-badge">
                        <i class="fas fa-book"></i>
                        ${escapeHtml(sug.nombre_curso || 'N/A')}
                    </div>
                    ${esMultiple ? '<span class="sugerencias__multiple-badge" title="Sugerido por múltiples profesores"><i class="fas fa-users"></i></span>' : ''}
                </td>
                <td class="sugerencias__cell">
                    <div class="sugerencias__date">
                        <div class="sugerencias__date-main">${fechaSugerencia}</div>
                        ${fechaSugerencia !== fechaUltima ? `<div class="sugerencias__date-secondary">Últ: ${fechaUltima}</div>` : ''}
                    </div>
                </td>
                <td class="sugerencias__cell">
                    <span class="sugerencias__status ${estadoClass}">
                        <i class="fas ${estadoIcon}"></i>
                        ${estadoText}
                    </span>
                </td>
                <td class="sugerencias__cell">
                    <button class="sugerencias__btn-eliminar" 
                            aria-label="Eliminar sugerencia"
                            onclick="abrirModalEliminar(${sug.id_sugerencia}, '${escapeHtml(sug.nombre_test).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_estudiante).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_curso || 'N/A').replace(/'/g, "\\'")}', ${esMultiple})"
                            title="Eliminar sugerencia">
                        <i class="fas fa-trash-alt"></i>
                        <span style="margin-left:0.5rem;">Eliminar</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');

    container.innerHTML = `
        <table class="sugerencias__table">
            <thead class="sugerencias__table-head">
                <tr class="sugerencias__row">
                    <th class="sugerencias__table-header">Test</th>
                    <th class="sugerencias__table-header">Estudiante</th>
                    <th class="sugerencias__table-header">Curso</th>
                    <th class="sugerencias__table-header">Fecha Sugerencia</th>
                    <th class="sugerencias__table-header">Estado</th>
                    <th class="sugerencias__table-header">Acción</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHTML}
            </tbody>
        </table>
    `;
}

function configurarFiltros() {
    document.querySelectorAll('.sugerencias__filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.sugerencias__filter-btn').forEach(b => b.classList.remove('sugerencias__filter-btn--active'));
            btn.classList.add('sugerencias__filter-btn--active');
            filtroActual = btn.dataset.filter;
            renderSugerencias();
        });
    });
    document.getElementById('searchInput').addEventListener('input', (e) => {
        busqueda = e.target.value;
        renderSugerencias();
    });
}

function abrirModalEliminar(idSugerencia, nombreTest, nombreEstudiante, nombreCurso, esMultiple) {
    sugerenciaAEliminar = idSugerencia;

    const mensaje = esMultiple
        ? `Se eliminará tu sugerencia del test <strong>${escapeHtml(nombreTest)}</strong> para <strong>${escapeHtml(nombreEstudiante)}</strong>. Otros profesores también sugirieron este test.`
        : `Se eliminará la sugerencia del test <strong>${escapeHtml(nombreTest)}</strong> para <strong>${escapeHtml(nombreEstudiante)}</strong>.`;

    const htmlContent = `
        <p style="font-size: 1rem; line-height: 1.6;">${mensaje}</p>
    `;

    // Usar el modal reutilizable
    if (window.Modal && typeof window.Modal.show === 'function') {
        window.Modal.show({
            type: 'delete',
            title: 'Confirmar Eliminación',
            html: htmlContent,
            confirmText: 'Eliminar',
            cancelText: 'Cancelar',
            onConfirm: async () => {
                // onConfirm ejecuta antes de cerrar el modal; delegamos la eliminación
                await confirmarEliminacion(idSugerencia);
            }
        });
    } else {
        // Fallback: si no hay modal, abrir el antiguo (no existen elementos ahora)
        if (confirm(`¿Eliminar la sugerencia de ${nombreEstudiante} para el test ${nombreTest}?`)) {
            confirmarEliminacion(idSugerencia);
        } else {
            sugerenciaAEliminar = null;
        }
    }
}

// confirmarEliminacion acepta un id (soporta llamadas directas y desde el modal)
async function confirmarEliminacion(id) {
    const idToDelete = id || sugerenciaAEliminar;
    if (!idToDelete) return;
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;
        const response = await fetch(`${baseUrl}/api/sugerencias.php?action=eliminar`, {
            method: 'POST', headers: {'Content-Type':'application/json'}, credentials:'include', body: JSON.stringify({ id_sugerencia: idToDelete })
        });
        const result = await response.json();
        if (result.success) {
            mostrarNotificacion(result.message, 'success');
            sugerenciaAEliminar = null;
            await cargarSugerencias();
        } else {
            mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar la sugerencia'), 'error');
        }
    } catch (error) {
        console.error('Error al eliminar sugerencia:', error);
        mostrarNotificacion('Error de conexión al eliminar la sugerencia', 'error');
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
