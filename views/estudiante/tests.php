<?php
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';

// Obtener tests desde la base de datos
$model = new TestsEstudianteModel();
$tests = $model->getTestsDisponibles();

// El breadcrumb se detecta automáticamente desde routes-config.php
renderPageHeader();
?>
<link rel="stylesheet" href="views/estudiante/tests.css?v=<?php echo time(); ?>">

<div class="tests-list">
    <?php if (empty($tests)): ?>
        <div class="no-tests">
            <i class="fas fa-inbox"></i>
            <p>No hay tests disponibles en este momento</p>
        </div>
    <?php else: ?>
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
                    <span class="status pending">Disponible</span>
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
                        data-name="<?php echo htmlspecialchars($test['nombre']); ?>"
                        data-questions="<?php echo $test['num_items']; ?>">
                        Iniciar Test
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.iniciar-test').forEach(button => {
        button.addEventListener('click', () => {
            const testId = button.dataset.id;
            const testName = encodeURIComponent(button.dataset.name);
            const questions = button.dataset.questions;

            // Redirige al formulario con los parámetros del test seleccionado
            const url = `?role=estudiante&page=formulario&test_id=${testId}&test_name=${testName}&questions=${questions}`;
            window.location.href = url;
        });
    });
});
</script>
