/**
 * prof-dashboard.js
 * Maneja la lógica del panel del profesor: Modal de sugerencias y carga de datos.
 */

document.addEventListener("DOMContentLoaded", function () {
  // --- Referencias al DOM ---
  const modal = document.getElementById("suggestModal");
  const closeBtn = document.getElementById("closeSuggestModal");
  const suggestForm = document.getElementById("suggestForm");
  const courseSelect = document.getElementById("selectCourse");
  const displayTestName = document.getElementById("displayTestName");
  const hiddenTestId = document.getElementById("hiddenTestId");
  const hiddenTestType = document.getElementById("hiddenTestType");
  const msgDiv = document.getElementById("suggestMsg");

  // --- 1. Cargar datos de Cursos (Consultados en BD por PHP) ---
  initCourseSelect();

  function initCourseSelect() {
    // Leemos los datos inyectados desde PHP
    const data = window.UnimindData || {};
    const courses = data.courses || [];

    // Limpiar select
    courseSelect.innerHTML = "";

    if (courses.length > 0) {
      // Opción por defecto
      const defaultOption = document.createElement("option");
      defaultOption.text = "-- Selecciona un curso --";
      defaultOption.value = "";
      defaultOption.disabled = true;
      defaultOption.selected = true;
      courseSelect.appendChild(defaultOption);

      // Rellenar con cursos de la BD
      courses.forEach((course) => {
        const option = document.createElement("option");
        option.value = course.id_curso; // ID real de la BD
        option.textContent = course.nombre_curso;
        courseSelect.appendChild(option);
      });
    } else {
      const option = document.createElement("option");
      option.textContent = "No hay cursos asignados";
      courseSelect.disabled = true;
      courseSelect.appendChild(option);
    }
  }

  // --- 2. Manejar apertura del Modal ---
  const suggestButtons = document.querySelectorAll(".btn-suggest");
  suggestButtons.forEach((btn) => {
    btn.addEventListener("click", function (e) {
      // Obtener tipo de test del atributo data-test-type del botón
      // .closest asegura que funcione aunque demos clic al icono dentro del botón
      const button = e.target.closest(".btn-suggest");
      const type = button.getAttribute("data-test-type"); // 'estres' o 'ansiedad'

      openModal(type);
    });
  });

  function openModal(type) {
    msgDiv.style.display = "none";
    msgDiv.className = "modal-msg";

    const testMap = window.UnimindData.tests || {};
    const testId = testMap[type];

    if (!testId) {
      showMsg("No se pudo cargar el test seleccionado.", "error");
      return;
    }

    displayTestName.value =
      type === "estres" ? "Test de Estrés" : "Test de Ansiedad";
    hiddenTestId.value = testId;
    hiddenTestType.value = type;

    // show modal (use flex to center)
    if (modal) modal.style.display = "flex";
    document.body.classList.add("no-scroll");
    courseSelect.focus();
    document.addEventListener("keydown", onEscClose);
  }

  // --- 3. Cerrar Modal ---
  function closeModal() {
    if (modal) modal.style.display = "none";
    document.body.classList.remove("no-scroll");
    document.removeEventListener("keydown", onEscClose);
  }

  function onEscClose(e) {
    if (e.key === "Escape") closeModal();
  }

  if (closeBtn) closeBtn.onclick = closeModal;

  modal.addEventListener("click", function (event) {
    if (event.target === modal) closeModal();
  });

  // Cancel button inside modal (new in markup)
  const cancelBtn = document.getElementById("cancelSuggest");
  if (cancelBtn)
    cancelBtn.addEventListener("click", function () {
      closeModal();
    });

  // --- 4. Manejar Envío del Formulario ---
  if (suggestForm) {
    suggestForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const courseId = courseSelect.value;
      const testId = hiddenTestId.value;
      const testType = hiddenTestType.value;

      if (!courseId) {
        showMsg("Por favor selecciona un curso.", "error");
        return;
      }

      // Add loading state to submit button
      const submitBtn = suggestForm.querySelector('button[type="submit"]');
      let spinnerEl = null;
      if (submitBtn) {
        submitBtn.classList.add("loading");
        submitBtn.disabled = true;
        // create spinner
        spinnerEl = document.createElement("span");
        spinnerEl.className = "loading-spinner";
        submitBtn.appendChild(spinnerEl);
      }

      try {
        const payload = {
          id_curso: Number(courseId),
          id_test: Number(testId),
          test_type: testType,
        };
        const resp = await fetch("/api/suggest_test.php", {
          method: "POST",
          headers: { "Content-Type": "application/json; charset=utf-8" },
          body: JSON.stringify(payload),
          credentials: "same-origin",
        });
        const json = await resp
          .json()
          .catch(() => ({
            success: false,
            message: "Respuesta inválida del servidor.",
          }));

        if (resp.ok && json.success) {
          showMsg(
            json.message || "Sugerencia enviada correctamente.",
            "success",
          );
          setTimeout(() => {
            closeModal();
            suggestForm.reset();
            msgDiv.style.display = "none";
          }, 1200);
        } else {
          showMsg(json.message || "No se pudo enviar la sugerencia.", "error");
        }
      } catch (err) {
        showMsg("Error de red: " + (err?.message || err), "error");
      } finally {
        // remove loading state
        if (submitBtn) {
          submitBtn.classList.remove("loading");
          submitBtn.disabled = false;
          if (spinnerEl && spinnerEl.parentNode)
            spinnerEl.parentNode.removeChild(spinnerEl);
        }
      }
    });
  }

  function showMsg(text, type) {
    msgDiv.textContent = text;
    msgDiv.className = "modal-msg " + (type === "error" ? "error" : "success");
    msgDiv.style.display = "block";
  }
});
