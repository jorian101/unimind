<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';
$model = new TestsEstudianteModel();
$userId = $_SESSION['id_usuario'] ?? null;
$tests = $model->getTestsDisponibles($userId);

// El breadcrumb se detecta automáticamente desde routes-config.php
renderPageHeader();
?>
<link rel="stylesheet" href="views/estudiante/tests.css?v=<?php echo time(); ?>">

<div class="tests-list" id="tests-container">
    <!-- Loading spinner -->
    <div class="loading-container">
        <div class="spinner"></div>
        <p>Cargando evaluaciones...</p>
    </div>
</div>

<!-- Modal de confirmación de test completado -->
<div class="modal-overlay" id="testCompletedModal" style="display: none;">
    <div class="modal-content-success">
        <div class="modal-icon-success">
            <i class="fas fa-check-circle"></i>
        </div>
        <!-- Aquí va el contenido del modal, el resto de la lista de tests va fuera del modal -->
    </div>
</div>

<?php foreach ($tests as $test): 
    $tiempoEstimado = ceil($test['num_items'] / 2); // ~2 preguntas por minuto
    $icon = 'fa-clipboard-list'; // icono por defecto
    // Asignar iconos según el tipo de test
    if (stripos($test['nombre'], 'estrés') !== false || stripos($test['nombre'], 'estres') !== false) {
        $icon = 'fa-chart-bar';
    } elseif (stripos($test['nombre'], 'ansiedad') !== false) {
        $icon = 'fa-brain';
    } elseif (stripos($test['nombre'], 'depresión') !== false || stripos($test['nombre'], 'depresion') !== false) {
        $icon = 'fa-heart-broken';
    } elseif (stripos($test['nombre'], 'burnout') !== false) {
        $icon = 'fa-fire';
    }
?>
    <div class="test-item">
        <div class="test-header">
            <h3><i class="fas <?php echo $icon; ?>"></i> <?php echo htmlspecialchars($test['nombre']); ?></h3>
            <?php if (!empty($test['id_aplicacion'])): ?>
                <span class="status suggested">Sugerido por profesor</span>
            <?php else: ?>
                <span class="status pending">Disponible</span>
            <?php endif; ?>
        </div>
        <div class="test-description">
            <p><?php echo htmlspecialchars($test['descripcion'] ?: 'Test de evaluación psicológica'); ?></p>
            <div class="test-details">
                <span class="detail"><i class="fas fa-list"></i> <?php echo $test['num_items']; ?> ítems</span>
                <span class="detail"><i class="fas fa-clock"></i> ~<?php echo $tiempoEstimado; ?> min</span>
            </div>
        </div>
        <div class="test-actions">
            <button class="btn-primary iniciar-test"
                data-id="<?php echo $test['id_test']; ?>"
                data-aplicacion="<?php echo isset($test['id_aplicacion']) ? $test['id_aplicacion'] : ''; ?>"
                data-name="<?php echo htmlspecialchars($test['nombre']); ?>"
                data-questions="<?php echo $test['num_items']; ?>">
                <?php echo !empty($test['id_aplicacion']) ? 'Iniciar (Sugerido)' : 'Iniciar Test'; ?>
            </button>
        </div>
    </div>
<?php endforeach; ?>
            </div>
        </div>
        <div class="modal-actions-success">
            <button class="btn-primary" onclick="cerrarModalYVerHistorial()">
                <i class="fas fa-history"></i> Ver Historial
            </button>
            <button class="btn-secondary" onclick="cerrarModal()">
                <i class="fas fa-list"></i> Ver Más Tests
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarTestsDisponibles();
    verificarTestCompletado();
});

/**
 * Cargar tests disponibles desde la API
 */
