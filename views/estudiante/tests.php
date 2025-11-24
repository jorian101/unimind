<?php
require_once dirname(__DIR__) . '/pageHeader.php';

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

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarTestsDisponibles();
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
        
        return `
            <div class="test-item">
                <div class="test-header">
                    <h3><i class="fas ${icon}"></i> ${escapeHtml(test.nombre)}</h3>
                    <span class="status pending">Disponible</span>
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
                        data-questions="${test.num_items}">
                        Iniciar Test
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
            
            // Redirigir al formulario con los parámetros del test seleccionado
            window.location.href = `?role=estudiante&page=formulario&test_id=${testId}&test_name=${testName}&questions=${questions}`;
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
</style>
