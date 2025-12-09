<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../utils/ModelFactory.php';

// Usar ModelFactory para obtener el modelo de tests según rol (profesor)
$model = ModelFactory::createTestsModel();
// Obtener todos los tests con detalles completos
$tests = [];
if ($model) {
    if (method_exists($model, 'getAllTestsConDetalles')) {
        $tests = $model->getAllTestsConDetalles();
    } elseif (method_exists($model, 'getAllTests')) {
        $tests = $model->getAllTests();
    }
}

// Nota: debug temporal removido

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/tests.css?v=<?php echo time(); ?>">

<div class="tests-container">
    <section class="tests-card">
        <h2 class="tests-title">Tests Disponibles</h2>
        <p class="tests-subtitle">Gestiona y sugiere evaluaciones psicológicas a tus cursos</p>

        <div class="tests-table-container" id="testsGrid">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Cargando tests...
            </div>
        </div>
    </section>
</div>

<!-- Modal para sugerir test -->
<div class="modal-overlay" id="sugerirModal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Sugerir Test a Curso</h3>
            <button class="modal-close" onclick="cerrarModalSugerir()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="selectCurso">Seleccionar Curso</label>
                <select id="selectCurso" class="form-control">
                    <option value="">Cargando cursos...</option>
                </select>
            </div>
            <div class="test-info-box">
                <h4 id="testNombreModal"></h4>
                <p id="testDescripcionModal"></p>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrarModalSugerir()">Cancelar</button>
            <button class="btn-primary" onclick="confirmarSugerencia()">Sugerir Test</button>
        </div>
    </div>
</div>

<script>
let testSeleccionado = null;
let cursosDisponibles = [];

document.addEventListener('DOMContentLoaded', function() {
    cargarTests();
    cargarCursosProfesor();
});

/**
 * Cargar todos los tests con detalles
 */
async function cargarTests() {
    const container = document.getElementById('testsGrid');

    // Intentar obtener tests usando el mismo endpoint que el administrador
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl =
            window.location.origin && window.location.origin !== 'null'
                ? window.location.origin + base
                : base;

        const candidates = [
            `${baseUrl}/controllers/TestsController.php?action=getAll`,
            `${window.location.origin}/unimind/controllers/TestsController.php?action=getAll`,
            `${window.location.origin}/controllers/TestsController.php?action=getAll`,
        ];

        let responseData = null;
        for (const url of candidates) {
            try {
                const resp = await fetch(url, { credentials: 'include' });
                if (resp && resp.ok) {
                    const json = await resp.json();
                    if (json && json.success && Array.isArray(json.data)) {
                        responseData = json.data;
                        break;
                    }
                }
            } catch (e) {
                // intentar siguiente candidato
            }
        }

        if (!responseData) {
            // Fallback: mostrar mensaje vacío
            container.innerHTML = `
                <div class="no-tests">
                    <i class="fas fa-clipboard-list"></i>
                    <p>No hay tests disponibles en este momento.</p>
                </div>
            `;
            return;
        }

        // Renderizar con la misma función que ya existe
        renderTests(responseData);
    } catch (error) {
        console.error('Error al cargar tests:', error);
        container.innerHTML = `
            <div class="mensaje-container text-danger">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error al cargar los tests. Por favor, intenta nuevamente.</p>
            </div>
        `;
    }
}

/**
 * Renderizar la lista de tests como tabla
 */
function renderTests(tests) {
    const container = document.getElementById('testsGrid');
    
    if (!tests || tests.length === 0) {
        container.innerHTML = `
            <div class="no-tests">
                <i class="fas fa-clipboard-list"></i>
                <p>No hay tests disponibles</p>
            </div>
        `;
        return;
    }
    
    // Construir filas de la tabla
    const rowsHTML = tests.map(test => {
        // Determinar icono según el tipo de test
        let icon = 'fa-clipboard-list';
        const nombre = test.nombre.toLowerCase();
        
        if (nombre.includes('estrés') || nombre.includes('estres')) {
            icon = 'fa-chart-bar';
        } else if (nombre.includes('ansiedad')) {
            icon = 'fa-brain';
        } else if (nombre.includes('depresión') || nombre.includes('depresion')) {
            icon = 'fa-heart-broken';
        } else if (nombre.includes('burnout')) {
            icon = 'fa-fire';
        }
        
        // Formatear fechas
        const fechaCreacion = test.created_at ? formatearFecha(test.created_at) : 'N/A';
        const fechaActualizacion = test.updated_at ? formatearFecha(test.updated_at) : 'N/A';
        
        // Construir tags de opciones de la escala (versión compacta)
        let opcionesTags = '';
        if (test.opciones && test.opciones.length > 0) {
            opcionesTags = test.opciones.map(opcion => 
                `<span class="option-tag-small" title="${escapeHtml(opcion.texto_opcion)}: ${opcion.valor_puntuacion}">
                    ${escapeHtml(opcion.texto_opcion.substring(0, 15))}${opcion.texto_opcion.length > 15 ? '...' : ''}
                </span>`
            ).join('');
        } else {
            opcionesTags = '<span class="option-tag-empty">N/A</span>';
        }
        
        return `
            <tr class="tests-table-row">
                <td class="tests-table-cell">
                    <div class="test-name-cell">
                        <div class="test-icon">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div>
                            <div class="test-name">${escapeHtml(test.nombre)}</div>
                            <div class="test-description">${escapeHtml(test.descripcion || '')}</div>
                        </div>
                    </div>
                </td>
                <td class="tests-table-cell">
                    <span class="tipo-badge tipo-${test.tipo_test || 'estres'}">
                        ${test.tipo_test === 'ansiedad' ? 'Ansiedad' : 'Estrés'}
                    </span>
                </td>
                <td class="tests-table-cell">
                    <span class="items-badge">
                        <i class="fas fa-list-ol"></i>
                        ${test.num_items}
                    </span>
                </td>
                <td class="tests-table-cell">
                    <span class="escala-name">${escapeHtml(test.nombre_escala || 'No definida')}</span>
                </td>
                <td class="tests-table-cell">
                    <div class="date-cell">${fechaCreacion}</div>
                </td>
                <td class="tests-table-cell">
                    <div class="opciones-compact">
                        ${opcionesTags}
                    </div>
                </td>
                <td class="tests-table-cell">
                    <button class="btn-sugerir" onclick="abrirModalSugerir(${test.id_test}, '${escapeHtml(test.nombre).replace(/'/g, "\\'")}', '${escapeHtml(test.descripcion || '').replace(/'/g, "\\'")}')">
                        <i class="fas fa-paper-plane"></i>
                        <span>Sugerir</span>
                    </button>
                </td>
            </tr>
        `;
    }).join('');
    
    // Construir tabla completa
    container.innerHTML = `
        <table class="tests-table">
            <thead class="tests-table-head">
                <tr class="tests-table-row">
                    <th class="tests-table-header">Test</th>
                    <th class="tests-table-header">Tipo</th>
                    <th class="tests-table-header">Items</th>
                    <th class="tests-table-header">Escala</th>
                    <th class="tests-table-header">Creado</th>
                    <th class="tests-table-header">Opciones</th>
                    <th class="tests-table-header">Acción</th>
                </tr>
            </thead>
            <tbody>
                ${rowsHTML}
            </tbody>
        </table>
    `;
}

