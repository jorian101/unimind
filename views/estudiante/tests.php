<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/pageHeader.php';

require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';
$model = new TestsEstudianteModel();
$userId = $_SESSION['id_usuario'] ?? null;
$tests = $model->getTestsSugeridos($userId);

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

<!-- Toast notification: se mostrará al completar un test (reemplaza el modal anterior) -->

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
            `${baseUrl}/controllers/AplicacionesController.php?action=getTestsSugeridos`,
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
        
        // Definir el estado del test
        let statusText = 'Disponible';
        let statusClass = 'pending';
        
        if (completado) {
            statusText = 'Completado';
            statusClass = 'completed';
        }

        const buttonText = completado ? 'Ver Historial' : 'Iniciar Test';
        const buttonIcon = completado ? 'fa-history' : 'fa-play';
        
        // Ya no mostramos información de sugerencia (Sugerido por profesor)
        let infoSugerencia = '';
        
        return `
            <div class="test-item ${completadoClass}">
                <div class="test-header">
                    <h3><i class="fas ${icon}"></i> ${escapeHtml(test.nombre)}</h3>
                    <span class="status ${statusClass}">${statusText}</span>
                </div>
                <div class="test-description">
                    <p>${escapeHtml(test.descripcion || 'Test de evaluación psicológica')}</p>
                    ${infoSugerencia}
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
                        data-completado="${completado}"
                        data-sugerencia="${test.id_sugerencia || ''}">
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
            const completado = button.dataset.completado === 'true';
            const sugerencia = button.dataset.sugerencia || '';
            
            // Si ya está completado, ir al historial; si no, iniciar test
            if (completado) {
                window.location.href = `?role=estudiante&page=historial`;
            } else {
                // Redirige al formulario con los parámetros del test seleccionado
                let url = `?role=estudiante&page=formulario&test_id=${testId}&test_name=${testName}&questions=${questions}`;
                if (sugerencia) {
                    url += `&id_sugerencia=${sugerencia}`;
                }
                window.location.href = url;
            }
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
            const mensaje = `✅ ${resultado.test_name} — ${resultado.resultado_nivel}`;
            mostrarNotificacion(mensaje, 'success', 'Ver Historial', function(){
                window.location.href = '?role=estudiante&page=historial';
            });
            <?php 
                // Limpiar la sesión después de mostrar
                unset($_SESSION['test_resultado']);
            ?>
        <?php else: ?>
            // Si la sesión no tiene datos, intentar leer parámetros de la URL como fallback.
            (function(){
                const params = new URLSearchParams(window.location.search);
                const name = params.get('name') ? decodeURIComponent(params.get('name')) : 'Evaluación';
                const level = params.get('level') ? decodeURIComponent(params.get('level')) : 'Completado';
                let completedAt = params.get('completed_at') ? decodeURIComponent(params.get('completed_at')) : new Date().toISOString();
                if (completedAt.indexOf(' ') !== -1) {
                    completedAt = completedAt.replace(' ', 'T');
                }

                const mensaje = `✅ ${name} — ${level}`;
                mostrarNotificacion(mensaje, 'success', 'Ver Historial', function(){
                    window.location.href = '?role=estudiante&page=historial';
                });
            })();
        <?php endif; ?>

        // Limpiar URL sin recargar la página
        const cleanUrl = window.location.pathname + '?role=estudiante&page=tests';
        window.history.replaceState({}, document.title, cleanUrl);
    }
}

/**
 * Mostrar notificación usando el Toast global
 * Wrapper para mantener compatibilidad
 */
function mostrarNotificacion(mensaje, tipo = 'info', actionLabel = null, actionCallback = null) {
    if (window.Toast) {
        window.Toast.show({
            message: mensaje,
            type: tipo,
            actionLabel: actionLabel,
            actionCallback: actionCallback
        });
    } else {
        console.warn('Toast no está disponible');
    }
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

/* Toast notification: ahora se usa el componente global public/js/toast.js */
</style>
