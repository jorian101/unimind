document.addEventListener("DOMContentLoaded", function () {
  // Cargar datos desde window.UnimindData (patrón MVC)
  cargarDatosDashboard();

  // Función auxiliar para obtener nivel legible
  function getNivelTexto(nivel) {
    const niveles = {
      normal: "Normal",
      leve: "Leve",
      moderado: "Moderado",
      alto: "Alto",
      severo: "Severo",
    };
    return niveles[nivel] || nivel;
  }

  // Función auxiliar para obtener color según nivel
  function getColorNivel(nivel) {
    const colores = {
      normal: "#28a745",
      leve: "#ffc107",
      moderado: "#fd7e14",
      alto: "#dc3545",
      severo: "#6f1e23",
    };
    return colores[nivel] || "#6c757d";
  }

  // Función para cargar datos del dashboard desde window.UnimindData (patrón MVC)
  function cargarDatosDashboard() {
    const container = document.getElementById("dashboard");

    if (!container) {
      return;
    }

    try {
      // Verificar que existan los datos en window.UnimindData
      if (!window.UnimindData || !window.UnimindData.dashboard) {
        throw new Error("No se encontraron datos del dashboard");
      }

      const data = window.UnimindData.dashboard;
      renderDashboard(data);
    } catch (error) {
      // Marcar 'error' como usado para evitar eslint no-unused-vars
      void error;
      // Error al cargar dashboard
      container.innerHTML = `
          <div class="error-message">
            <i class="fas fa-exclamation-circle"></i>
            <p>Error al cargar el dashboard. Por favor, intenta de nuevo.</p>
          </div>
        `;
    }
  }

  // Render del dashboard con datos reales
  function renderDashboard(data) {
    const container = document.getElementById("dashboard");

    if (!container) {
      return;
    }

    const estres = data.estres || null;
    const ansiedad = data.ansiedad || null;
    const global = data.global || {};
    const riesgo = data.riesgo_emergente || {};

    // Valores por defecto
    const estresPercent = estres ? parseFloat(estres.porcentaje_score) || 0 : 0;
    const ansiedadPercent = ansiedad
      ? parseFloat(ansiedad.porcentaje_score) || 0
      : 0;
    const estresNivel = estres ? estres.nivel_calculado || "normal" : "normal";
    const ansiedadNivel = ansiedad
      ? ansiedad.nivel_calculado || "normal"
      : "normal";
    const totalTests = global.total_tests || 0;
    const diasUltimo = global.dias_ultimo_test || "N/A";
    const estadoGeneral = global.estado_general || "Sin datos";
    const requiereAtencion = global.requiere_atencion || false;

    // Calcular cambio y tendencia de estrés
    const cambioEstres = estres ? parseFloat(estres.cambio_pct) || 0 : 0;
    const iconoEstres = cambioEstres < 0 ? "↓" : cambioEstres > 0 ? "↑" : "→";
    const colorCambioEstres =
      cambioEstres < 0 ? "#28a745" : cambioEstres > 0 ? "#dc3545" : "#6c757d";

    // Calcular cambio y tendencia de ansiedad
    const cambioAnsiedad = ansiedad ? parseFloat(ansiedad.cambio_pct) || 0 : 0;
    const iconoAnsiedad =
      cambioAnsiedad < 0 ? "↓" : cambioAnsiedad > 0 ? "↑" : "→";
    const colorCambioAnsiedad =
      cambioAnsiedad < 0
        ? "#28a745"
        : cambioAnsiedad > 0
          ? "#dc3545"
          : "#6c757d";

    // Alerta de riesgo emergente
    const alertaRiesgo = riesgo.tiene_riesgo
      ? `<div class="alerta-riesgo">⚠️ <strong>ATENCIÓN:</strong> Se ha detectado un cambio significativo en tus niveles. Te recomendamos consultar con un profesional.</div>`
      : "";

    container.innerHTML = `
            ${alertaRiesgo}
            
            <section class="cards-row">
                <div class="card card--stress">
                    <h3>Nivel de Estrés</h3>
                    ${estres ? `<small>Última medición ${estres.fecha_finalizacion ? "- " + new Date(estres.fecha_finalizacion).toLocaleDateString() : ""}</small>` : "<small>Sin datos disponibles</small>"}
                    <div class="percent">${estresPercent.toFixed(1)}%</div>
                    <div class="bar">
                        <div class="bar-fill" style="width:${estresPercent}%; background-color: ${getColorNivel(estresNivel)};"></div>
                    </div>
                    <div class="level">Nivel ${getNivelTexto(estresNivel).toLowerCase()}</div>
                    ${
                      estres && !estres.es_primera_aplicacion
                        ? `<div class="cambio" style="color: ${colorCambioEstres};">
                        ${iconoEstres} ${Math.abs(cambioEstres).toFixed(1)} puntos vs anterior
                    </div>`
                        : '<div class="cambio">Primera medición</div>'
                    }
                    ${
                      estres && estres.percentil_curso
                        ? `<small>Percentil en curso: ${parseFloat(estres.percentil_curso).toFixed(0)}%</small>`
                        : ""
                    }
                </div>

                <div class="card card--anxiety">
                    <h3>Nivel de Ansiedad</h3>
                    ${ansiedad ? `<small>Última medición ${ansiedad.fecha_finalizacion ? "- " + new Date(ansiedad.fecha_finalizacion).toLocaleDateString() : ""}</small>` : "<small>Sin datos disponibles</small>"}
                    <div class="percent">${ansiedadPercent.toFixed(1)}%</div>
                    <div class="bar">
                        <div class="bar-fill" style="width:${ansiedadPercent}%; background-color: ${getColorNivel(ansiedadNivel)};"></div>
                    </div>
                    <div class="level">Nivel ${getNivelTexto(ansiedadNivel).toLowerCase()}</div>
                    ${
                      ansiedad && !ansiedad.es_primera_aplicacion
                        ? `<div class="cambio" style="color: ${colorCambioAnsiedad};">
                        ${iconoAnsiedad} ${Math.abs(cambioAnsiedad).toFixed(1)} puntos vs anterior
                    </div>`
                        : '<div class="cambio">Primera medición</div>'
                    }
                    ${
                      ansiedad && ansiedad.percentil_curso
                        ? `<small>Percentil en curso: ${parseFloat(ansiedad.percentil_curso).toFixed(0)}%</small>`
                        : ""
                    }
                </div>
            </section>

            <section class="general">
                <div class="general-header">Estado General</div>
                <div class="general-sub">Resumen de tu bienestar emocional actual</div>

                <div class="general-body">
                    <div>
                        <strong>Estado Actual</strong><br>
                        <span>${estadoGeneral}</span>
                    </div>
                    ${requiereAtencion ? '<span class="status status-alerta">Requiere atención</span>' : '<span class="status">Normal</span>'}
                </div>

                <button class="dashboard-btn" onclick="realizarNuevoTest()">Realizar Nuevo Test</button>
            </section>

            <section class="metrics">
                <div class="metric">
                    <div class="value">${totalTests}</div>
                    <div class="label">Tests Realizados</div>
                </div>
                <div class="metric">
                    <div class="value">${global.total_tests_estres || 0} / ${global.total_tests_ansiedad || 0}</div>
                    <div class="label">Estrés / Ansiedad</div>
                </div>
                <div class="metric">
                    <div class="value">${diasUltimo !== null ? diasUltimo : "N/A"}</div>
                    <div class="label">Días desde Último Test</div>
                </div>
            </section>
        `;

    // Animar las barras de progreso después de renderizar
    setTimeout(() => {
      animateProgressBars();
    }, 100);
  }

  // Animar barras de progreso
  function animateProgressBars() {
    const barFills = document.querySelectorAll(".bar-fill");
    barFills.forEach((bar) => {
      const width = bar.style.width;
      bar.style.width = "0%";
      setTimeout(() => {
        bar.style.width = width;
      }, 100);
    });
  }

  // Función para el botón de nuevo test
  window.realizarNuevoTest = function () {
    // Guardar el estado actual del sidebar antes de navegar
    const currentCollapsed = document
      .getElementById("sidebar")
      .classList.contains("sidebar--collapsed");
    localStorage.setItem("sidebarCollapsed", currentCollapsed.toString());

    // Navegar a la página de tests
    window.location.href = "?role=estudiante&page=tests";
  };

  // Renderizar dashboard inmediatamente
  renderDashboard(datosUsuario);
});
