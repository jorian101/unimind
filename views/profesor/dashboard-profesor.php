<?php
require_once dirname(__DIR__) . '/pageHeader.php';

// Datos de ejemplo para los gráficos (en un entorno real, estos provendrían de una base de datos)
$chart_data_temporal_stress = [30, 35, 45, 50, 40, 60];
$chart_data_temporal_anxiety = [20, 28, 38, 42, 35, 50];
$chart_labels_temporal = ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4', 'Sem 5', 'Sem 6'];

$chart_data_risk = [19, 45, 36]; // Bajo, Moderado, Alto
$chart_labels_risk = ['Bajo (0-3): 19%', 'Moderado (4-6): 45%', 'Alto (7-10): 36%'];

$chart_data_faculty_stress = [65, 59, 80, 81, 56];
$chart_data_faculty_anxiety = [55, 48, 70, 65, 45];
$chart_labels_faculty = ['Ingeniería', 'Medicina', 'Derecho', 'Artes', 'Economía'];

// Colores de la imagen para Ansiedad (#f472b6) y Estrés (#3b82f6)
$color_stress = '#3b82f6'; 
$color_anxiety = '#f472b6'; 

renderPageHeader('Panel del Docente', ['UniMind Profesor', 'Dashboard']);
?>

<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="views/profesor/dashboard-profesor.css?v=<?php echo time(); ?>">

<script>
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

<main class="dashboard-container font-display bg-background-light dark:bg-background-dark" id="dashboard-profesor">

            <div class="flex flex-col gap-6 mb-8">
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recomendaciones</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Recursos y estrategias para apoyar a tus estudiantes</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4">
                        <div class="flex items-start gap-4">
                            <div class="bg-sky-100 dark:bg-sky-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-sky-600 dark:text-sky-400">psychology_alt</span></div>
                            <div class="flex flex-col flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Sugerir test de estrés</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Medir estrés de aula</p>
                            </div>
                        </div>
                        <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end">Ver Más <span class="material-symbols-outlined text-base">open_in_new</span></button>
                    </div>
                    
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4">
                        <div class="flex items-start gap-4">
                            <div class="bg-pink-100 dark:bg-pink-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-pink-600 dark:text-pink-400">monitor_heart</span></div>
                            <div class="flex flex-col flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Sugerir test de ansiedad</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Medir ansiedad de aula</p>
                            </div>
                        </div>
                        <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end">Ver Más <span class="material-symbols-outlined text-base">open_in_new</span></button>
                    </div>
                    
                    <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col gap-4">
                        <div class="flex items-start gap-4">
                            <div class="bg-red-100 dark:bg-red-900/50 p-3 rounded-lg"><span class="material-symbols-outlined text-red-600 dark:text-red-400">warning</span></div>
                            <div class="flex flex-col flex-1">
                                <h3 class="font-semibold text-gray-900 dark:text-white">Niveles altos</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Requieren atención</p>
                            </div>
                        </div>
                        <button class="mt-auto flex items-center gap-2 text-primary font-medium text-sm self-end">Ver Más <span class="material-symbols-outlined text-base">open_in_new</span></button>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                
                <div class="lg:col-span-2 rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Evolución Temporal</h3>
                    <div class="h-80 flex-1 relative">
                        <div class="absolute top-0 right-0 flex flex-col gap-1 text-xs">
                            <span class="text-gray-900 dark:text-white">Estrés: 60</span>
                            <span class="text-gray-900 dark:text-white">Ansiedad: 45</span>
                        </div>
                        <canvas id="temporalEvolutionChart"></canvas>
                    </div>
                </div>
                
                <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900 flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Distribución por Nivel de Riesgo</h3>
                    <div class="h-80 flex-1 relative flex items-center justify-center">
                        <canvas id="riskDistributionChart"></canvas>
                        <div class="absolute right-0 top-1/2 transform -translate-y-1/2 flex flex-col gap-2 text-sm text-gray-900 dark:text-white">
                            <?php foreach ($chart_labels_risk as $label): ?>
                                <p class="text-gray-700 dark:text-gray-300"><?php echo $label; ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-6 bg-white dark:bg-gray-900">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Niveles por Facultad</h3>
                <div class="h-80"><canvas id="facultyLevelsChart"></canvas></div>
            </div>
</main>

