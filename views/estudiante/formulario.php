<?php
require_once dirname(__DIR__) . '/./pageHeader.php';

// Get test data from URL parameters
$testId = $_GET['test_id'] ?? 'estres-ansiedad';
$testName = $_GET['test_name'] ?? 'Test de Estrés y Ansiedad';
$totalQuestions = (int)($_GET['questions'] ?? 21);

// Sample questions - in a real app, these would come from database
$questions = [
    1 => "¿Con qué frecuencia has estado afectado por algo que ha ocurrido inesperadamente?",
    2 => "¿Con qué frecuencia te has sentido incapaz de controlar las cosas importantes en tu vida?",
    3 => "¿Con qué frecuencia te has sentido nervioso o estresado?",
    4 => "¿Con qué frecuencia has manejado con éxito los pequeños problemas irritantes de la vida?",
    5 => "¿Con qué frecuencia has sentido que has afrontado efectivamente los cambios importantes que han estado ocurriendo en tu vida?",
    6 => "¿Con qué frecuencia has estado seguro sobre tu capacidad para manejar tus problemas personales?",
    7 => "¿Con qué frecuencia has sentido que las cosas te van bien?",
    8 => "¿Con qué frecuencia has sentido que no podías afrontar todas las cosas que tenías que hacer?",
    9 => "¿Con qué frecuencia has podido controlar las dificultades de tu vida?",
    10 => "¿Con qué frecuencia te has sentido al control de todo?",
    11 => "¿Con qué frecuencia te has sentido molesto porque las cosas que te han pasado estaban fuera de tu control?",
    12 => "¿Con qué frecuencia has encontrado que no podías lidiar con todas las cosas que tenías que hacer?",
    13 => "¿Con qué frecuencia has sido capaz de controlar la forma en que pasas el tiempo?",
    14 => "¿Con qué frecuencia has sentido que las dificultades se acumulan tanto que no puedes superarlas?",
    15 => "¿Te sientes frecuentemente abrumado por las responsabilidades?",
    16 => "¿Con qué frecuencia experimentas síntomas físicos del estrés?",
    17 => "¿Te resulta difícil relajarte después del trabajo o estudio?",
    18 => "¿Con qué frecuencia tienes problemas para dormir debido al estrés?",
    19 => "¿Te sientes ansioso ante situaciones sociales?",
    20 => "¿Con qué frecuencia te sientes preocupado por el futuro?",
    21 => "¿Sientes que tu nivel de estrés afecta tu rendimiento académico o laboral?"
];

$options = [
    0 => "Nunca",
    1 => "Casi nunca", 
    2 => "De vez en cuando",
    3 => "A menudo",
    4 => "Muy a menudo"
];

renderPageHeader($testName, ['Inicio', 'Evaluaciones', $testName]);
?>
<link rel="stylesheet" href="views/estudiante/formulario.css?v=<?php echo time(); ?>">

