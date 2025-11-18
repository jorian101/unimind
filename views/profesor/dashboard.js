// --- Variables Globales ---
let currentCursoId = null;
let globalData = {
  cursos: [],
  tests: [],
};
let currentTestIdToSuggest = null; // Almacena el ID (1 o 2) del test a sugerir

// Instancias de los gráficos
let temporalChart, riskChart, facultyChart;

// --- Colores y Helpers (Modo Claro) ---
const stressColor = "#3b82f6";
const anxietyColor = "#f472b6";
const chartGridColor = "rgba(0, 0, 0, 0.1)";
const chartTicksColor = "rgba(0, 0, 0, 0.7)";
const chartTooltipBg = "#fff";
const chartTooltipTitleColor = "#1f2937";
const chartTooltipBodyColor = "#4b5563";

// --- Funciones Principales ---

document.addEventListener("DOMContentLoaded", () => {
  // 1. Cargar los datos iniciales
  fetchDashboardData();

  // 2. Asignar Event Listeners a los botones de las TARJETAS
  document
    .getElementById("form-sugerir-estres")
    .addEventListener("submit", (e) => {
      e.preventDefault();
      openSugerirModal(1); // Abrir modal para Test ID 1
    });
  document
    .getElementById("form-sugerir-ansiedad")
    .addEventListener("submit", (e) => {
      e.preventDefault();
      openSugerirModal(2); // Abrir modal para Test ID 2
    });
  // Interceptar click en "Niveles Altos" para mostrar modal informativo en lugar de navegar
  const linkAltos = document.getElementById("link-niveles-altos");
  if (linkAltos) {
    linkAltos.addEventListener("click", (e) => {
      e.preventDefault();
      openInfoModal(
        "Niveles altos",
        "Estos niveles requieren atención y deben ser revisados por el docente. Revisa el reporte detallado o comunica con el equipo de soporte para seguimiento.",
      );
    });
  }

  // 3. Asignar Event Listeners a los botones del MODAL
  document
    .getElementById("modal-btn-cerrar")
    .addEventListener("click", closeSugerirModal);
  document
    .getElementById("modal-btn-cancelar")
    .addEventListener("click", closeSugerirModal);
  document
    .getElementById("modal-btn-sugerir")
    .addEventListener("click", handleModalSubmit);
});

/**
 * Llama a la API para obtener TODOS los datos iniciales
 */
async function fetchDashboardData() {
  const url = "api.php";

  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error("Error en la respuesta de la API");

    const data = await response.json();

    // 1. Almacenar datos globales
    currentCursoId = data.id_curso_seleccionado;
    globalData.cursos = data.lista_cursos;
    globalData.tests = data.tests_disponibles;

    // 2. Actualizar el UI (tarjetas, título)
    updateCards(data);

    // 3. Actualizar Gráficos
    updateTemporalChart(data.data_temporal);
    updateRiskChart(data.data_riesgo);
    updateFacultyChart(data.data_escuelas);
  } catch (error) {
    console.error("Error al cargar datos del dashboard:", error);
    document.getElementById("subtitulo-curso").innerText =
      "Error al cargar los datos.";
  }
}

/**
 * Maneja el envío del formulario del modal
 */
async function handleModalSubmit() {
  const id_test = currentTestIdToSuggest;
  const id_curso = document.getElementById("modal-form-curso-select").value;

  if (!id_test || !id_curso) {
    alert("Error: No se ha seleccionado un test o un curso.");
    return;
  }

  const data = { id_test: id_test, id_curso: id_curso };

  try {
    const response = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });
    const result = await response.json();

    if (result.success) {
      alert("Test sugerido correctamente al curso seleccionado.");
      closeSugerirModal();
    } else {
      alert("Error al sugerir el test.");
    }
  } catch (error) {
    console.error("Error en la solicitud POST:", error);
    alert("Error de conexión al sugerir el test.");
  }
}

// --- Funciones del Modal ---

