<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/sugerencias-profesor.css?v=<?php echo time(); ?>">

<div class="sugerencias-container">
    <section class="sugerencias-card">
        <div class="card-header-section">
            <div>
                <h2 class="sugerencias-title">Mis Sugerencias de Tests</h2>
                <p class="sugerencias-subtitle">Gestiona los tests que has sugerido a tus estudiantes</p>
            </div>
            <div class="header-stats">
                <div class="stat-item">
                    <i class="fas fa-paper-plane"></i>
                    <div>
                        <span class="stat-value" id="totalSugerencias">0</span>
                        <span class="stat-label">Total sugerencias</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <span class="stat-value" id="completadas">0</span>
                        <span class="stat-label">Completadas</span>
                    </div>
                </div>
                <div class="stat-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <span class="stat-value" id="pendientes">0</span>
                        <span class="stat-label">Pendientes</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="filters-section">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" placeholder="Buscar por estudiante, test o curso...">
            </div>
            <div class="filter-buttons">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-list"></i> Todas
                </button>
                <button class="filter-btn" data-filter="pendiente">
                    <i class="fas fa-clock"></i> Pendientes
                </button>
                <button class="filter-btn" data-filter="completado">
                    <i class="fas fa-check-circle"></i> Completadas
                </button>
            </div>
        </div>

        <div class="sugerencias-table-container" id="sugerenciasGrid">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Cargando sugerencias...
            </div>
        </div>
    </section>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal-overlay" id="eliminarModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Confirmar Eliminación</h3>
            <button class="modal-close" onclick="cerrarModalEliminar()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="warning-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <p><strong>¿Estás seguro de eliminar esta sugerencia?</strong></p>
            <div class="sugerencia-info-box">
                <p><strong>Test:</strong> <span id="modalTestNombre"></span></p>
                <p><strong>Estudiante:</strong> <span id="modalEstudiante"></span></p>
                <p><strong>Curso:</strong> <span id="modalCurso"></span></p>
            </div>
            <p class="warning-text">Esta acción no se puede deshacer.</p>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrarModalEliminar()">Cancelar</button>
            <button class="btn-danger" onclick="confirmarEliminacion()">Eliminar</button>
        </div>
    </div>
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

/**
 * Cargar sugerencias desde la API
 */
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

/**
 * Actualizar estadísticas en el header
 */
function actualizarEstadisticas() {
    const total = sugerencias.length;
    const completadas = sugerencias.filter(s => s.completado).length;
    const pendientes = total - completadas;

    document.getElementById('totalSugerencias').textContent = total;
    document.getElementById('completadas').textContent = completadas;
    document.getElementById('pendientes').textContent = pendientes;
}

/**
 * Renderizar tabla de sugerencias
 */