<div class="formulario">
    <div class="formulario__container">
        <div class="formulario__header">
            <div class="formulario__info">
                <div class="formulario__badge">
                    <i class="fas fa-clipboard-list"></i>
                    Evaluación Psicológica
                </div>
                <p class="formulario__description">
                    Por favor, responde a cada pregunta seleccionando la opción que mejor describa tu experiencia durante las últimas semanas. 
                    No hay respuestas correctas o incorrectas, solo busca ser honesto contigo mismo.
                </p>
                <div class="formulario__metadata">
                    <span class="formulario__question-count">
                        <i class="fas fa-list-ol"></i>
                        <?php echo $totalQuestions; ?> preguntas
                    </span>
                    <span class="formulario__duration">
                        <i class="fas fa-clock"></i>
                        ~15-20 minutos
                    </span>
                </div>
            </div>
        </div>

        <form class="formulario__form" id="testForm" method="POST" action="controllers/submit-test.php">
            <input type="hidden" name="test_id" value="<?php echo htmlspecialchars($testId); ?>">
            <input type="hidden" name="test_name" value="<?php echo htmlspecialchars($testName); ?>">
            
            <div class="formulario__progress">
                <div class="formulario__progress-bar">
                    <div class="formulario__progress-fill" id="progressFill"></div>
                </div>
                <span class="formulario__progress-text" id="progressText">Pregunta 1 de <?php echo $totalQuestions; ?></span>
            </div>

            <!-- Bloque de preguntas con navegación móvil/tablet -->
            <div class="formulario__questions" id="questionsNavigator">
                <?php foreach ($questions as $index => $question): ?>
                    <div class="formulario__question-block" data-question="<?php echo $index; ?>" style="<?php echo $index === 1 ? '' : 'display:none;'; ?>">
                        <div class="formulario__question">
                            <h3 class="formulario__question-title">
                                <span class="formulario__question-number"><?php echo $index; ?>.</span>
                                <?php echo htmlspecialchars($question); ?>
                            </h3>
                            <div class="formulario__options">
                                <?php foreach ($options as $value => $label): ?>
                                    <label class="formulario__option">
                                        <input 
                                            type="radio" 
                                            name="question_<?php echo $index; ?>" 
                                            value="<?php echo $value; ?>"
                                            class="formulario__option-input"
                                            required
                                        >
                                        <span class="formulario__option-custom"></span>
                                        <span class="formulario__option-label"><?php echo htmlspecialchars($label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <!-- Botones navegación solo en móvil/tablet -->
                        <div class="formulario__nav-actions">
                            <button type="button" class="formulario__btn formulario__btn--secondary formulario__nav-prev" style="display:none;">
                                <i class="fas fa-arrow-left"></i> Atrás
                            </button>
                            <button type="button" class="formulario__btn formulario__btn--primary formulario__nav-next">
                                Siguiente <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="formulario__actions" id="formActions">
                <button type="button" class="formulario__btn formulario__btn--secondary" onclick="window.history.back()">
                    <i class="fas fa-arrow-left"></i>
                    Volver
                </button>
                <button type="submit" class="formulario__btn formulario__btn--primary" id="submitBtn">
                    <i class="fas fa-check"></i>
                    Enviar Evaluación
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('testForm');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const questionsNavigator = document.getElementById('questionsNavigator');
    const questionBlocks = questionsNavigator.querySelectorAll('.formulario__question-block');
    const totalQuestions = <?php echo $totalQuestions; ?>;
    let currentQuestion = 1;

    function updateProgress() {
        const answered = form.querySelectorAll('input[type="radio"]:checked').length;
        const percentage = (answered / totalQuestions) * 100;
        
        progressFill.style.width = percentage + '%';
        progressText.textContent = `${answered} de ${totalQuestions} respondidas`;
        
        if (answered === totalQuestions) {
            progressText.textContent = '¡Evaluación completa!';
            progressFill.style.backgroundColor = 'var(--acc-500)';
        }
    }
    
    function isMobileOrTablet() {
        return window.innerWidth <= 768;
    }

    function showQuestion(index) {
        questionBlocks.forEach((block, i) => {
            block.style.display = (i + 1 === index) ? '' : 'none';
        });
        // Actualiza botones navegación
        questionBlocks.forEach((block, i) => {
            const prevBtn = block.querySelector('.formulario__nav-prev');
            const nextBtn = block.querySelector('.formulario__nav-next');
            if (i + 1 === index) {
                if (prevBtn) prevBtn.style.display = (index > 1) ? '' : 'none';
                if (nextBtn) {
                    nextBtn.style.display = (index < totalQuestions) ? '' : 'none';
                    nextBtn.textContent = (index < totalQuestions) ? 'Siguiente ' : '';
                    if (index < totalQuestions) nextBtn.innerHTML = 'Siguiente <i class="fas fa-arrow-right"></i>';
                }
            }
        });
        // Oculta acciones principales en móvil/tablet excepto en la última pregunta
        document.getElementById('formActions').style.display = (isMobileOrTablet() && index < totalQuestions) ? 'none' : '';
    }

    function setupNavigation() {
        if (!isMobileOrTablet()) {
            // Muestra todas las preguntas y oculta navegación
            questionBlocks.forEach(block => {
                block.style.display = '';
                const navActions = block.querySelector('.formulario__nav-actions');
                if (navActions) navActions.style.display = 'none';
            });
            document.getElementById('formActions').style.display = '';
        } else {
            // Solo muestra una pregunta y activa navegación
            questionBlocks.forEach((block, i) => {
                block.style.display = (i === 0) ? '' : 'none';
                const navActions = block.querySelector('.formulario__nav-actions');
                if (navActions) navActions.style.display = 'flex';
            });
            showQuestion(currentQuestion);
        }
    }

    // Listen for radio button changes
    form.addEventListener('change', updateProgress);
    
    // Eventos para navegación
    questionsNavigator.addEventListener('click', function(e) {
        if (e.target.classList.contains('formulario__nav-next')) {
            // Validar respuesta antes de avanzar
            const currentBlock = questionBlocks[currentQuestion - 1];
            const checked = currentBlock.querySelector('input[type="radio"]:checked');
            if (!checked) {
                alert('Por favor, selecciona una opción antes de continuar.');
                return;
            }
            if (currentQuestion < totalQuestions) {
                currentQuestion++;
                showQuestion(currentQuestion);
                updateProgress();
            }
        }
        if (e.target.classList.contains('formulario__nav-prev')) {
            if (currentQuestion > 1) {
                currentQuestion--;
                showQuestion(currentQuestion);
                updateProgress();
            }
        }
    });

    // Responsive: actualiza navegación al cambiar tamaño
    window.addEventListener('resize', setupNavigation);
    setupNavigation();

    // Form submission
    form.addEventListener('submit', function(e) {
        const answered = form.querySelectorAll('input[type="radio"]:checked').length;
        if (answered < totalQuestions) {
            e.preventDefault();
            alert(`Por favor, responde todas las preguntas. Te faltan ${totalQuestions - answered} preguntas.`);
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
    });
});
</script>
