<?php
require_once dirname(__DIR__) . '/pageHeader.php';
// El breadcrumb se detecta automáticamente desde routes-config.php
renderPageHeader();
?>
<link rel="stylesheet" href="views/estudiante/tests.css?v=<?php echo time(); ?>">

<div class="tests-list">
    <div class="test-item">
        <div class="test-header">
            <h3><i class="fas fa-chart-bar"></i> PSS-4 (Estrés)</h3>
            <span class="status pending">Pendiente</span>
        </div>
        <div class="test-description">
            <p>Evalúa el nivel de estrés percibido en situaciones diarias.</p>
            <div class="test-details">
                <span class="detail"><i class="fas fa-list"></i> 4 ítems</span>
                <span class="detail"><i class="fas fa-clock"></i> 1 min</span>
                <span class="detail"><i class="fas fa-calendar-alt"></i> Diario/semanal</span>
            </div>
        </div>
        <div class="test-actions">
            <button class="btn-primary iniciar-test"
                data-id="pss-4"
                data-name="PSS-4 (Estrés)"
                data-questions="4">
                Iniciar Test
            </button>
        </div>
    </div>

    <div class="test-item">
        <div class="test-header">
            <h3><i class="fas fa-brain"></i> GAD-2 (Ansiedad)</h3>
            <span class="status completed">Completado</span>
        </div>
        <div class="test-description">
            <p>Mide síntomas de ansiedad generalizada.</p>
            <div class="test-details">
                <span class="detail"><i class="fas fa-list"></i> 2 ítems</span>
                <span class="detail"><i class="fas fa-clock"></i> 30 seg</span>
                <span class="detail"><i class="fas fa-calendar-alt"></i> Diario/semanal</span>
            </div>
        </div>
        <div class="test-actions">
            <button class="btn-secondary">Ver Resultados</button>
        </div>
    </div>

    <div class="test-item">
        <div class="test-header">
            <h3><i class="fas fa-bullseye"></i> PSS-10 + GAD-7 (Seguimiento general)</h3>
            <span class="status pending">Pendiente</span>
        </div>
        <div class="test-description">
            <p>Combina evaluación de estrés y ansiedad para un seguimiento integral.</p>
            <div class="test-details">
                <span class="detail"><i class="fas fa-list"></i> 17 ítems</span>
                <span class="detail"><i class="fas fa-clock"></i> 5 min</span>
                <span class="detail"><i class="fas fa-calendar-alt"></i> Mensual/quincenal</span>
            </div>
        </div>
        <div class="test-actions">
            <button class="btn-primary iniciar-test"
                data-id="pss10-gad7"
                data-name="PSS-10 + GAD-7 (Seguimiento general)"
                data-questions="17">
                Iniciar Test
            </button>
        </div>
    </div>
</div>

<script>
// === Funcionalidad agregada (sin alterar CSS ni estructura) ===
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
</script>