function openSugerirModal(testId) {
  currentTestIdToSuggest = testId;

  // 1. Encontrar el test en los datos globales
  const test = globalData.tests
    ? globalData.tests.find((t) => t.id_test == testId)
    : null;
  if (!test) {
    // Si no hay datos disponibles, mostrar un modal informativo en lugar de fallar silenciosamente
    console.warn("No se encontraron datos para el test ID:", testId);
    openInfoModal(
      "Sugerir Test",
      "Información del test no disponible en este momento. Intenta recargar la página o verifica la conexión con el servidor.",
    );
    return;
  }

  // 2. Llenar los campos del formulario (solo lectura)
  document.getElementById("modal-title").textContent =
    `Sugerir: ${test.nombre}`;
  document.getElementById("modal-form-nombre").value = test.nombre;
  document.getElementById("modal-form-descripcion").value = test.descripcion;
  document.getElementById("modal-form-preguntas").value = test.num_items;

  // 3. Llenar el selector de cursos
  const selectCurso = document.getElementById("modal-form-curso-select");
  selectCurso.innerHTML = ""; // Limpiar opciones

  globalData.cursos.forEach((curso) => {
    const option = document.createElement("option");
    option.value = curso.id_curso;
    option.textContent = curso.nombre_curso;
    // Seleccionar por defecto el curso que ya está cargado en el dashboard
    if (curso.id_curso == currentCursoId) {
      option.selected = true;
    }
    selectCurso.appendChild(option);
  });

  // 4. Mostrar el modal
  document.getElementById("sugerir-test-modal").classList.add("visible");
}

function closeSugerirModal() {
  document.getElementById("sugerir-test-modal").classList.remove("visible");
  currentTestIdToSuggest = null; // Limpiar el ID
}

/**
 * Abre un modal informativo reutilizando la estructura del modal de sugerir
 */
function openInfoModal(title, description) {
  document.getElementById("modal-title").textContent = title;
  document.getElementById("modal-form-nombre").value = "";
  document.getElementById("modal-form-descripcion").value = description;
  document.getElementById("modal-form-preguntas").value = "";

  // Rellenar selector con el curso actual si existe
  const selectCurso = document.getElementById("modal-form-curso-select");
  selectCurso.innerHTML = "";
  if (currentCursoId && globalData.cursos && globalData.cursos.length) {
    const curso =
      globalData.cursos.find((c) => c.id_curso == currentCursoId) ||
      globalData.cursos[0];
    const option = document.createElement("option");
    option.value = curso.id_curso;
    option.textContent = curso.nombre_curso || "Curso";
    option.selected = true;
    selectCurso.appendChild(option);
  } else {
    const option = document.createElement("option");
    option.value = "";
    option.textContent = "No disponible";
    selectCurso.appendChild(option);
  }

  document.getElementById("sugerir-test-modal").classList.add("visible");
}

// --- Funciones de Actualización de UI (Tarjetas) ---

function updateCards(data) {
  // Subtítulo
  document.getElementById("subtitulo-curso").innerHTML =
    `Mostrando reportes para: <span class="subtitle-highlight">${data.nombre_curso_seleccionado}</span>`;

  // Link Niveles Altos
  const linkAltos = document.getElementById("link-niveles-altos");
  linkAltos.href = `reporte-niveles-altos.php?id_curso=${currentCursoId}`;

  // Habilitar/Deshabilitar controles
  const controls = [
    document.getElementById("btn-sugerir-estres"),
    document.getElementById("btn-sugerir-ansiedad"),
    linkAltos,
  ];

  if (currentCursoId > 0) {
    controls.forEach((ctrl) => ctrl.classList.remove("disabled-control"));
  } else {
    controls.forEach((ctrl) => ctrl.classList.add("disabled-control"));
  }
}

// --- Funciones de Gráficos (Sin cambios) ---

