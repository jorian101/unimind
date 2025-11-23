document.addEventListener("DOMContentLoaded", function () {
  // Simulación de datos "desde base de datos"
  const datosUsuario = {
    estres: 65,
    ansiedad: 48,
    tendencia: "Mejorando",
    estadoGeneral: "Positivo",
    totalTests: 12,
    cambioMes: -15,
    diasUltimoTest: 3,
  };

  // Cálculo de nivel según porcentaje
  function calcularNivel(valor) {
    if (valor < 30) return "Bajo";
    if (valor < 70) return "Moderado";
    return "Alto";
  }

  // Render del dashboard
  function renderDashboard(data) {
    const container = document.getElementById("dashboard");

    if (!container) {
      return;
    }

    container.innerHTML = `
            <section class="cards-row">
                <div class="card card--stress">
                    <h3>Nivel de Estrés</h3>
                    <small>Última medición</small>
                    <div class="percent">${data.estres}%</div>
                    <div class="bar">
                        <div class="bar-fill" style="width:${data.estres}%;"></div>
                    </div>
                    <div class="level">Nivel ${calcularNivel(data.estres).toLowerCase()}</div>
                </div>

                <div class="card card--anxiety">
                    <h3>Nivel de Ansiedad</h3>
                    <small>Última medición</small>
                    <div class="percent">${data.ansiedad}%</div>
                    <div class="bar">
                        <div class="bar-fill" style="width:${data.ansiedad}%;"></div>
                    </div>
                    <div class="level">Nivel ${calcularNivel(data.ansiedad).toLowerCase()}</div>
                </div>
            </section>

            <section class="general">
                <div class="general-header">Estado General</div>
                <div class="general-sub">Resumen de tu bienestar emocional actual</div>

                <div class="general-body">
                    <div>
                        <strong>Tendencia General</strong><br>
                        <span>${data.tendencia}</span>
                    </div>
                    <span class="status">${data.estadoGeneral}</span>
                </div>

                <button class="dashboard-btn" onclick="realizarNuevoTest()">Realizar Nuevo Test</button>
            </section>

            <section class="metrics">
                <div class="metric">
                    <div class="value">${data.totalTests}</div>
                    <div class="label">Tests Realizados</div>
                </div>
                <div class="metric">
                    <div class="value">${data.cambioMes > 0 ? "+" : ""}${data.cambioMes}%</div>
                    <div class="label">Cambio del Mes</div>
                </div>
                <div class="metric">
                    <div class="value">${data.diasUltimoTest}</div>
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
