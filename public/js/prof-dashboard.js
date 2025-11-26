/**
 * prof-dashboard.js
 * Maneja la lógica del panel del profesor: Modal de sugerencias y carga de datos.
 */

document.addEventListener('DOMContentLoaded', function() {
    // --- Referencias al DOM ---
    const modal = document.getElementById('suggestModal');
    const closeBtn = document.getElementById('closeSuggestModal');
    const suggestForm = document.getElementById('suggestForm');
    const courseSelect = document.getElementById('selectCourse');
    const displayTestName = document.getElementById('displayTestName');
    const hiddenTestId = document.getElementById('hiddenTestId');
    const hiddenTestType = document.getElementById('hiddenTestType');
    const msgDiv = document.getElementById('suggestMsg');

    // --- 1. Cargar datos de Cursos (Consultados en BD por PHP) ---
    initCourseSelect();

    function initCourseSelect() {
        // Leemos los datos inyectados desde PHP
        const data = window.UnimindData || {};
        const courses = data.courses || [];

        // Limpiar select
        courseSelect.innerHTML = '';

        if (courses.length > 0) {
            // Opción por defecto
            const defaultOption = document.createElement('option');
            defaultOption.text = "-- Selecciona un curso --";
            defaultOption.value = "";
            defaultOption.disabled = true;
            defaultOption.selected = true;
            courseSelect.appendChild(defaultOption);

            // Rellenar con cursos de la BD
            courses.forEach(course => {
                const option = document.createElement('option');
                option.value = course.id_curso; // ID real de la BD
                option.textContent = course.nombre_curso;
                courseSelect.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.textContent = "No hay cursos asignados";
            courseSelect.disabled = true;
            courseSelect.appendChild(option);
        }
    }

    // --- 2. Manejar apertura del Modal ---
    const suggestButtons = document.querySelectorAll('.btn-suggest');
    suggestButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            // Obtener tipo de test del atributo data-test-type del botón
            // .closest asegura que funcione aunque demos clic al icono dentro del botón
            const button = e.target.closest('.btn-suggest');
            const type = button.getAttribute('data-test-type'); // 'estres' o 'ansiedad'

            openModal(type);
        });
    });

    function openModal(type) {
        // Resetear mensajes previos
        msgDiv.style.display = 'none';
        msgDiv.className = 'modal-msg';

        // Obtener ID del test desde la configuración de PHP
        const testMap = window.UnimindData.tests || {};
        const testId = testMap[type];

        if (!testId) {
            alert("Error: El ID del test no se pudo cargar de la base de datos.");
            return;
        }

        // Configurar la UI del modal
        if (type === 'estres') {
            displayTestName.value = "Test de Estrés";
        } else {
            displayTestName.value = "Test de Ansiedad";
        }

        // Establecer valores ocultos para el envío
        hiddenTestId.value = testId;
        hiddenTestType.value = type;

        // Mostrar
        modal.style.display = 'block';
    }

    // --- 3. Cerrar Modal ---
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    };

    // --- 4. Manejar Envío del Formulario ---
    if (suggestForm) {
        suggestForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const courseId = courseSelect.value;
            const testId = hiddenTestId.value;

            if (!courseId) {
                showMsg('Por favor selecciona un curso.', 'error');
                return;
            }

            // Aquí iría la llamada real al backend (AJAX/Fetch)
            console.log("Enviando sugerencia...", { courseId, testId });
            
            // Simulación de éxito
            // Reemplaza esto con tu fetch('/api/sugerir', ...)
            showMsg('Sugerencia enviada correctamente.', 'success');
            
            setTimeout(() => {
                modal.style.display = 'none';
                suggestForm.reset();
                msgDiv.style.display = 'none';
            }, 2000);
        });
    }

    function showMsg(text, type) {
        msgDiv.textContent = text;
        msgDiv.className = 'modal-msg ' + (type === 'error' ? 'error' : 'success');
        msgDiv.style.display = 'block';
    }
});