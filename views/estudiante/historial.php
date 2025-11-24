<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/./pageHeader.php';
require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';

// Verificar que el usuario esté autenticado (aceptar 'id_usuario' o 'user_id' por compatibilidad)
if (!isset($_SESSION['id_usuario']) && !isset($_SESSION['user_id'])) {
    // Redirigir a la página de login de autenticación (ruta centralizada)
    echo '<script>window.location.href = "?role=autenticacion&page=login";</script>';
    exit;
}

// Cargar historial del usuario
$model = new TestsEstudianteModel();
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? null;

if (!$id_usuario) {
    echo '<script>window.location.href = "?role=autenticacion&page=login";</script>';
    exit;
}

$historial = $model->getHistorialUsuario($id_usuario);

// Mensaje de éxito si viene de completar un test
$showSuccess = isset($_GET['success']) && $_GET['success'] == 1;
$resultado = $_SESSION['test_resultado'] ?? null;
if ($showSuccess && $resultado) {
    unset($_SESSION['test_resultado']); // Limpiar después de mostrar
}

renderPageHeader('Historial de evaluaciones', ['Dashboard', 'Historial de evaluaciones']);
?>
<link rel="stylesheet" href="views/estudiante/historial.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php if ($showSuccess && $resultado): ?>
<div class="success-message" style="background: #d4edda; color: #155724; padding: 1rem; margin-bottom: 1rem; border-radius: 8px; border: 1px solid #c3e6cb;">
    <i class="fas fa-check-circle"></i>
    <strong>¡Test completado con éxito!</strong>
    <p>Resultado: <strong><?php echo htmlspecialchars($resultado['resultado_nivel']); ?></strong> - Puntuación: <?php echo $resultado['puntuacion_total']; ?></p>
</div>
<?php endif; ?>

<div class="historial">
    <section class="historial__card historial__card--history">
        <h2 class="historial__title">Evaluaciones</h2>
        <p class="historial__subtitle">Registro completo de tus evaluaciones psicológicas</p>

        <div class="historial__table-container">
            <table class="historial__table" id="history-table">
                <thead class="historial__table-head">
                    <tr class="historial__table-row">
                        <th class="historial__table-header">Fecha</th>
                        <th class="historial__table-header">Test</th>
                        <th class="historial__table-header">Puntuación</th>
                        <th class="historial__table-header">Nivel</th>
                        <th class="historial__table-header">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($historial)): ?>
                        <tr class="historial__table-row">
                            <td colspan="4" class="historial__table-cell" style="text-align: center; padding: 2rem;">
                                <i class="fas fa-inbox" style="font-size: 2rem; color: #ccc; display: block; margin-bottom: 0.5rem;"></i>
                                No has completado ninguna evaluación todavía
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($historial as $item): 
                            // Determinar clase según el nivel
                            $nivelClass = 'moderado';
                            if (stripos($item['resultado_nivel'], 'bajo') !== false || stripos($item['resultado_nivel'], 'mínimo') !== false) {
                                $nivelClass = 'bajo';
                            } elseif (stripos($item['resultado_nivel'], 'alto') !== false || stripos($item['resultado_nivel'], 'severo') !== false) {
                                $nivelClass = 'alto';
                            }
                            
                            $fecha_formateada = date('d/m/Y H:i', strtotime($item['fecha_aplicacion']));
                        ?>
                            <tr class="historial__table-row">
                                <td class="historial__table-cell"><?php echo $fecha_formateada; ?></td>
                                <td class="historial__table-cell"><?php echo htmlspecialchars($item['Nombre_Test']); ?></td>
                                <td class="historial__table-cell">
                                    <span class="historial__percentage"><?php echo $item['puntuacion_total']; ?></span>
                                </td>
                                <td class="historial__table-cell">
                                    <span class="historial__badge historial__badge--<?php echo $nivelClass; ?>">
                                        <?php echo htmlspecialchars($item['resultado_nivel']); ?>
                                    </span>
                                </td>
                                <td class="historial__table-cell">
                                    <button class="btn-ver-detalle" 
                                        onclick="verDetalle(<?php echo $item['id_aplicacion']; ?>, '<?php echo htmlspecialchars($item['Nombre_Test']); ?>')">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="historial__card historial__card--stats">
        <h2 class="historial__title">Estadísticas del Mes</h2>
        <p class="historial__subtitle">Resumen de tu progreso mensual</p>

        <div class="historial__stats-grid">
            <div class="historial__stat-item">
                <p class="historial__stat-label">Promedio de Estrés</p>
                <p class="historial__stat-value" id="avg-stress">--%</p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Promedio de Ansiedad</p>
                <p class="historial__stat-value" id="avg-anxiety">--%</p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Tendencia General</p>
                <p class="historial__stat-value" id="trend">--</p>
            </div>
        </div>
    </section>

    <?php if (!empty($historial)): 
        // Calcular estadísticas básicas
        $total_evaluaciones = count($historial);
        $suma_puntuaciones = array_sum(array_column($historial, 'puntuacion_total'));
        $promedio = $total_evaluaciones > 0 ? round($suma_puntuaciones / $total_evaluaciones, 1) : 0;
    ?>
    <section class="historial__card historial__card--stats">
        <h2 class="historial__title">Estadísticas</h2>
        <p class="historial__subtitle">Resumen de tus evaluaciones</p>

        <div class="historial__stats-grid">
            <div class="historial__stat-item">
                <p class="historial__stat-label">Total de Evaluaciones</p>
                <p class="historial__stat-value"><?php echo $total_evaluaciones; ?></p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Puntuación Promedio</p>
                <p class="historial__stat-value"><?php echo $promedio; ?></p>
            </div>
            <div class="historial__stat-item">
                <p class="historial__stat-label">Última Evaluación</p>
                <p class="historial__stat-value"><?php echo date('d/m/Y', strtotime($historial[0]['fecha_aplicacion'])); ?></p>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<!-- Modal para ver detalles de la aplicación -->
