<main class="dashboard-container font-display bg-background-light dark:bg-background-dark p-8 md:p-10" id="dashboard-profesor">

    <div class="flex flex-col md:flex-row justify-between ...">
        </div>
    <script>
        // ... (script del selector de curso igual) ...
    </script>

    <div class="flex flex-col gap-6 mb-8">
        <div class="flex flex-col">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Recomendaciones</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Recursos y estrategias para apoyar a tus estudiantes</p>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            
            <form method="POST" action="dashboard-profesor.php" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-md flex flex-col transition duration-300 hover:shadow-lg">
                <input type="hidden" name="sugerir_test" value="1">
                <input type="hidden" name="id_test" value="1">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso_seleccionado; ?>">
                
                <div class="p-6 flex-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 flex-shrink-0 rounded-lg flex items-center justify-center bg-sky-50 dark:bg-sky-900/50">
                            <span class="material-symbols-outlined text-sky-600 dark:text-sky-400 text-2xl">psychology_alt</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-red-800 dark:text-red-400 text-lg">Sugerir test de estrés</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Medir estrés de aula</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" <?php echo !$id_curso_seleccionado ? 'disabled' : ''; ?> 
                        class="w-full text-right p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 rounded-b-xl text-sm font-medium text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 flex items-center justify-end gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    Ver Más
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                </button>
            </form>
            
            <form method="POST" action="dashboard-profesor.php" class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-md flex flex-col transition duration-300 hover:shadow-lg">
                <input type="hidden" name="sugerir_test" value="1">
                <input type="hidden" name="id_test" value="2">
                <input type="hidden" name="id_curso" value="<?php echo $id_curso_seleccionado; ?>">

                <div class="p-6 flex-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 flex-shrink-0 rounded-lg flex items-center justify-center bg-pink-50 dark:bg-pink-900/50">
                            <span class="material-symbols-outlined text-pink-600 dark:text-pink-400 text-2xl">monitor_heart</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-red-800 dark:text-red-400 text-lg">Sugerir test de ansiedad</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Medir ansiedad de aula</p>
                        </div>
                    </div>
                </div>
                
                <button type="submit" <?php echo !$id_curso_seleccionado ? 'disabled' : ''; ?> 
                        class="w-full text-right p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 rounded-b-xl text-sm font-medium text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 flex items-center justify-end gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    Ver Más
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                </button>
            </form>
            
            <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-md flex flex-col transition duration-300 hover:shadow-lg">
                <div class="p-6 flex-1">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 flex-shrink-0 rounded-lg flex items-center justify-center bg-red-50 dark:bg-red-900/50">
                            <span class="material-symbols-outlined text-red-600 dark:text-red-400 text-2xl">warning</span>
                        </div>
                        <div class="flex flex-col">
                            <h3 class="font-semibold text-red-800 dark:text-red-400 text-lg">Niveles altos</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Requieren atención</p>
                        </div>
                    </div>
                    <div class="text-center my-4">
                        <p class="text-6xl font-bold text-red-600 dark:text-red-500">
                            <?php echo $conteo_niveles_altos; ?>
                        </p>
                        <p class="text-lg font-medium text-gray-700 dark:text-gray-300">Estudiantes</p>
                    </div>
                </div>
                
                <a href="reporte-niveles-altos.php?id_curso=<?php echo $id_curso_seleccionado; ?>" 
                   class="w-full text-right p-4 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 rounded-b-xl text-sm font-medium text-gray-700 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 flex items-center justify-end gap-2 <?php echo !$id_curso_seleccionado ? 'disabled-link' : ''; ?>">
                    Ver Más
                    <span class="material-symbols-outlined text-base">open_in_new</span>
                </a>
            </div>
        </div>
    </div>
    
    </main>

<script>
    // ... (Todo el script de Chart.js y Dark Mode va aquí, sin cambios) ...
</script>

<style>
    a.disabled-link {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }
</style>