/**
 * Cargar los cursos del profesor
 */
async function cargarCursosProfesor() {
    try {
        const userId = <?php echo $_SESSION['id_usuario'] ?? 'null'; ?>;
        if (!userId) {
            console.error('No hay sesión de usuario');
            return;
        }
        
        // Aquí deberías hacer un fetch a una API que devuelva los cursos del profesor
        // Por ahora usamos datos de ejemplo
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;

        const candidates = [
            `${baseUrl}/controllers/TestsController.php?action=getCursosProfesor`,
            `${window.location.origin}/unimind/controllers/TestsController.php?action=getCursosProfesor`,
            `${window.location.origin}/controllers/TestsController.php?action=getCursosProfesor`,
        ];

        let responseData = null;
        for (const url of candidates) {
            try {
                const resp = await fetch(url, { credentials: 'include' });
                if (resp && resp.ok) {
                    const json = await resp.json();
                    if (json && json.success && Array.isArray(json.data)) {
                        responseData = json.data;
                        break;
                    }
                }
            } catch (e) {
                // intentar siguiente candidato
            }
        }

        if (responseData) {
            cursosDisponibles = responseData;
        } else {
            cursosDisponibles = [];
            console.warn('No se pudieron obtener cursos del servidor; usando lista vacía');
        }
        
    } catch (error) {
        console.error('Error al cargar cursos:', error);
    }
}

/**
 * Abrir modal para sugerir test
 */
function abrirModalSugerir(idTest, nombreTest, descripcionTest) {
    testSeleccionado = idTest;
    
    // Actualizar información del test en el modal
    document.getElementById('testNombreModal').textContent = nombreTest;
    document.getElementById('testDescripcionModal').textContent = descripcionTest || 'Sin descripción';
    
    // Cargar cursos en el select
    const selectCurso = document.getElementById('selectCurso');
    if (cursosDisponibles.length > 0) {
        selectCurso.innerHTML = '<option value="">Selecciona un curso...</option>' +
            cursosDisponibles.map(curso => 
                `<option value="${curso.id_curso}">${escapeHtml(curso.nombre_curso)}</option>`
            ).join('');
    } else {
        selectCurso.innerHTML = '<option value="">No hay cursos asignados</option>';
    }
    
    // Mostrar modal
    document.getElementById('sugerirModal').style.display = 'flex';
}

/**
 * Cerrar modal de sugerir
 */
function cerrarModalSugerir() {
    document.getElementById('sugerirModal').style.display = 'none';
    testSeleccionado = null;
}

/**
 * Confirmar sugerencia de test
 */
async function confirmarSugerencia() {
    const cursoId = document.getElementById('selectCurso').value;
    
    if (!cursoId) {
        alert('Por favor, selecciona un curso');
        return;
    }
    
    if (!testSeleccionado) {
        alert('Error: No se ha seleccionado ningún test');
        return;
    }
    
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null'
            ? window.location.origin + base
            : base;
        
        const response = await fetch(`${baseUrl}/api/suggest_test.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include',
            body: JSON.stringify({
                id_test: testSeleccionado,
                id_curso: parseInt(cursoId)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const estudiantesAfectados = result.data?.estudiantes_afectados || 0;
            mostrarNotificacion(
                `Test sugerido correctamente a ${estudiantesAfectados} estudiante(s) del curso`,
                'success'
            );
            cerrarModalSugerir();
        } else {
            mostrarNotificacion(
                'Error: ' + (result.message || 'No se pudo sugerir el test'),
                'error'
            );
        }
    } catch (error) {
        console.error('Error al sugerir test:', error);
        alert('❌ Error de conexión al sugerir el test');
    }
}

/**
 * Mostrar mensaje de error o información
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.getElementById('testsGrid');
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
    return `${dia}/${mes}/${anio}`;
}

/**
 * Mostrar notificación usando el Toast global
 */
function mostrarNotificacion(mensaje, tipo = 'info') {
    if (window.Toast) {
        window.Toast.show({
            message: mensaje,
            type: tipo
        });
    } else {
        console.warn('Toast no está disponible');
    }
}
</script>