<div class="modal-overlay" id="detalleModal" style="display: none;">
    <div class="modal-content-detalle">
        <div class="modal-header">
            <h2 id="modalTitle">Detalles de la Evaluación</h2>
            <button class="modal-close" onclick="cerrarModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body" id="modalBody">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Cargando detalles...
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="cerrarModal()">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-ocultar mensaje de éxito después de 5 segundos
    const successMessage = document.querySelector('.success-message');
    if (successMessage) {
        setTimeout(() => {
            successMessage.style.transition = 'opacity 0.5s';
            successMessage.style.opacity = '0';
            setTimeout(() => successMessage.remove(), 500);
        }, 5000);
    }
});

/**
 * Ver detalle de una aplicación
 */
async function verDetalle(idAplicacion, nombreTest) {
    const modal = document.getElementById('detalleModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    
    modalTitle.textContent = `Detalles: ${nombreTest}`;
    modalBody.innerHTML = '<div class="loading-spinner"><i class="fas fa-spinner fa-spin"></i> Cargando detalles...</div>';
    modal.style.display = 'flex';
    
    try {
        const base = window.UNIMIND_BASE || '';
        const baseUrl = window.location.origin && window.location.origin !== 'null' 
            ? window.location.origin + base 
            : base;
            
        const response = await fetch(
            `${baseUrl}/controllers/AplicacionesController.php?action=getDetalleAplicacion&id_aplicacion=${idAplicacion}`,
            {
                method: 'GET',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include'
            }
        );
        
        const result = await response.json();
        
        if (result.success && result.data) {
            renderDetalle(result.data);
        } else {
            modalBody.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>${result.message || 'No se pudieron cargar los detalles'}</p>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error al cargar detalles:', error);
        modalBody.innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error al cargar los detalles. Por favor, intenta de nuevo.</p>
            </div>
        `;
    }
}

/**
 * Renderizar detalles de la aplicación
 */
function renderDetalle(data) {
    const modalBody = document.getElementById('modalBody');
    
    if (!data.respuestas || data.respuestas.length === 0) {
        modalBody.innerHTML = '<p>No hay respuestas registradas para esta evaluación.</p>';
        return;
    }
    
    // Sección de resumen
    let html = `
        <div class="detalle-resumen">
            <div class="resumen-item">
                <span class="resumen-label"><i class="fas fa-calendar"></i> Fecha:</span>
                <span class="resumen-value">${formatearFecha(data.resultado.fecha_aplicacion)}</span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label"><i class="fas fa-trophy"></i> Puntuación Total:</span>
                <span class="resumen-value resumen-score">${data.resultado.puntuacion_total || 0}</span>
            </div>
            <div class="resumen-item">
                <span class="resumen-label"><i class="fas fa-chart-line"></i> Nivel:</span>
                <span class="resumen-value resumen-nivel">${data.resultado.resultado_nivel || 'No determinado'}</span>
            </div>
        </div>
        
        <h3 class="detalle-subtitle"><i class="fas fa-list-check"></i> Respuestas Detalladas</h3>
        <div class="detalle-respuestas">
    `;
    
    // Listar respuestas
    data.respuestas.forEach((item, index) => {
        html += `
            <div class="respuesta-item">
                <div class="respuesta-header">
                    <span class="respuesta-numero">Ítem ${item.orden || (index + 1)}</span>
                    <span class="respuesta-puntaje">+${item.puntuacion_obtenida} pts</span>
                </div>
                <div class="respuesta-pregunta">
                    <strong>${item.texto_item}</strong>
                </div>
                <div class="respuesta-opcion">
                    <i class="fas fa-check-circle"></i>
                    <span>${item.respuesta_seleccionada}</span>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    modalBody.innerHTML = html;
}

/**
 * Cerrar modal
 */
function cerrarModal() {
    const modal = document.getElementById('detalleModal');
    modal.style.display = 'none';
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    const date = new Date(fecha);
    const opciones = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return date.toLocaleDateString('es-ES', opciones);
}

// Cerrar modal al hacer clic fuera
document.addEventListener('click', function(e) {
    const modal = document.getElementById('detalleModal');
    if (e.target === modal) {
        cerrarModal();
    }
});
</script>
