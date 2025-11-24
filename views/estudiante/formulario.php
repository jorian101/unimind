<?php
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../models/estudiante/TestsEstudianteModel.php';

// Get test data from URL parameters
$testId = $_GET['test_id'] ?? null;

if (!$testId) {
    header('Location: ?role=estudiante&page=tests');
    exit;
}

// Cargar datos del test desde la base de datos
$model = new TestsEstudianteModel();
$testInfo = $model->getTestById($testId);

if (!$testInfo) {
    echo "<script>alert('Test no encontrado'); window.location.href='?role=estudiante&page=tests';</script>";
    exit;
}

$items = $model->getItemsByTest($testId);
$opciones = $model->getOpcionesByTestId($testId); // Obtener opciones filtradas por tipo de escala

$testName = $testInfo['nombre'];
$totalQuestions = count($items);

// El breadcrumb se construye automáticamente desde la configuración
renderPageHeader();
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

        <?php
        // Construir action absoluto usando base detectado en index.php
        $basePath = isset($base) ? rtrim($base, '/') : '';
        $submitAction = $basePath . '/controllers/submit-test.php';
        ?>
        <form class="formulario__form" id="testForm" method="POST" action="<?php echo htmlspecialchars($submitAction); ?>">
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
                <?php foreach ($items as $index => $item): 
                    $questionNumber = $index + 1;
                ?>
                    <div class="formulario__question-block" data-question="<?php echo $questionNumber; ?>" style="<?php echo $index === 0 ? '' : 'display:none;'; ?>">
                        <div class="formulario__question">
                            <h3 class="formulario__question-title">
                                <span class="formulario__question-number"><?php echo $questionNumber; ?>.</span>
                                <?php echo htmlspecialchars($item['texto_item']); ?>
                            </h3>
                            <div class="formulario__options">
                                <?php foreach ($opciones as $opcion): ?>
                                    <label class="formulario__option">
                                        <input 
                                            type="radio" 
                                            name="item_<?php echo $item['id_item']; ?>" 
                                            value="<?php echo $opcion['id_opcion']; ?>"
                                            class="formulario__option-input"
                                            data-item-id="<?php echo $item['id_item']; ?>"
                                            required
                                        >
                                        <span class="formulario__option-custom"></span>
                                        <span class="formulario__option-label"><?php echo htmlspecialchars($opcion['texto_opcion']); ?></span>
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

    // Form submission via AJAX to maintain session
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const answered = form.querySelectorAll('input[type="radio"]:checked').length;
        if (answered < totalQuestions) {
            alert(`Por favor, responde todas las preguntas. Te faltan ${totalQuestions - answered} preguntas.`);
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
        
        try {
            // Enviar formulario via AJAX para mantener sesión
            const formData = new FormData(form);
            const base = window.UNIMIND_BASE || '';
            const baseUrl = window.location.origin && window.location.origin !== 'null' 
                ? window.location.origin + base 
                : base;
            
            const response = await fetch(`${baseUrl}/controllers/submit-test.php`, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            let result;
            try {
                result = await response.json();
            } catch (e) {
                console.error('Error al parsear JSON:', e);
                throw new Error('Respuesta del servidor no válida');
            }
            
            if (result.success) {
                // Redirigir con parámetros de resultado en la URL (fallback si sesión falla)
                const params = new URLSearchParams({
                    role: 'estudiante',
                    page: 'tests',
                    test_completed: '1',
                    name: result.data.test_name || '',
                    score: result.data.score || '0',
                    level: result.data.level || '',
                    completed_at: result.data.completed_at || ''
                });
                window.location.href = `${baseUrl}/index.php?${params.toString()}`;
            } else {
                // Si no está autenticado, redirigir al login
                if (response.status === 401 && result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }
                throw new Error(result.message || 'Error al enviar el test');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Hubo un error al enviar el test. Por favor, intenta de nuevo.');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check"></i> Enviar Evaluación';
        }
    });
});
</script>
