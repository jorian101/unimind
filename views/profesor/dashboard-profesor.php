<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Profesor</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <link rel="stylesheet" href="views/profesor/styles.css">
</head>

<body>

    <main class="container">

        <header class="dashboard-header">
            <div>
                <h1 class="header-title">Dashboard de Reportes</h1>
                <p id="subtitulo-curso" class="header-subtitle">Monitorea el bienestar de tu aula</p>
            </div>
        </header>

        <section class="recommendations">
            <h2 class="section-title">Recomendaciones</h2>
            <p class="section-subtitle">Recursos y estrategias para apoyar a tus estudiantes</p>
            
            <div class="grid-container">
                
                <form id="form-sugerir-estres" class="card">
                    <div class="card-content">
                        <div class="card-icon bg-sky">
                            <span class="material-symbols-outlined">psychology_alt</span>
                        </div>
                        <div class="card-text">
                            <h3>Sugerir test de estrés</h3>
                            <p>Medir estrés de aula</p>
                        </div>
                    </div>
                    <button type="submit" id="btn-sugerir-estres" class="card-button">
                        Ver Más <span class="material-symbols-outlined">open_in_new</span>
                    </button>
                </form>
                
                <form id="form-sugerir-ansiedad" class="card">
                    <div class="card-content">
                        <div class="card-icon bg-pink">
                            <span class="material-symbols-outlined">monitor_heart</span>
                        </div>
                        <div class="card-text">
                            <h3>Sugerir test de ansiedad</h3>
                            <p>Medir ansiedad de aula</p>
                        </div>
                    </div>
                    <button type="submit" id="btn-sugerir-ansiedad" class="card-button">
                        Ver Más <span class="material-symbols-outlined">open_in_new</span>
                    </button>
                </form>
                
                <div class="card">
                    <div class="card-content">
                        <div class="card-icon bg-red">
                            <span class="material-symbols-outlined">warning</span>
                        </div>
                        <div class="card-text">
                            <h3>Niveles altos</h3>
                            <p>Requieren atención</p>
                        </div>
                    </div>
                    <a id="link-niveles-altos" href="#" class="card-button">
                        Ver Más <span class="material-symbols-outlined">open_in_new</span>
                    </a>
                </div>
            </div>
        </section>
        
        <section class="charts-grid">
            <div class="chart-container chart-span-2">
                <h3 class="chart-title">Evolución Temporal del Curso</h3>
                <div class="chart-wrapper" style="height: 320px;">
                    <div id="stats-temporal" class="chart-stats"></div>
                    <canvas id="temporalEvolutionChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h3 class="chart-title">Distribución por Nivel de Riesgo (Curso)</h3>
                <div class="chart-wrapper chart-doughnut-wrapper" style="height: 320px;">
                    <div class="chart-doughnut-inner">
                        <canvas id="riskDistributionChart"></canvas>
                    </div>
                    <div id="leyenda-riesgo" class="chart-doughnut-legend"></div>
                </div>
            </div>
            <div class="chart-container chart-span-3">
                <h3 class="chart-title">Niveles de Estrés y Ansiedad por Facultad (Global)</h3>
                <div class="chart-wrapper" style="height: 320px;">
                    <canvas id="facultyLevelsChart"></canvas>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal para sugerir tests (inyectado para que dashboard.js lo controle) -->
    <div id="sugerir-test-modal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="modal-title">
            <div class="modal-header">
                <h3 id="modal-title">Sugerir Test</h3>
                <button id="modal-btn-cerrar" class="modal-close-btn" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="modal-form-nombre">Nombre</label>
                    <input id="modal-form-nombre" type="text" readonly />
                </div>
                <div class="form-group">
                    <label for="modal-form-descripcion">Descripción</label>
                    <textarea id="modal-form-descripcion" rows="3" readonly></textarea>
                </div>
                <div class="form-group">
                    <label for="modal-form-preguntas">Número de preguntas</label>
                    <input id="modal-form-preguntas" type="text" readonly />
                </div>
                <div class="form-group">
                    <label for="modal-form-curso-select">Seleccionar curso</label>
                    <select id="modal-form-curso-select"></select>
                </div>
            </div>
            <div class="modal-actions">
                <button id="modal-btn-cancelar" class="btn btn-secondary">Cancelar</button>
                <button id="modal-btn-sugerir" class="btn btn-primary">Sugerir</button>
            </div>
        </div>
    </div>

    <script>
    // Base path para construcción de URLs
    if (!window.UNIMIND_BASE) {
        var pathname = window.location.pathname;
        if (pathname.includes('/unimind/') || pathname.startsWith('/unimind')) {
            window.UNIMIND_BASE = '/unimind';
        } else {
            window.UNIMIND_BASE = '';
        }
    }
    <?php if (isset($base) && $base): ?>
    window.UNIMIND_BASE = '<?php echo $base; ?>';
    <?php endif; ?>
    </script>
    <script src="dashboard.js"></script>

</body>
</html>