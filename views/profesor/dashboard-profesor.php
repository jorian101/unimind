<?php
require_once dirname(__DIR__) . '/pageHeader.php';

// Datos de ejemplo para los gráficos (en un entorno real, estos provendrían de una base de datos)
$chart_data_temporal_stress = [30, 35, 45, 50, 40, 60];
$chart_data_temporal_anxiety = [20, 28, 38, 42, 35, 50];
$chart_labels_temporal = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'];

$chart_data_risk = [19, 45, 36]; // Bajo, Moderado, Alto (Porcentajes)
$chart_labels_risk = ['Bajo (0-3): 19%', 'Moderado (4-6): 45%', 'Alto (7-10): 36%'];

$chart_data_faculty_stress = [65, 59, 80, 81, 56];
$chart_data_faculty_anxiety = [55, 48, 70, 65, 45];
$chart_labels_faculty = ['Ingeniería', 'Medicina', 'Derecho', 'Artes', 'Economía'];

// Colores de la imagen para Ansiedad (#f472b6) y Estrés (#3b82f6)
$color_stress = '#3b82f6'; 
$color_anxiety = '#f472b6'; 

// La función renderPageHeader() se llama desde el require_once, 
// así que la omitimos aquí si ya está en tu estructura.
// renderPageHeader(); 
?>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="views/profesor/dashboard-profesor.css?v=<?php echo time(); ?>">

<script>
    // Configuración de Tailwind CSS (se mantiene la que definiste)
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#dc2626",
                    "background-light": "#f6f6f8",
                    "background-dark": "#101622",
                    "sidebar-active": "#fecaca",
                },
                fontFamily: {
                    "display": ["Inter"]
                },
                borderRadius: {
                    "DEFAULT": "0.5rem",
                    "lg": "1rem",
                    "xl": "1.5rem",
                    "full": "9999px"
                },
            },
        },
    }
</script>

<main class="dashboard-container font-display bg-background-light dark:bg-background-dark p-8 md:p-10" id="dashboard-profesor">

    <div class="flex flex-col gap-6 mb-8">
        <div class="flex flex-col">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recomendaciones</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recursos y estrategias para apoyar a tus estudiantes</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4 shadow-lg hover:shadow-xl transition duration-300">
                <div class="flex items-start gap-4">
                    <div class="bg-sky-100 dark:bg-sky-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-sky-600 dark:text-sky-400 text-2xl">psychology_alt</span></div>
                    <div class="flex flex-col flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Sugerir test de estrés</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Medir los niveles de estrés en el aula.</p>
                    </div>
                </div>
                <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end hover:underline">
                    Ver Más <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
            
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4 shadow-lg hover:shadow-xl transition duration-300">
                <div class="flex items-start gap-4">
                    <div class="bg-pink-100 dark:bg-pink-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-pink-600 dark:text-pink-400 text-2xl">monitor_heart</span></div>
                    <div class="flex flex-col flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Sugerir test de ansiedad</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Medir los niveles de ansiedad en el aula.</p>
                    </div>
                </div>
                <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end hover:underline">
                    Ver Más <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
            
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4 shadow-lg hover:shadow-xl transition duration-300">
                <div class="flex items-start gap-4">
                    <div class="bg-red-100 dark:bg-red-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span></div>
                    <div class="flex flex-col flex-1">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-lg">Niveles altos</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Estudiantes que requieren atención inmediata.</p>
                    </div>
                </div>
                <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end hover:underline">
                    Ver Más <span class="material-symbols-outlined text-base">arrow_forward</span>
                </button>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <div class="lg:col-span-2 rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Evolución Temporal del Aula</h3>
            <div class="h-80 flex-1 relative">
                <div class="absolute top-0 right-0 flex flex-col gap-1 text-sm z-10 p-2 bg-white dark:bg-gray-900 rounded-lg border border-gray-100 dark:border-gray-700">
                    <p class="text-gray-900 dark:text-white font-medium">Estrés Reciente: <span class="text-sky-600 dark:text-sky-400"><?php echo end($chart_data_temporal_stress); ?></span></p>
                    <p class="text-gray-900 dark:text-white font-medium">Ansiedad Reciente: <span class="text-pink-600 dark:text-pink-400"><?php echo end($chart_data_temporal_anxiety); ?></span></p>
                </div>
                <canvas id="temporalEvolutionChart"></canvas>
            </div>
        </div>
        
        <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col shadow-lg">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Distribución por Nivel de Riesgo</h3>
            <div class="h-80 flex-1 relative flex items-center justify-center">
                <div class="w-1/2 h-full flex items-center justify-center">
                    <canvas id="riskDistributionChart"></canvas>
                </div>
                <div class="absolute right-0 top-1/2 transform -translate-y-1/2 flex flex-col gap-2 text-sm">
                    <?php foreach ($chart_labels_risk as $label): ?>
                        <p class="text-gray-700 dark:text-gray-300 flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full <?php 
                                // Asignar color basado en el nivel
                                if (strpos($label, 'Bajo') !== false) echo 'bg-green-400';
                                elseif (strpos($label, 'Moderado') !== false) echo 'bg-amber-400';
                                else echo 'bg-red-500'; 
                            ?>"></span>
                            <?php echo $label; ?>
                        </p>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 shadow-lg">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Niveles de Estrés y Ansiedad por Facultad</h3>
        <div class="h-80"><canvas id="facultyLevelsChart"></canvas></div>
    </div>