async function cargarTestsDisponibles() {
    const container = document.getElementById('tests-container');
    
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null' 
            ? window.location.origin + base 
            : base;
            
        const response = await fetch(
            `${baseUrl}/controllers/AplicacionesController.php?action=getTestsDisponibles`,
            {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'include'
            }
        );
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Error al cargar los tests');
        }
        
        if (result.success && result.data) {
            renderTests(result.data);
        } else {
            mostrarMensaje('No hay tests disponibles en este momento', 'info');
        }
        
    } catch (error) {
        console.error('Error al cargar tests:', error);
        mostrarMensaje('Error al cargar las evaluaciones. Por favor, intenta nuevamente.', 'error');
    }
}

/**
 * Renderizar la lista de tests
 */
function renderTests(tests) {
    const container = document.getElementById('tests-container');
    
    if (!tests || tests.length === 0) {
        container.innerHTML = `
            <div class="no-tests">
                <i class="fas fa-inbox"></i>
                <p>No hay tests disponibles en este momento</p>
            </div>
        `;
        return;
    }
    
    const testsHTML = tests.map(test => {
        const tiempoEstimado = Math.ceil(test.num_items / 2);
        let icon = 'fa-clipboard-list';
        const nombre = test.nombre.toLowerCase();
        
        // Determinar icono según el tipo de test
        if (nombre.includes('estrés') || nombre.includes('estres')) {
            icon = 'fa-chart-bar';
        } else if (nombre.includes('ansiedad')) {
            icon = 'fa-brain';
        } else if (nombre.includes('depresión') || nombre.includes('depresion')) {
            icon = 'fa-heart-broken';
        } else if (nombre.includes('burnout')) {
            icon = 'fa-fire';
        }
        
        // Determinar si está completado
        const completado = test.completado === true || test.completado === 1;
        const completadoClass = completado ? 'test-completado' : '';
        const statusText = completado ? 'Completado' : 'Disponible';
        const statusClass = completado ? 'completed' : 'pending';
        const buttonText = completado ? 'Ver Historial' : 'Iniciar Test';
        const buttonIcon = completado ? 'fa-history' : 'fa-play';
        
        return `
            <div class="test-item ${completadoClass}">
                <div class="test-header">
                    <h3><i class="fas ${icon}"></i> ${escapeHtml(test.nombre)}</h3>
                    <span class="status ${statusClass}">${statusText}</span>
                </div>
                <div class="test-description">
                    <p>${escapeHtml(test.descripcion || 'Test de evaluación psicológica')}</p>
                    <div class="test-details">
                        <span class="detail"><i class="fas fa-list"></i> ${test.num_items} ítems</span>
                        <span class="detail"><i class="fas fa-clock"></i> ~${tiempoEstimado} min</span>
                        ${test.created_at ? `<span class="detail"><i class="fas fa-calendar"></i> ${formatearFecha(test.created_at)}</span>` : ''}
                    </div>
                </div>
                <div class="test-actions">
                    <button class="btn-primary iniciar-test"
                        data-id="${test.id_test}"
                        data-name="${escapeHtml(test.nombre)}"
                        data-questions="${test.num_items}"
                        data-completado="${completado}">
                        <i class="fas ${buttonIcon}"></i> ${buttonText}
                    </button>
                </div>
            </div>
        `;
    }).join('');
    
    container.innerHTML = testsHTML;
    
    // Agregar event listeners a los botones
    container.querySelectorAll('.iniciar-test').forEach(button => {
        button.addEventListener('click', () => {
            const testId = button.dataset.id;
            const testName = encodeURIComponent(button.dataset.name);
            const questions = button.dataset.questions;
<<<<<<< HEAD
            const aplicacion = button.dataset.aplicacion || '';

            // Redirige al formulario con los parámetros del test seleccionado
            let url = `?role=estudiante&page=formulario&test_id=${testId}&test_name=${testName}&questions=${questions}`;
            if (aplicacion) url += `&id_aplicacion=${aplicacion}`;
            window.location.href = url;
=======
            const completado = button.dataset.completado === 'true';
            
            // Si ya está completado, ir al historial; si no, iniciar test
            if (completado) {
                window.location.href = `?role=estudiante&page=historial`;
            } else {
                window.location.href = `?role=estudiante&page=formulario&test_id=${testId}&test_name=${testName}&questions=${questions}`;
            }
>>>>>>> 971c157eca1e1524143cd3c8f9b1280bbdef7cc3
        });
    });
}

