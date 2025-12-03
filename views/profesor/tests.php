<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../database/Database.php';
// Usar el modelo específico para tests (nombre: TestModel)
require_once __DIR__ . '/../../models/profesor/TestModel.php';

// Conectar a la base de datos
$database = new Database();
$conn = $database->connect();
$model = new TestModel($conn);

// Obtener todos los tests con detalles completos
// Obtener todos los tests con detalles completos
$tests = $model->getAllTestsConDetalles();

// Nota: debug temporal removido

renderPageHeader();
?>
<link rel="stylesheet" href="views/profesor/tests.css?v=<?php echo time(); ?>">

<div class="tests-container">
    <div class="tests-header">
        <h2 class="tests-title">Tests Disponibles</h2>
        <p class="tests-subtitle">Gestiona y sugiere evaluaciones psicológicas a tus cursos</p>
    </div>

    <div class="tests-list" id="testsGrid">
        <div class="loading-container">
            <div class="spinner"></div>
            <p>Cargando tests...</p>
        </div>
    </div>
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
 * Renderizar la lista de tests
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
    
    const testsHTML = tests.map(test => {
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
        
        // Construir tags de opciones de la escala
        let opcionesTags = '';
        if (test.opciones && test.opciones.length > 0) {
            opcionesTags = test.opciones.map(opcion => 
                `<span class="option-tag" title="${escapeHtml(opcion.texto_opcion)}">
                    ${escapeHtml(opcion.texto_opcion)} (${opcion.valor_puntuacion})
                </span>`
            ).join('');
        } else {
            opcionesTags = '<span class="option-tag-empty">Sin opciones definidas</span>';
        }
        
        return `
            <div class="test-card">
                <div class="test-card-header">
                    <div class="test-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="test-header-content">
                        <h3 class="test-name">${escapeHtml(test.nombre)}</h3>
                    </div>
                    <button class="btn-sugerir" onclick="abrirModalSugerir(${test.id_test}, '${escapeHtml(test.nombre)}', '${escapeHtml(test.descripcion || '')}')">
                        <i class="fas fa-paper-plane"></i>
                        Sugerir
                    </button>
                </div>
                
                <div class="test-card-body">
                    <p class="test-description">${escapeHtml(test.descripcion || 'Sin descripción disponible')}</p>
                    
                    <div class="test-details-grid">
                        <div class="detail-item">
                            <i class="fas fa-list-ol"></i>
                            <span><strong>Items:</strong> ${test.num_items}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-ruler"></i>
                            <span><strong>Escala:</strong> ${escapeHtml(test.nombre_escala || 'No definida')}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-plus"></i>
                            <span><strong>Creado:</strong> ${fechaCreacion}</span>
                        </div>
                        <div class="detail-item">
                            <i class="fas fa-calendar-edit"></i>
                            <span><strong>Actualizado:</strong> ${fechaActualizacion}</span>
                        </div>
                    </div>
                    
                    ${test.descripcion_escala ? `
                        <div class="escala-info">
                            <h4><i class="fas fa-info-circle"></i> Descripción de la Escala</h4>
                            <p>${escapeHtml(test.descripcion_escala)}</p>
                        </div>
                    ` : ''}
                    
                    <div class="opciones-section">
                        <h4><i class="fas fa-tags"></i> Opciones de Respuesta</h4>
                        <div class="opciones-tags">
                            ${opcionesTags}
                        </div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = testsHTML;
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
            ? window.location.origin + (base ? base : '') 
            : (base ? base : '');
        
        // Simulación - en producción deberías tener un endpoint para esto
        cursosDisponibles = [
            { id_curso: 1, nombre_curso: 'Curso de Ejemplo 1' },
            { id_curso: 2, nombre_curso: 'Curso de Ejemplo 2' }
        ];
        
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
function confirmarSugerencia() {
    const cursoId = document.getElementById('selectCurso').value;
    
    if (!cursoId) {
        alert('Por favor, selecciona un curso');
        return;
    }
    
    if (!testSeleccionado) {
        alert('Error: No se ha seleccionado ningún test');
        return;
    }
    
    // Aquí iría la lógica para sugerir el test
    console.log(`Sugiriendo test ${testSeleccionado} al curso ${cursoId}`);
    alert('Funcionalidad de sugerencia en desarrollo');
    
    cerrarModalSugerir();
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
</script>