</main>

<script>
    // --- Lógica de Chart.js y Dark Mode ---

    // Variables y colores dinámicos de PHP
    const stressColor = '<?php echo $color_stress; ?>'; // #3b82f6 (Azul)
    const anxietyColor = '<?php echo $color_anxiety; ?>'; // #f472b6 (Rosa)

    const isDarkMode = () => document.documentElement.classList.contains('dark');
    // Funciones para obtener colores de rejilla y ticks en función del modo (claro/oscuro)
    const chartGridColor = () => isDarkMode() ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    const chartTicksColor = () => isDarkMode() ? 'rgba(255, 255, 255, 0.8)' : 'rgba(0, 0, 0, 0.7)';
    const chartTooltipBg = () => isDarkMode() ? '#1f2937' : '#fff'; // Fondo oscuro para tooltip
    const chartTooltipTitleColor = () => isDarkMode() ? '#fff' : '#1f2937';
    const chartTooltipBodyColor = () => isDarkMode() ? '#d1d5db' : '#4b5563';
    
    // --- Chart 1: Evolución Temporal (Gráfico de Área - Línea) ---
    const ctx1 = document.getElementById('temporalEvolutionChart').getContext('2d');
    
    // Crear gradientes para el relleno del área
    const gradientStress = ctx1.createLinearGradient(0, 0, 0, 400);
    gradientStress.addColorStop(0, 'rgba(59, 130, 246, 0.5)'); // Azul (Estrés)
    gradientStress.addColorStop(1, 'rgba(59, 130, 246, 0)');
    
    const gradientAnxiety = ctx1.createLinearGradient(0, 0, 0, 400);
    gradientAnxiety.addColorStop(0, 'rgba(244, 114, 182, 0.5)'); // Rosa (Ansiedad)
    gradientAnxiety.addColorStop(1, 'rgba(244, 114, 182, 0)');
    
    const temporalEvolutionChart = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($chart_labels_temporal); ?>,
            datasets: [{
                label: 'Estrés',
                data: <?php echo json_encode($chart_data_temporal_stress); ?>,
                backgroundColor: gradientStress,
                borderColor: stressColor,
                tension: 0.4,
                fill: true, // Habilita el relleno de área
                pointRadius: 5,
                pointBackgroundColor: stressColor,
                pointHoverRadius: 7,
                borderWidth: 3
            }, {
                label: 'Ansiedad',
                data: <?php echo json_encode($chart_data_temporal_anxiety); ?>,
                backgroundColor: gradientAnxiety,
                borderColor: anxietyColor,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: anxietyColor,
                pointHoverRadius: 7,
                borderWidth: 3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true, // Mostramos la leyenda para identificar las líneas
                    labels: {
                        color: chartTicksColor, // Usa el color dinámico para los textos
                        font: { size: 14 }
                    }
                }, 
                tooltip: { 
                    backgroundColor: chartTooltipBg,
                    titleColor: chartTooltipTitleColor,
                    bodyColor: chartTooltipBodyColor,
                    borderColor: isDarkMode() ? '#475569' : '#ddd',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            },
            scales: {
                x: {
                    grid: { color: chartGridColor },
                    ticks: { color: chartTicksColor }
                },
                y: {
                    beginAtZero: true,
                    max: 80, 
                    grid: { color: chartGridColor },
                    ticks: { color: chartTicksColor, stepSize: 20 }
                }
            }
        }
    });

    // --- Chart 2: Distribución de Riesgo (Gráfico de Rosquilla - Doughnut) ---
    const ctx2 = document.getElementById('riskDistributionChart').getContext('2d');
    const riskDistributionChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['Bajo', 'Moderado', 'Alto'], 
            datasets: [{
                label: 'Nivel de Riesgo',
                data: <?php echo json_encode($chart_data_risk); ?>,
                backgroundColor: ['#34d399', '#f59e0b', '#ef4444'], // Verde, Amarillo, Rojo
                borderColor: isDarkMode() ? '#101622' : '#fff', // Color del fondo del dashboard/tarjeta
                borderWidth: 8, // Borde más grueso para separar segmentos
                hoverOffset: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            cutout: '70%', // Tamaño del agujero central
            plugins: {
                legend: { display: false }, // La leyenda es externa (en HTML)
                tooltip: { 
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed + '%';
                            }
                            return label;
                        }
                    },
                    backgroundColor: chartTooltipBg,
                    titleColor: chartTooltipTitleColor,
                    bodyColor: chartTooltipBodyColor,
                    borderColor: isDarkMode() ? '#475569' : '#ddd',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            }
        }
    });
    
    // --- Chart 3: Niveles por Facultad (Gráfico de Barras Agrupadas) ---
    const ctx3 = document.getElementById('facultyLevelsChart').getContext('2d');
    const facultyLevelsChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chart_labels_faculty); ?>,
            datasets: [{
                label: 'Ansiedad',
                data: <?php echo json_encode($chart_data_faculty_anxiety); ?>,
                backgroundColor: anxietyColor,
                borderRadius: 4,
            }, {
                label: 'Estrés',
                data: <?php echo json_encode($chart_data_faculty_stress); ?>,
                backgroundColor: stressColor,
                borderRadius: 4,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    display: true, // Muestra la leyenda para identificar las barras
                    labels: {
                        color: chartTicksColor,
                        font: { size: 14 }
                    }
                },
                tooltip: { 
                    backgroundColor: chartTooltipBg,
                    titleColor: chartTooltipTitleColor,
                    bodyColor: chartTooltipBodyColor,
                    borderColor: isDarkMode() ? '#475569' : '#ddd',
                    borderWidth: 1,
                    cornerRadius: 8,
                    padding: 12,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: chartTicksColor }
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: chartGridColor },
                    ticks: { color: chartTicksColor, stepSize: 10 } 
                }
            }
        }
    });

    // --- Lógica de Dark Mode para actualizar gráficos automáticamente ---
    const updateChartColors = () => {
        const newGridColor = chartGridColor();
        const newTicksColor = chartTicksColor();
        const tooltipBg = chartTooltipBg();
        const tooltipTitleColor = chartTooltipTitleColor();
        const tooltipBodyColor = chartTooltipBodyColor();
        
        // Función auxiliar para actualizar los ejes de un gráfico
        const updateScales = (chart) => {
            chart.options.scales.x.grid.color = newGridColor;
            chart.options.scales.x.ticks.color = newTicksColor;
            chart.options.scales.y.grid.color = newGridColor;
            chart.options.scales.y.ticks.color = newTicksColor;
            chart.options.plugins.legend.labels.color = newTicksColor; // Actualiza el color del texto de la leyenda
        };

        // 1. Gráfico de Evolución Temporal
        updateScales(temporalEvolutionChart);
        temporalEvolutionChart.options.plugins.tooltip.backgroundColor = tooltipBg;
        temporalEvolutionChart.options.plugins.tooltip.titleColor = tooltipTitleColor;
        temporalEvolutionChart.options.plugins.tooltip.bodyColor = tooltipBodyColor;

        // 2. Gráfico de Riesgo
        riskDistributionChart.data.datasets[0].borderColor = isDarkMode() ? '#101622' : '#fff';
        riskDistributionChart.options.plugins.tooltip.backgroundColor = tooltipBg;
        riskDistributionChart.options.plugins.tooltip.titleColor = tooltipTitleColor;
        riskDistributionChart.options.plugins.tooltip.bodyColor = tooltipBodyColor;
        
        // 3. Gráfico por Facultad
        updateScales(facultyLevelsChart);
        facultyLevelsChart.options.plugins.tooltip.backgroundColor = tooltipBg;
        facultyLevelsChart.options.plugins.tooltip.titleColor = tooltipTitleColor;
        facultyLevelsChart.options.plugins.tooltip.bodyColor = tooltipBodyColor;

        temporalEvolutionChart.update();
        riskDistributionChart.update();
        facultyLevelsChart.update();
    }

    // Observar cambios en el atributo 'class' del elemento <html> (para detectar el cambio de 'dark')
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                updateChartColors();
            }
        }
    });

    // Iniciar la observación en el elemento <html>
    observer.observe(document.documentElement, { attributes: true });

    // Actualizar colores una vez al inicio por si el tema ya es oscuro
    window.onload = updateChartColors; 
</script>