<script>
    // Variables y colores dinámicos de PHP
    const stressColor = '<?php echo $color_stress; ?>';
    const anxietyColor = '<?php echo $color_anxiety; ?>';

    const isDarkMode = () => document.documentElement.classList.contains('dark');
    const chartGridColor = () => isDarkMode() ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    const chartTicksColor = () => isDarkMode() ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.7)';
    
    // --- Chart 1: Temporal Evolution (Area Chart) ---
    const ctx1 = document.getElementById('temporalEvolutionChart').getContext('2d');
    const gradientStress = ctx1.createLinearGradient(0, 0, 0, 400);
    gradientStress.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
    gradientStress.addColorStop(1, 'rgba(59, 130, 246, 0)');
    const gradientAnxiety = ctx1.createLinearGradient(0, 0, 0, 400);
    gradientAnxiety.addColorStop(0, 'rgba(244, 114, 182, 0.5)');
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
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }, {
                label: 'Ansiedad',
                data: <?php echo json_encode($chart_data_temporal_anxiety); ?>,
                backgroundColor: gradientAnxiety,
                borderColor: anxietyColor,
                tension: 0.4,
                fill: true,
                pointRadius: 4,
                pointHoverRadius: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { 
                    backgroundColor: isDarkMode() ? '#334155' : '#fff',
                    titleColor: isDarkMode() ? '#fff' : '#333',
                    bodyColor: isDarkMode() ? '#cbd5e1' : '#666',
                    borderColor: isDarkMode() ? '#475569' : '#ddd',
                    borderWidth: 1,
                }
            },
            scales: {
                x: {
                    grid: { color: chartGridColor() },
                    ticks: { color: chartTicksColor() }
                },
                y: {
                    beginAtZero: true,
                    max: 80, // Ajuste para que coincida con el rango de la imagen
                    grid: { color: chartGridColor() },
                    ticks: { color: chartTicksColor(), stepSize: 20 }
                }
            }
        }
    });

    // --- Chart 2: Risk Distribution (Doughnut Chart) ---
    const ctx2 = document.getElementById('riskDistributionChart').getContext('2d');
    const riskDistributionChart = new Chart(ctx2, {
        type: 'doughnut',
        data: {
            // No usamos labels para el gráfico, sino para la leyenda externa
            labels: ['Bajo', 'Moderado', 'Alto'], 
            datasets: [{
                label: 'Nivel de Riesgo',
                data: <?php echo json_encode($chart_data_risk); ?>,
                backgroundColor: ['#34d399', '#f59e0b', '#ef4444'], // Verde, Amarillo, Rojo
                borderColor: isDarkMode() ? '#111827' : '#fff',
                borderWidth: 4,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }, // Ocultamos la leyenda para usar la externa HTML
            }
        }
    });
    
    // --- Chart 3: Faculty Levels (Grouped Bar Chart) ---
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
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: chartTicksColor() }
                },
                y: {
                    beginAtZero: true,
                    max: 100,
                    grid: { color: chartGridColor() },
                    ticks: { color: chartTicksColor(), stepSize: 10 } // Ajuste para que coincida con la imagen (0, 3, 6, 10...)
                }
            }
        }
    });

    // Código para actualizar los gráficos en cambio de tema (Dark Mode)
    const observer = new MutationObserver((mutationsList) => {
        for (const mutation of mutationsList) {
            if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                const newGridColor = chartGridColor();
                const newTicksColor = chartTicksColor();
                
                [temporalEvolutionChart, facultyLevelsChart].forEach(chart => {
                    // Actualizar colores de rejilla y ticks
                    chart.options.scales.x.grid.color = newGridColor;
                    chart.options.scales.x.ticks.color = newTicksColor;
                    chart.options.scales.y.grid.color = newGridColor;
                    chart.options.scales.y.ticks.color = newTicksColor;
                    // Actualizar color de fondo del tooltip
                    chart.options.plugins.tooltip.backgroundColor = isDarkMode() ? '#334155' : '#fff';
                    chart.options.plugins.tooltip.titleColor = isDarkMode() ? '#fff' : '#333';
                    chart.options.plugins.tooltip.bodyColor = isDarkMode() ? '#cbd5e1' : '#666';
                });
                
                riskDistributionChart.data.datasets[0].borderColor = isDarkMode() ? '#111827' : '#fff';
                
        temporalEvolutionChart.update();
        riskDistributionChart.update();
        facultyLevelsChart.update();
    }
}
});
observer.observe(document.documentElement, { attributes: true });
</script>