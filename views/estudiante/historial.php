<?php
session_start();
require_once dirname(__DIR__) . '/./pageHeader.php';
require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ?role=estudiante&page=login');
    exit;
}

// Cargar historial del usuario
$model = new TestsEstudianteModel();
$id_usuario = $_SESSION['id_usuario'];
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
</script>
