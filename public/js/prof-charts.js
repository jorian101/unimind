// Fetch metrics and render charts using Chart.js
(function () {
  function ready(fn) {
    if (document.readyState === "loading")
      document.addEventListener("DOMContentLoaded", fn);
    else fn();
  }

  ready(function () {
    const url = "api/prof_metrics.php";
    const canvLine = document.getElementById("prof-line");
    const canvPie = document.getElementById("prof-pie");
    const canvBar = document.getElementById("prof-bar");
    const courseSelect = document.getElementById("chartCourseSelect");

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

        // populate course selector if present
        if (courseSelect) {
          // clear existing
          courseSelect.innerHTML = "";
          const allOpt = document.createElement("option");
          allOpt.value = "";
          allOpt.textContent = "Todos los cursos";
          courseSelect.appendChild(allOpt);
          courses.forEach((c) => {
            const o = document.createElement("option");
            o.value = c.id_curso;
            o.textContent = c.nombre_curso;
            courseSelect.appendChild(o);
          });
        }

        // store chart instances so we can destroy before re-creating
        let lineChart = null,
          pieChart = null,
          barChart = null;

        // render charts for a given course id (or first course)

        function renderFor(courseId) {
          let course = null;
          if (!courseId) {
            // Aggregate all courses for evolution chart (series)
            // For the first chart, show all schools (all courses together)
            const dateMap = {};
            courses.forEach((c) => {
              (c.series || []).forEach((s) => {
                if (!dateMap[s.date]) dateMap[s.date] = [];
                dateMap[s.date].push(s.value);
              });
            });
            const aggSeries = Object.entries(dateMap).map(([date, vals]) => ({
              date,
              value: vals.reduce((a, b) => a + b, 0) / vals.length,
            }));
            course = {
              nombre_curso: "Todas las escuelas",
              series: aggSeries,
              distribution: { Bajo: 0, Moderado: 0, Alto: 0 },
              avg_score: null,
            };
          } else {
            course =
              courses.find((x) => String(x.id_curso) === String(courseId)) ||
              null;
          }

          // destroy existing charts to avoid duplicates
          try {
            if (lineChart && typeof lineChart.destroy === "function")
              lineChart.destroy();
          } catch (e) {
            void e;
          }
          try {
            if (pieChart && typeof pieChart.destroy === "function")
              pieChart.destroy();
          } catch (e) {
            void e;
          }
          try {
            if (barChart && typeof barChart.destroy === "function")
              barChart.destroy();
          } catch (e) {
            void e;
          }

          // Line
          try {
            if (canvLine && course && course.series && window.Chart) {
              const labels = course.series.map((s) => s.date);
              const values = course.series.map((s) => s.value);
              lineChart = new Chart(canvLine.getContext("2d"), {
                type: "line",
                data: {
                  labels,
                  datasets: [
                    {
                      label: course.nombre_curso || "Promedio",
                      data: values,
                      borderColor: "#0ea5a4",
                      backgroundColor: "rgba(14,165,164,0.08)",
                      tension: 0.3,
                    },
                  ],
                },
                options: { responsive: false, maintainAspectRatio: false },
              });
            }
          } catch (e) {
            void e;
          }

          // Pie
          try {
            if (canvPie && course && course.distribution && window.Chart) {
              const dist = course.distribution;
              const labels = Object.keys(dist);
              const values = Object.values(dist);
              pieChart = new Chart(canvPie.getContext("2d"), {
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

          // Bar: faculties (global)
          try {
            if (canvBar && faculties.length > 0 && window.Chart) {
              const facNames = faculties.map((f) => f.nombre_escuela || "");
              const est = faculties.map((f) =>
                f.avg_estres !== null ? Number(f.avg_estres) : 0,
              );
              const ans = faculties.map((f) =>
                f.avg_ansiedad !== null ? Number(f.avg_ansiedad) : 0,
              );
              barChart = new Chart(canvBar.getContext("2d"), {
                type: "bar",
                data: {
                  labels: facNames,
                  datasets: [
                    {
                      label: "Ansiedad",
                      data: ans,
                      backgroundColor: "#7c3aed",
                    },
                    { label: "Estrés", data: est, backgroundColor: "#6366f1" },
                  ],
                },
                options: { responsive: false, maintainAspectRatio: false },
              });
            }
          } catch (e) {
            void e;
          }
        }

        // initial render
        renderFor();

        if (courseSelect) {
          courseSelect.addEventListener("change", function () {
            // simple approach: reload metrics and render selected course
            renderFor(courseSelect.value);
          });
          // Refresh button should trigger the same render
          const refreshBtn = document.getElementById("refreshChartsBtn");
          if (refreshBtn) {
            refreshBtn.addEventListener("click", function () {
              renderFor(courseSelect.value || "");
            });
          }
        }
      })
      .catch((err) => {
        void err;
      });
  });
})();
