<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

require_once __DIR__ . '/../../controllers/ReportsController.php';

$reportsController = new ReportsController();
$summary = $reportsController->getSummaryCounts();
$usuarios = $summary['usuarios'] ?? 0;
$cursos = $summary['cursos'] ?? 0;
$escuelas = $summary['escuelas'] ?? 0;
$tests = $summary['tests'] ?? 0;
$actividad = $reportsController->getActividadReciente(10);
// Datos adicionales se obtienen del modelo ya que ReportsController aún no expone todos los métodos
require_once __DIR__ . '/../../models/administrador/ReportsModel.php';
$reportsModel = new ReportsModel();
$niveles = $reportsModel->getNivelesDistribucion();
$escuelas_riesgo = $reportsModel->getEscuelasRiesgo();
$puntuaciones_mes = $reportsModel->getPuntuacionesMes();
?>

<?php require_once __DIR__ . '/../../utils/asset-version.php'; ?>
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="public/css/style.css?v=<?php echo asset_version('public/css/style.css'); ?>">
<link rel="stylesheet" href="views/administrador/reportes.css?v=<?php echo asset_version('views/administrador/reportes.css'); ?>">

<main class="reportes-container">
  <div class="page-header">
    <div class="header-content">
      <h1><i class="fas fa-chart-pie"></i> Reportes del Sistema</h1>
      <p class="subtitle">Resumen general, distribución de riesgo y evolución de puntuaciones</p>
    </div>
  </div>

  <section class="summary-section">
    <h2 class="section-title">Resumen General</h2>
    <div class="summary-grid">
      <div class="card-resumen">
        <div class="card-value"><i class="fas fa-users"></i><span><?= $usuarios ?></span></div>
        <div class="card-label">Usuarios</div>
      </div>
      <div class="card-resumen">
        <div class="card-value"><i class="fas fa-book"></i><span><?= $cursos ?></span></div>
        <div class="card-label">Cursos</div>
      </div>
      <div class="card-resumen">
        <div class="card-value"><i class="fas fa-school"></i><span><?= $escuelas ?></span></div>
        <div class="card-label">Escuelas</div>
      </div>
      <div class="card-resumen">
        <div class="card-value"><i class="fas fa-clipboard-list"></i><span><?= $tests ?></span></div>
        <div class="card-label">Tests</div>
      </div>
    </div>
  </section>

  <section class="riesgo-section">
    <h2 class="section-title">Distribución de Niveles de Riesgo</h2>
    <div class="riesgo-grid">
      <div class="grafico-container">
        <canvas id="graficoRiesgo"></canvas>
      </div>
      <aside class="niveles-aside">
        <ul class="niveles-list">
          <?php foreach ($niveles as $nivel): ?>
            <li>
              <span class="nivel-dot" data-nivel="<?= htmlspecialchars($nivel['resultado_nivel']) ?>" data-color="#<?= ($nivel['resultado_nivel']=='Alto'?'e53e3e':($nivel['resultado_nivel']=='Moderado'?'f59e0b':'10b981')) ?>"></span>
              <b><?= htmlspecialchars($nivel['resultado_nivel']) ?>:</b>
              <span class="nivel-count"><?= $nivel['total'] ?> casos</span>
            </li>
          <?php endforeach; ?>
        </ul>
      </aside>
    </div>
  </section>

  <section class="escuelas-section">
    <h2 class="section-title">Escuelas y Promedio / Casos Alto</h2>
    <div class="table-wrapper">
      <table class="reportes-table">
        <thead>
          <tr>
            <th>Escuela</th>
            <th>Promedio Puntuación</th>
            <th>Casos Riesgo Alto</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($escuelas_riesgo as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['nombre_escuela']) ?></td>
              <td><?= number_format($row['promedio_puntuacion'],2) ?></td>
              <td><?= $row['casos_alto'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </section>

  <section class="evolucion-section">
    <h2 class="section-title">Promedio de Puntuaciones a lo largo del tiempo</h2>
    <div class="grafico-linea-container">
      <canvas id="graficoLineaTiempo"></canvas>
    </div>
  </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Pie/Doughnut data
  const labelsRiesgo = [
    <?php foreach ($niveles as $nivel): ?>
      "<?= htmlspecialchars($nivel['resultado_nivel']) ?>",
    <?php endforeach; ?>
  ];
  const valuesRiesgo = [
    <?php foreach ($niveles as $nivel): ?>
      <?= $nivel['total'] ?>,
    <?php endforeach; ?>
  ];
  const colorsRiesgo = [
    <?php foreach ($niveles as $nivel): ?>
      '#<?= ($nivel['resultado_nivel']=='Alto'?'e53e3e':($nivel['resultado_nivel']=='Moderado'?'f59e0b':'10b981')) ?>',
    <?php endforeach; ?>
  ];

  // Helper to compute total
  const totalRiesgo = valuesRiesgo.reduce((s, v) => s + Number(v), 0) || 1;

  const ctx = document.getElementById('graficoRiesgo').getContext('2d');
  new Chart(ctx, {
    type: 'doughnut',
    data: { labels: labelsRiesgo, datasets: [{ data: valuesRiesgo, backgroundColor: colorsRiesgo, borderWidth: 2 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 13 } } },
        tooltip: {
          callbacks: {
            label: function(context) {
              const val = Number(context.parsed);
              const pct = ((val / totalRiesgo) * 100).toFixed(1);
              return context.label + ': ' + val + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });

  // Line chart
  const labelsLinea = [
    <?php foreach ($puntuaciones_mes as $row): ?>
      "<?= htmlspecialchars($row['mes']) ?>",
    <?php endforeach; ?>
  ];
  const valuesLinea = [
    <?php foreach ($puntuaciones_mes as $row): ?>
      <?= number_format($row['promedio'],2) ?>,
    <?php endforeach; ?>
  ];

  const ctxLinea = document.getElementById('graficoLineaTiempo').getContext('2d');
  new Chart(ctxLinea, {
    type: 'line',
    data: { labels: labelsLinea, datasets: [{ label: 'Promedio de puntuaciones', data: valuesLinea, fill: false, borderColor: getComputedStyle(document.documentElement).getPropertyValue('--pri-500') || '#70001e', backgroundColor: '#f59e0b', tension: 0.24, pointRadius: 4 }] },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: 'top', labels: { font: { size: 13 } } },
        tooltip: {
          callbacks: {
            label: function(ctx) { return ctx.dataset.label + ': ' + ctx.parsed.y; }
          }
        }
      },
      scales: {
        x: { title: { display: true, text: 'Mes' }, ticks: { maxRotation: 0, autoSkip: true } },
        y: { beginAtZero: true }
      }
    }
  });

  // Apply color chips to nivel-dot elements (read color from data-color)
  document.querySelectorAll('.nivel-dot').forEach(el => {
    const c = el.getAttribute('data-color');
    if (c) el.style.background = c;
  });
});
</script>