function updateTemporalChart(data) {
  const ctx = document
    .getElementById("temporalEvolutionChart")
    .getContext("2d");
  if (temporalChart) temporalChart.destroy();

  const statsEl = document.getElementById("stats-temporal");
  statsEl.innerHTML = `
        <p>Estrés Reciente: <span style="color:${stressColor}; font-weight: 600;">${data.stress?.slice(-1)[0] ?? 0}</span></p>
        <p>Ansiedad Reciente: <span style="color:${anxietyColor}; font-weight: 600;">${data.anxiety?.slice(-1)[0] ?? 0}</span></p>
    `;

  const gradientStress = ctx.createLinearGradient(0, 0, 0, 400);
  gradientStress.addColorStop(0, "rgba(59, 130, 246, 0.5)");
  gradientStress.addColorStop(1, "rgba(59, 130, 246, 0)");
  const gradientAnxiety = ctx.createLinearGradient(0, 0, 0, 400);
  gradientAnxiety.addColorStop(0, "rgba(244, 114, 182, 0.5)");
  gradientAnxiety.addColorStop(1, "rgba(244, 114, 182, 0)");

  temporalChart = new Chart(ctx, {
    type: "line",
    data: {
      /* ... (datos sin cambios) ... */ labels: data.labels,
      datasets: [
        {
          label: "Estrés",
          data: data.stress,
          backgroundColor: gradientStress,
          borderColor: stressColor,
          tension: 0.4,
          fill: true,
          pointRadius: 5,
          pointBackgroundColor: stressColor,
          pointHoverRadius: 7,
          borderWidth: 3,
        },
        {
          label: "Ansiedad",
          data: data.anxiety,
          backgroundColor: gradientAnxiety,
          borderColor: anxietyColor,
          tension: 0.4,
          fill: true,
          pointRadius: 5,
          pointBackgroundColor: anxietyColor,
          pointHoverRadius: 7,
          borderWidth: 3,
        },
      ],
    },
    options: {
      /* ... (opciones sin cambios) ... */ responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          labels: { color: chartTicksColor, font: { size: 14 } },
        },
        tooltip: {
          backgroundColor: chartTooltipBg,
          titleColor: chartTooltipTitleColor,
          bodyColor: chartTooltipBodyColor,
          borderColor: "#ddd",
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        },
      },
      scales: {
        x: {
          grid: { color: chartGridColor },
          ticks: { color: chartTicksColor },
        },
        y: {
          beginAtZero: true,
          grid: { color: chartGridColor },
          ticks: { color: chartTicksColor },
        },
      },
    },
  });
}

function updateRiskChart(data) {
  const ctx = document.getElementById("riskDistributionChart").getContext("2d");
  if (riskChart) riskChart.destroy();

  const leyendaEl = document.getElementById("leyenda-riesgo");
  leyendaEl.innerHTML = data.labels_html
    .map(
      (item) => `
        <p class="legend-item">
            <span class="legend-color-box" style="background-color: ${item.color}"></span>
            ${item.label}
        </p>
    `,
    )
    .join("");

  riskChart = new Chart(ctx, {
    type: "doughnut",
    data: {
      /* ... (datos sin cambios) ... */ labels: data.labels_js,
      datasets: [
        {
          label: "Nivel de Riesgo",
          data: data.data,
          backgroundColor: data.colors,
          borderColor: "#fff",
          borderWidth: 8,
          hoverOffset: 12,
        },
      ],
    },
    options: {
      /* ... (opciones sin cambios) ... */ responsive: true,
      maintainAspectRatio: true,
      cutout: "70%",
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function (context) {
              return `${context.label}: ${context.parsed}`;
            },
          },
          backgroundColor: chartTooltipBg,
          titleColor: chartTooltipTitleColor,
          bodyColor: chartTooltipBodyColor,
          borderColor: "#ddd",
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        },
      },
    },
  });
}

function updateFacultyChart(data) {
  const ctx = document.getElementById("facultyLevelsChart").getContext("2d");
  if (facultyChart) facultyChart.destroy();

  facultyChart = new Chart(ctx, {
    type: "bar",
    data: {
      /* ... (datos sin cambios) ... */ labels: data.labels,
      datasets: [
        {
          label: "Ansiedad",
          data: data.anxiety,
          backgroundColor: anxietyColor,
          borderRadius: 4,
        },
        {
          label: "Estrés",
          data: data.stress,
          backgroundColor: stressColor,
          borderRadius: 4,
        },
      ],
    },
    options: {
      /* ... (opciones sin cambios) ... */ responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          labels: { color: chartTicksColor, font: { size: 14 } },
        },
        tooltip: {
          backgroundColor: chartTooltipBg,
          titleColor: chartTooltipTitleColor,
          bodyColor: chartTooltipBodyColor,
          borderColor: "#ddd",
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
        },
      },
      scales: {
        x: { grid: { display: false }, ticks: { color: chartTicksColor } },
        y: {
          beginAtZero: true,
          grid: { color: chartGridColor },
          ticks: { color: chartTicksColor },
        },
      },
    },
  });
}