function renderSugerencias() {
    const container = document.getElementById('sugerenciasGrid');
    
    // Filtrar sugerencias
    let sugerenciasFiltradas = sugerencias.filter(sug => {
        // Filtro por estado
        if (filtroActual === 'completado' && !sug.completado) return false;
        if (filtroActual === 'pendiente' && sug.completado) return false;
        
        // Filtro por búsqueda
        if (busqueda) {
            const searchLower = busqueda.toLowerCase();
            const matchTest = sug.nombre_test.toLowerCase().includes(searchLower);
            const matchEstudiante = sug.nombre_estudiante.toLowerCase().includes(searchLower);
            const matchCurso = (sug.nombre_curso || '').toLowerCase().includes(searchLower);
            const matchCodigo = (sug.codigo_usuario || '').toLowerCase().includes(searchLower);
            
            if (!matchTest && !matchEstudiante && !matchCurso && !matchCodigo) {
                return false;
            }
        }
        
        return true;
    });
    
    if (sugerenciasFiltradas.length === 0) {
        container.innerHTML = `
            <div class="no-sugerencias">
                <i class="fas fa-inbox"></i>
                <p>No se encontraron sugerencias</p>
            </div>
        `;
        return;
    }
    
    // Construir filas de la tabla
    const rowsHTML = sugerenciasFiltradas.map(sug => {
        const fechaSugerencia = formatearFecha(sug.fecha_sugerencia);
        const fechaUltima = formatearFecha(sug.fecha_ultima_sugerencia);
        const esMultiple = sug.profesores_ids.length > 1;
        const estadoClass = sug.completado ? 'completado' : 'pendiente';
        const estadoText = sug.completado ? 'Completado' : 'Pendiente';
        const estadoIcon = sug.completado ? 'fa-check-circle' : 'fa-clock';
        
        return `
            <tr class="sugerencias-table-row">
                <td class="sugerencias-table-cell">
                    <div class="test-info">
                        <div class="test-icon">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <div>
                            <div class="test-name">${escapeHtml(sug.nombre_test)}</div>
                            <div class="test-meta">${sug.num_items} ítems</div>
                        </div>
                    </div>
                </td>
                <td class="sugerencias-table-cell">
                    <div class="estudiante-info">
                        <div class="estudiante-nombre">${escapeHtml(sug.nombre_estudiante)}</div>
                        <div class="estudiante-codigo">${escapeHtml(sug.codigo_usuario)}</div>
                    </div>
                </td>
                <td class="sugerencias-table-cell">
                    <div class="curso-badge">
                        <i class="fas fa-book"></i>
                        ${escapeHtml(sug.nombre_curso || 'N/A')}
                    </div>
                    ${esMultiple ? '<span class="multiple-badge" title="Sugerido por múltiples profesores"><i class="fas fa-users"></i></span>' : ''}
                </td>
                <td class="sugerencias-table-cell">
                    <div class="fecha-info">
                        <div class="fecha-principal">${fechaSugerencia}</div>
                        ${fechaSugerencia !== fechaUltima ? `<div class="fecha-secundaria">Últ: ${fechaUltima}</div>` : ''}
                    </div>
                </td>
                <td class="sugerencias-table-cell">
                    <span class="estado-badge ${estadoClass}">
                        <i class="fas ${estadoIcon}"></i>
                        ${estadoText}
                    </span>
                </td>
                <td class="sugerencias-table-cell">
                    <button class="btn-eliminar" 
                            onclick="abrirModalEliminar(${sug.id_sugerencia}, '${escapeHtml(sug.nombre_test).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_estudiante).replace(/'/g, "\\'")}', '${escapeHtml(sug.nombre_curso || 'N/A').replace(/'/g, "\\'")}', ${esMultiple})"
                            title="Eliminar sugerencia">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Construir tabla completa
    container.innerHTML = `
        <table class="sugerencias-table">
            <thead class="sugerencias-table-head">
                <tr class="sugerencias-table-row">
                    <th class="sugerencias-table-header">Test</th>
                    <th class="sugerencias-table-header">Estudiante</th>
                    <th class="sugerencias-table-header">Curso</th>
                    <th class="sugerencias-table-header">Fecha Sugerencia</th>
                    <th class="sugerencias-table-header">Estado</th>
                    <th class="sugerencias-table-header">Acción</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHTML}
            </tbody>
        </table>
    `;
}

/**
 * Configurar filtros y búsqueda
 */
function configurarFiltros() {
    // Filtros por estado
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            filtroActual = btn.dataset.filter;
            renderSugerencias();
        });
    });
    
    // Búsqueda
    document.getElementById('searchInput').addEventListener('input', (e) => {
        busqueda = e.target.value;
        renderSugerencias();
    });
}

/**
 * Abrir modal de eliminación
 */
function abrirModalEliminar(idSugerencia, nombreTest, nombreEstudiante, nombreCurso, esMultiple) {
    sugerenciaAEliminar = idSugerencia;
    
    document.getElementById('modalTestNombre').textContent = nombreTest;
    document.getElementById('modalEstudiante').textContent = nombreEstudiante;
    document.getElementById('modalCurso').textContent = nombreCurso;
    
    // Actualizar mensaje si es múltiple
    const warningText = document.querySelector('.warning-text');
    if (esMultiple) {
        warningText.textContent = 'Nota: Otros profesores también sugirieron este test. Solo se eliminará tu sugerencia.';
        warningText.style.color = 'var(--warning-600)';
    } else {
        warningText.textContent = 'Esta acción no se puede deshacer.';
        warningText.style.color = '';
    }
    
    document.getElementById('eliminarModal').style.display = 'flex';
}

/**
 * Cerrar modal de eliminación
 */
function cerrarModalEliminar() {
    document.getElementById('eliminarModal').style.display = 'none';
    sugerenciaAEliminar = null;
}

/**
 * Confirmar eliminación
 */
async function confirmarEliminacion() {
    if (!sugerenciaAEliminar) return;
    
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;
        
        const response = await fetch(`${baseUrl}/api/sugerencias.php?action=eliminar`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                id_sugerencia: sugerenciaAEliminar
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Mostrar mensaje de éxito
            mostrarNotificacion(result.message, 'success');
            
            // Cerrar modal
            cerrarModalEliminar();
            
            // Recargar sugerencias
            await cargarSugerencias();
        } else {
            mostrarNotificacion('Error: ' + (result.message || 'No se pudo eliminar la sugerencia'), 'error');
        }
    } catch (error) {
        console.error('Error al eliminar sugerencia:', error);
        mostrarNotificacion('Error de conexión al eliminar la sugerencia', 'error');
    }
}

/**
 * Mostrar mensaje en el contenedor
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.getElementById('sugerenciasGrid');
    const iconClass = tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    const colorClass = tipo === 'error' ? 'text-danger' : 'text-info';
    
    container.innerHTML = `
        <div class="mensaje-container ${colorClass}">
            <i class="fas ${iconClass}"></i>
            <p>${mensaje}</p>
        </div>
    `;
}

/**
 * Mostrar notificación temporal
 */
function mostrarNotificacion(mensaje, tipo = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${tipo}`;
    notification.innerHTML = `
        <i class="fas ${tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        <span>${mensaje}</span>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 100);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

/**
 * Escapar HTML para prevenir XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

/**
 * Formatear fecha a formato legible
 */
function formatearFecha(fecha) {
    if (!fecha) return 'N/A';
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    const hora = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
}
</script>
