// Fetch metrics and render charts using Chart.js
(function () {
  function ready(fn) {
    if (document.readyState === "loading")
      document.addEventListener("DOMContentLoaded", fn);
    else fn();
  }

  ready(function () {
    const url = "/unimind/api/prof_metrics.php";
    const canvLine = document.getElementById("prof-line");
    const canvPie = document.getElementById("prof-pie");
    const canvBar = document.getElementById("prof-bar");

    // set fixed heights to avoid layout jumps
    if (canvLine) {
      canvLine.style.height = "160px";
      canvLine.height = 160;
    }
    if (canvPie) {
      canvPie.style.height = "160px";
      canvPie.height = 160;
    }
    if (canvBar) {
      canvBar.style.height = "160px";
      canvBar.height = 160;
    }

    fetch(url)
      .then((r) => r.json())
      .then((data) => {
        if (!data || !data.success) return;
        const courses = data.courses || [];
        const faculties = data.faculties || [];

        // Line: use first course series
        try {
          if (
            canvLine &&
            courses.length > 0 &&
            courses[0].series &&
            courses[0].series.length > 0 &&
            window.Chart
          ) {
            const labels = courses[0].series.map((s) => s.date);
            const values = courses[0].series.map((s) => s.value);
            new Chart(canvLine.getContext("2d"), {
              type: "line",
              data: {
                labels,
                datasets: [
                  {
                    label: courses[0].nombre_curso || "Promedio",
                    data: values,
                    borderColor: "#6366f1",
                    backgroundColor: "rgba(99,102,241,0.08)",
                    tension: 0.3,
                  },
                ],
              },
              options: {
                responsive: false,
                maintainAspectRatio: false,
                scales: { x: { display: true }, y: { display: true } },
              },
            });
          }
        } catch (e) {
          void e;
        }

        // Pie: distribution of first course
        try {
          if (
            canvPie &&
            courses.length > 0 &&
            courses[0].distribution &&
            window.Chart
          ) {
            const dist = courses[0].distribution;
            const labels = Object.keys(dist);
            const values = Object.values(dist);
            new Chart(canvPie.getContext("2d"), {
              type: "doughnut",
              data: {
                labels,
                datasets: [
                  {
                    data: values,
                    backgroundColor: ["#34D399", "#FBBF24", "#F87171"],
                  },
                ],
              },
              options: {
                responsive: false,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
              },
            });
            // fill legend values
            const legendVals = document.querySelectorAll(
              ".pie-legend .legend-val",
            );
            if (legendVals && legendVals.length >= 3) {
              const total = values.reduce((a, b) => a + b, 0) || 1;
              legendVals[0].textContent =
                Math.round((values[0] / total) * 100) + "%";
              legendVals[1].textContent =
                Math.round((values[1] / total) * 100) + "%";
              legendVals[2].textContent =
                Math.round((values[2] / total) * 100) + "%";
            }
          }
        } catch (e) {
          void e;
        }

        // Bar: faculties comparison
        try {
          if (canvBar && faculties.length > 0 && window.Chart) {
            const facNames = faculties.map((f) => f.nombre_escuela || "");
            const est = faculties.map((f) =>
              f.avg_estres !== null ? Number(f.avg_estres) : 0,
            );
            const ans = faculties.map((f) =>
              f.avg_ansiedad !== null ? Number(f.avg_ansiedad) : 0,
            );
            new Chart(canvBar.getContext("2d"), {
              type: "bar",
              data: {
                labels: facNames,
                datasets: [
                  { label: "Ansiedad", data: ans, backgroundColor: "#7c3aed" },
                  { label: "Estrés", data: est, backgroundColor: "#6366f1" },
                ],
              },
              options: {
                responsive: false,
                maintainAspectRatio: false,
                scales: { x: { stacked: false }, y: { beginAtZero: true } },
              },
            });
          }
        } catch (e) {
          void e;
        }
      })
      .catch((err) => {
        void err;
      });
  });
})();
