<?php
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../controllers/RecomendacionesController.php';

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: /unimind/index.php');
    exit;
}

// Obtener recomendaciones personalizadas
$controller = new RecomendacionesController();
$recomendaciones = $controller->getRecomendacionesParaEstudiante($_SESSION['id_usuario']);

renderPageHeader('Recomendaciones Personalizadas', ['Inicio', 'Recomendaciones']);

// Construir base URL para enlaces a assets
if (!function_exists('unimind_detect_base')) {
    function unimind_detect_base() {
        $derived = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $candidates = ['', '/unimind', $derived];
        foreach ($candidates as $c) {
            $swPath = $docroot . ($c === '' ? '' : $c) . '/sw.js';
            if (file_exists($swPath)) {
                return $c;
            }
        }
        return $derived;
    }
}
$baseUrl = unimind_detect_base();
echo '<link rel="stylesheet" href="' . $baseUrl . '/public/css/theme.css">';
?>

<style>
.recomendaciones-estudiante {
    padding: 0;
}

.rec-empty {
    text-align: center;
    padding: 3rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.rec-empty i {
    font-size: 4rem;
    color: var(--sec-500);
    margin-bottom: 1rem;
}

.rec-empty h3 {
    color: var(--pri-500);
    margin-bottom: 0.5rem;
}

.rec-empty p {
    color: var(--var-700);
}

.rec-grid {
    display: grid;
    gap: 1.5rem;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    padding: 0;
}

.rec-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border-left: 4px solid var(--acc-700);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.rec-card::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    background: var(--acc-100);
    border-radius: 0 0 0 100%;
    opacity: 0.3;
}

.rec-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.rec-card.priority-5 {
    border-left-color: var(--pri-500);
}

.rec-card.priority-4 {
    border-left-color: #dc3545;
}

.rec-card.priority-3 {
    border-left-color: #fd7e14;
}

.rec-card.priority-2 {
    border-left-color: #ffc107;
}

.rec-card.priority-1 {
    border-left-color: var(--acc-500);
}

.rec-card-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.rec-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.rec-icon.mental {
    background: linear-gradient(135deg, var(--acc-100) 0%, var(--acc-300) 100%);
    color: var(--acc-900);
}

.rec-icon.profesional {
    background: linear-gradient(135deg, var(--var-300) 0%, var(--var-500) 100%);
    color: white;
}

.rec-icon.fisica {
    background: linear-gradient(135deg, var(--sec-100) 0%, var(--sec-300) 100%);
    color: var(--sec-700);
}

.rec-icon.academica {
    background: linear-gradient(135deg, var(--pri-100) 0%, var(--pri-200) 100%);
    color: var(--pri-500);
}

.rec-icon.social {
    background: linear-gradient(135deg, var(--acc-100) 0%, var(--acc-300) 100%);
    color: var(--acc-700);
}

.rec-card-title-area {
    flex: 1;
}

.rec-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--pri-500);
    margin: 0 0 0.35rem 0;
    line-height: 1.3;
}

.rec-badges {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.rec-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 600;
}

.rec-badge.categoria {
    background: var(--bg-400);
    color: var(--var-700);
}

.rec-badge.nivel {
    background: var(--sec-100);
    color: var(--sec-700);
}

.rec-badge.test {
    background: var(--acc-100);
    color: var(--acc-700);
}

.rec-description {
    color: var(--var-700);
    font-size: 0.95rem;
    line-height: 1.6;
    margin: 1rem 0;
    position: relative;
    z-index: 1;
}

.rec-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid var(--bg-400);
    position: relative;
    z-index: 1;
}

.rec-priority {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    font-size: 0.85rem;
    font-weight: 600;
}

.rec-priority i {
    font-size: 1rem;
}

.rec-priority.priority-5 {
    color: var(--pri-500);
}

.rec-priority.priority-4 {
    color: #dc3545;
}

.rec-priority.priority-3 {
    color: #fd7e14;
}

.rec-priority.priority-2 {
    color: #ffc107;
}

.rec-priority.priority-1 {
    color: var(--acc-700);
}

@media (max-width: 768px) {
    .rec-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="recomendaciones-estudiante">
    <?php if (empty($recomendaciones)): ?>
        <!-- Estado vacío -->
        <div class="rec-empty">
            <i class="fa-solid fa-clipboard-question"></i>
            <h3>No hay recomendaciones disponibles</h3>
            <p>Completa algunos tests para recibir recomendaciones personalizadas según tus niveles de estrés y ansiedad.</p>
        </div>
    <?php else: ?>
        <!-- Grid de recomendaciones -->
        <div class="rec-grid">
            <?php 
            $iconosCategorias = [
                'mental' => 'fa-spa',
                'profesional' => 'fa-user-doctor',
                'fisica' => 'fa-dumbbell',
                'academica' => 'fa-book-open',
                'social' => 'fa-users'
            ];
            
            $nombresCategorias = [
                'mental' => 'Mental',
                'profesional' => 'Profesional',
                'fisica' => 'Física',
                'academica' => 'Académica',
                'social' => 'Social'
            ];

            $nombresPrioridad = [
                5 => 'Crítica',
                4 => 'Alta',
                3 => 'Media',
                2 => 'Media-Baja',
                1 => 'Baja'
            ];

            $nombresNivel = [
                'normal' => 'Normal',
                'leve' => 'Leve',
                'moderado' => 'Moderado',
                'alto' => 'Alto',
                'severo' => 'Severo'
            ];
            
            foreach ($recomendaciones as $rec): 
                $categoria = $rec['categoria'];
                $icono = $iconosCategorias[$categoria] ?? 'fa-lightbulb';
                $nombreCat = $nombresCategorias[$categoria] ?? ucfirst($categoria);
                $prioridad = (int)$rec['prioridad'];
                $nombrePrioridad = $nombresPrioridad[$prioridad] ?? 'Media';
                $nivelDetectado = $rec['nivel_detectado'] ?? '';
                $nombreNivel = $nombresNivel[$nivelDetectado] ?? '';
                $testTipo = $rec['test_tipo'] ?? '';
            ?>
            <div class="rec-card priority-<?php echo $prioridad; ?>">
                <div class="rec-card-header">
                    <div class="rec-icon <?php echo $categoria; ?>">
                        <i class="fa-solid <?php echo $icono; ?>"></i>
                    </div>
                    <div class="rec-card-title-area">
                        <h3 class="rec-card-title"><?php echo htmlspecialchars($rec['titulo']); ?></h3>
                        <div class="rec-badges">
                            <span class="rec-badge categoria">
                                <i class="fa-solid fa-tag"></i>
                                <?php echo $nombreCat; ?>
                            </span>
                            <?php if ($nombreNivel): ?>
                            <span class="rec-badge nivel">
                                <i class="fa-solid fa-signal"></i>
                                Nivel: <?php echo $nombreNivel; ?>
                            </span>
                            <?php endif; ?>
                            <?php if ($testTipo): ?>
                            <span class="rec-badge test">
                                <?php echo ucfirst($testTipo); ?>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <p class="rec-description">
                    <?php echo nl2br(htmlspecialchars($rec['descripcion'])); ?>
                </p>

                <div class="rec-meta">
                    <span class="rec-priority priority-<?php echo $prioridad; ?>">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        Prioridad: <?php echo $nombrePrioridad; ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>