/**
 * Mostrar mensaje de error o información
 */
function mostrarMensaje(mensaje, tipo = 'info') {
    const container = document.getElementById('tests-container');
    const iconClass = tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle';
    const colorClass = tipo === 'error' ? 'text-danger' : 'text-info';
    
    container.innerHTML = `
        <div class="no-tests ${colorClass}">
            <i class="fas ${iconClass}"></i>
            <p>${escapeHtml(mensaje)}</p>
        </div>
    `;
}

/**
 * Escapar HTML para prevenir XSS
 */
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

/**
 * Formatear fecha a formato legible
 */
function formatearFecha(fecha) {
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, '0');
    const mes = String(date.getMonth() + 1).padStart(2, '0');
    const anio = date.getFullYear();
    const hora = String(date.getHours()).padStart(2, '0');
    const minutos = String(date.getMinutes()).padStart(2, '0');
    return `${dia}/${mes}/${anio} ${hora}:${minutos}`;
}

/**
 * Verificar si se completó un test y mostrar modal
 */
function verificarTestCompletado() {
    const urlParams = new URLSearchParams(window.location.search);
    const testCompleted = urlParams.get('test_completed');
    
    if (testCompleted === '1') {
        // Obtener resultado de la sesión mediante PHP
        <?php if (isset($_SESSION['test_resultado'])): ?>
            const resultado = <?php echo json_encode($_SESSION['test_resultado']); ?>;
            mostrarModalExito(resultado);
            <?php 
                // Limpiar la sesión después de mostrar
                unset($_SESSION['test_resultado']);
            ?>
        <?php else: ?>
            // Si la sesión no tiene datos (por ejemplo, cookie de sesión no disponible),
            // intentar leer parámetros de la URL como fallback.
            (function(){
                const params = new URLSearchParams(window.location.search);
                const name = params.get('name') ? decodeURIComponent(params.get('name')) : 'Evaluación';
                const score = params.get('score') ? parseInt(params.get('score'), 10) : 0;
                const level = params.get('level') ? decodeURIComponent(params.get('level')) : 'Completado';
                let completedAt = params.get('completed_at') ? decodeURIComponent(params.get('completed_at')) : new Date().toISOString();
                // Normalizar fecha si viene con espacio (YYYY-MM-DD HH:MM:SS)
                if (completedAt.indexOf(' ') !== -1) {
                    completedAt = completedAt.replace(' ', 'T');
                }

                mostrarModalExito({
                    test_name: name,
                    puntuacion_total: score,
                    resultado_nivel: level,
                    completed_at: completedAt
                });
            })();
        <?php endif; ?>
        
        // Limpiar URL sin recargar la página
        const cleanUrl = window.location.pathname + '?role=estudiante&page=tests';
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

/**
 * Mostrar modal de éxito con los resultados
 */
function mostrarModalExito(resultado) {
    const modal = document.getElementById('testCompletedModal');
    const resultDetails = document.getElementById('resultDetails');
    
    // Construir detalles del resultado
    let nivelColor = 'var(--success-500)';
    let nivelIcon = 'fa-smile';
    
    const nivelLower = resultado.resultado_nivel.toLowerCase();
    if (nivelLower.includes('alto') || nivelLower.includes('severo')) {
        nivelColor = 'var(--danger-500)';
        nivelIcon = 'fa-exclamation-triangle';
    } else if (nivelLower.includes('medio') || nivelLower.includes('moderado')) {
        nivelColor = 'var(--warning-500)';
        nivelIcon = 'fa-exclamation-circle';
    }
    
    resultDetails.innerHTML = `
        <div class="result-item">
            <span class="result-label">Test:</span>
            <span class="result-value"><strong>${escapeHtml(resultado.test_name)}</strong></span>
        </div>
        <div class="result-item">
            <span class="result-label">Nivel de Resultado:</span>
            <span class="result-value" style="color: ${nivelColor};">
                <i class="fas ${nivelIcon}"></i> 
                <strong>${escapeHtml(resultado.resultado_nivel)}</strong>
            </span>
        </div>
        <div class="result-item">
            <span class="result-label">Completado:</span>
            <span class="result-value">${formatearFecha(resultado.completed_at)}</span>
        </div>
    `;
    
    modal.style.display = 'flex';
    
    // Auto-cerrar después de 30 segundos
    setTimeout(() => {
        if (modal.style.display === 'flex') {
            cerrarModal();
        }
    }, 30000);
}

/**
 * Cerrar modal
 */
function cerrarModal() {
    const modal = document.getElementById('testCompletedModal');
    modal.style.display = 'none';
}

/**
 * Cerrar modal y redirigir a historial
 */
function cerrarModalYVerHistorial() {
    cerrarModal();
    window.location.href = '?role=estudiante&page=historial';
}
</script>

<style>
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem;
    gap: 1rem;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 4px solid rgba(0, 0, 0, 0.1);
    border-left-color: var(--primary-color, #007bff);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.text-danger {
    color: #dc3545;
}

.text-info {
    color: #17a2b8;
}

/* Modal de confirmación */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 1rem;
    backdrop-filter: blur(4px);
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-content-success {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 500px;
    width: 100%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.4s ease;
    text-align: center;
}

@keyframes slideUp {
    from {
        transform: translateY(30px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.modal-icon-success {
    font-size: 4rem;
    color: var(--success-500, #10b981);
    margin-bottom: 1rem;
    animation: bounceIn 0.6s ease;
}

@keyframes bounceIn {
    0% { transform: scale(0); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.modal-content-success h2 {
    color: var(--neutral-800, #1f2937);
    font-size: 1.75rem;
    margin-bottom: 1rem;
    font-weight: 700;
}

.modal-body-success {
    margin: 1.5rem 0;
}

.modal-body-success > p {
    color: var(--neutral-600, #6b7280);
    margin-bottom: 1.5rem;
    font-size: 1rem;
}

.result-details {
    background: var(--neutral-50, #f9fafb);
    border-radius: 12px;
    padding: 1.25rem;
    margin-top: 1rem;
    text-align: left;
}

.result-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--neutral-200, #e5e7eb);
}

.result-item:last-child {
    border-bottom: none;
}

.result-label {
    color: var(--neutral-600, #6b7280);
    font-size: 0.95rem;
}

.result-value {
    color: var(--neutral-800, #1f2937);
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-actions-success {
    display: flex;
    gap: 1rem;
    margin-top: 2rem;
    flex-wrap: wrap;
}

.modal-actions-success button {
    flex: 1;
    min-width: 150px;
    padding: 0.875rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.modal-actions-success .btn-primary {
    background: var(--primary-500, #3b82f6);
    color: white;
}

.modal-actions-success .btn-primary:hover {
    background: var(--primary-600, #2563eb);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.modal-actions-success .btn-secondary {
    background: var(--neutral-100, #f3f4f6);
    color: var(--neutral-700, #374151);
}

.modal-actions-success .btn-secondary:hover {
    background: var(--neutral-200, #e5e7eb);
    transform: translateY(-2px);
}

@media (max-width: 640px) {
    .modal-content-success {
        padding: 1.5rem;
    }
    
    .modal-actions-success {
        flex-direction: column;
    }
    
    .modal-actions-success button {
        width: 100%;
    }
}
</style>
