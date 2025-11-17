// Variable global para almacenar el ID del curso cargado
let currentCursoId = null;

// Instancias de los gráficos (para poder destruirlos)
let temporalChart, riskChart, facultyChart;

// --- Colores y Helpers (Valores estáticos para modo claro) ---
const stressColor = "#3b82f6";
const anxietyColor = "#f472b6";
const chartGridColor = "rgba(0, 0, 0, 0.1)";
const chartTicksColor = "rgba(0, 0, 0, 0.7)";
const chartTooltipBg = "#fff";
const chartTooltipTitleColor = "#1f2937";
const chartTooltipBodyColor = "#4b5563";

// --- Funciones Principales ---

/**
 * Función principal que se ejecuta al cargar la página
 */
document.addEventListener("DOMContentLoaded", () => {
  // 1. Cargar los datos iniciales
  fetchDashboardData();

  // 2. Asignar Event Listeners
  document
    .getElementById("form-sugerir-estres")
    .addEventListener("submit", handleSugerirTest);
  document
    .getElementById("form-sugerir-ansiedad")
    .addEventListener("submit", handleSugerirTest);
});

/**
 * Maneja el submit de los formularios "Sugerir Test"
 */
async function handleSugerirTest(e) {
  e.preventDefault();
  const form = e.target;
  const idTest = form.id === "form-sugerir-estres" ? 1 : 2;

  // El ID del curso se toma de la variable global 'currentCursoId'
  const data = {
    id_test: idTest,
    id_curso: currentCursoId,
  };

  try {
    const response = await fetch("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(data),
    });
    const result = await response.json();

    if (result.success) {
      alert("Test sugerido correctamente a los alumnos.");
    } else {
      alert("Error al sugerir el test.");
    }
  } catch (error) {
    console.error("Error en la solicitud POST:", error);
    alert("Error de conexión al sugerir el test.");
  }
}

/**
 * Llama a la API para obtener todos los datos del dashboard
 */
async function fetchDashboardData() {
  // Ya no se necesita un ID de curso, la API lo determina
  const url = "api.php";

  try {
    const response = await fetch(url);
    if (!response.ok) throw new Error("Error en la respuesta de la API");

    const data = await response.json();

    // 1. Almacenar estado global
    currentCursoId = data.id_curso_seleccionado;

    // 2. Actualizar el UI
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

// --- Funciones de Actualización de UI ---

/**
 * Actualiza las tarjetas (conteo, links, botones)
 */
function updateCards(data) {
  // Subtítulo
  document.getElementById("subtitulo-curso").innerHTML =
    `Mostrando reportes para: <span class="subtitle-highlight">${data.nombre_curso_seleccionado}</span>`;

  // Conteo Niveles Altos
  document.getElementById("conteo-niveles-altos").textContent =
    data.conteo_niveles_altos;

  // Link Niveles Altos
  const linkAltos = document.getElementById("link-niveles-altos");
  linkAltos.href = `reporte-niveles-altos.php?id_curso=${currentCursoId}`;

  // Habilitar/Deshabilitar controles
  const controls = [
    document.getElementById("btn-sugerir-estres"),
    document.getElementById("btn-sugerir-ansiedad"),
    linkAltos,
  ];

  // Si no hay curso (ID es 0 o null), deshabilitar
  if (currentCursoId > 0) {
    controls.forEach((ctrl) => ctrl.classList.remove("disabled-control"));
  } else {
    controls.forEach((ctrl) => ctrl.classList.add("disabled-control"));
  }
}

// --- Funciones de Gráficos ---

/**
 * Crea o actualiza el gráfico de Evolución Temporal
 */
function updateTemporalChart(data) {
  const ctx = document
    .getElementById("temporalEvolutionChart")
    .getContext("2d");

  if (temporalChart) temporalChart.destroy(); // Destruir el gráfico anterior

  // Actualizar stats
  const statsEl = document.getElementById("stats-temporal");
  statsEl.innerHTML = `
        <p>Estrés Reciente: <span style="color:${stressColor}; font-weight: 600;">${data.stress?.slice(-1)[0] ?? 0}</span></p>
        <p>Ansiedad Reciente: <span style="color:${anxietyColor}; font-weight: 600;">${data.anxiety?.slice(-1)[0] ?? 0}</span></p>
    `;

  // Gradientes
  const gradientStress = ctx.createLinearGradient(0, 0, 0, 400);
  gradientStress.addColorStop(0, "rgba(59, 130, 246, 0.5)");
  gradientStress.addColorStop(1, "rgba(59, 130, 246, 0)");
  const gradientAnxiety = ctx.createLinearGradient(0, 0, 0, 400);
  gradientAnxiety.addColorStop(0, "rgba(244, 114, 182, 0.5)");
  gradientAnxiety.addColorStop(1, "rgba(244, 114, 182, 0)");

  temporalChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: data.labels,
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
      responsive: true,
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

/**
 * Crea o actualiza el gráfico de Distribución de Riesgo
 */
function updateRiskChart(data) {
  const ctx = document.getElementById("riskDistributionChart").getContext("2d");
  if (riskChart) riskChart.destroy();

  // Actualizar leyenda HTML
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
      labels: data.labels_js,
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
      responsive: true,
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

/**
 * Crea o actualiza el gráfico de Comparativa de Facultades
 */
function updateFacultyChart(data) {
  const ctx = document.getElementById("facultyLevelsChart").getContext("2d");
  if (facultyChart) facultyChart.destroy();

  facultyChart = new Chart(ctx, {
    type: "bar",
    data: {
      labels: data.labels,
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
      responsive: true,
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
          grid: { display: false },
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
