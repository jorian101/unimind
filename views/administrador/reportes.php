<?php
require_once __DIR__ . '/../../models/administrador/ReportsModel.php';

$reportsModel = new ReportsModel();
$summary = $reportsModel->getSummaryCounts();
$usuarios = $summary['usuarios'] ?? 0;
$cursos = $summary['cursos'] ?? 0;
$escuelas = $summary['escuelas'] ?? 0;
$tests = $summary['tests'] ?? 0;
$actividad = $reportsModel->getActividadReciente(10);
$niveles = $reportsModel->getNivelesDistribucion();
$escuelas_riesgo = $reportsModel->getEscuelasRiesgo();
$puntuaciones_mes = $reportsModel->getPuntuacionesMes();
?>
<link rel="stylesheet" href="reportes.css">
<div class="reportes-container" style="max-width: 98vw; min-width: 320px; margin: 24px auto 24px auto; background: #fff; padding: 48px 3vw; border-radius: 24px; box-shadow: 0 4px 24px #0002; font-family: 'Inter',sans-serif;">
  <h2>Reportes del Sistema</h2>
  <div style="margin-bottom:32px;">
      <h3>Resumen General</h3>
      <div style="display:flex;gap:32px;flex-wrap:wrap;justify-content:center;">
        <div class="card-resumen">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-users" style="margin-right:8px;"></i><?= $usuarios ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Usuarios</div>
        </div>
        <div class="card-resumen">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-book" style="margin-right:8px;"></i><?= $cursos ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Cursos</div>
        </div>
        <div class="card-resumen">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-school" style="margin-right:8px;"></i><?= $escuelas ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Escuelas</div>
        </div>
        <div class="card-resumen">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-clipboard-list" style="margin-right:8px;"></i><?= $tests ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Tests</div>
        </div>
      </div>
  </div>
  <!-- Distribución de Niveles de Riesgo -->
  <div style="margin:48px 0 32px 0;">
    <h3>Distribución de Niveles de Riesgo</h3>
    <div style="display:flex;gap:48px;flex-wrap:wrap;align-items:center;justify-content:center;">
      <div class="grafico-container">
        <canvas id="graficoRiesgo" width="320" height="320"></canvas>
      </div>
      <div style="flex:1 1 220px;min-width:220px;max-width:320px;">
        <ul class="niveles-list">
          <?php foreach ($niveles as $nivel): ?>
            <li>
              <span class="nivel-dot" style="background:#<?= ($nivel['resultado_nivel']=='Alto'?'e53e3e':($nivel['resultado_nivel']=='Moderado'?'f59e0b':'10b981')) ?>;"></span>
              <b><?= htmlspecialchars($nivel['resultado_nivel']) ?>:</b> <?= $nivel['total'] ?> casos
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Escuelas y riesgo -->
  <div style="margin-bottom:48px;">
    <h3>Escuelas y Promedio de Puntuación / Casos de Riesgo Alto</h3>
    <table class="reportes-table" style="border-collapse: separate; border-spacing: 0; width: 100%;">
      <thead>
        <tr>
          <th>Escuela</th>
          <th>Promedio Puntuación</th>
          <th>Casos Riesgo Alto</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($escuelas_riesgo as $row): ?>
        <tr style="border-bottom: 3px solid #bdbdbd;">
          <td style="border-right: 2px solid #bdbdbd; border-bottom: 2px solid #bdbdbd; padding: 12px 8px;"> <?= htmlspecialchars($row['nombre_escuela']) ?> </td>
          <td style="border-right: 2px solid #bdbdbd; border-bottom: 2px solid #bdbdbd; padding: 12px 8px;"> <?= number_format($row['promedio_puntuacion'],2) ?> </td>
          <td style="border-bottom: 2px solid #bdbdbd; padding: 12px 8px;"> <?= $row['casos_alto'] ?> </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

    <!-- Línea de tiempo: Promedio de puntuaciones mes a mes -->
    <div style="margin-bottom:48px;">
      <h3>Promedio de Puntuaciones a lo largo del tiempo</h3>
      <div class="grafico-linea-container" style="max-width: 900px; margin: 0 auto;">
        <canvas id="graficoLineaTiempo" width="900" height="340"></canvas>
      </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de pastel/donut para niveles de riesgo
document.addEventListener('DOMContentLoaded', function() {
  var ctx = document.getElementById('graficoRiesgo').getContext('2d');
  var data = {
    labels: [
      <?php foreach ($niveles as $nivel): ?>
        "<?= htmlspecialchars($nivel['resultado_nivel']) ?>",
      <?php endforeach; ?>
    ],
    datasets: [{
      data: [
        <?php foreach ($niveles as $nivel): ?>
          <?= $nivel['total'] ?>,
        <?php endforeach; ?>
      ],
      backgroundColor: [
        <?php foreach ($niveles as $nivel): ?>
          '#<?= ($nivel['resultado_nivel']=='Alto'?'e53e3e':($nivel['resultado_nivel']=='Moderado'?'f59e0b':'10b981')) ?>',
        <?php endforeach; ?>
      ],
      borderWidth: 2
    }]
  };
  new Chart(ctx, {
    type: 'doughnut',
    data: data,
    options: {
      responsive: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { font: { size: 16 } }
        }
      }
    }
  });
});
// Gráfico de línea para promedio de puntuaciones mes a mes
document.addEventListener('DOMContentLoaded', function() {
  var ctxLinea = document.getElementById('graficoLineaTiempo').getContext('2d');
  var dataLinea = {
    labels: [
      <?php foreach ($puntuaciones_mes as $row): ?>
        "<?= htmlspecialchars($row['mes']) ?>",
      <?php endforeach; ?>
    ],
    datasets: [{
      label: 'Promedio de puntuaciones',
      data: [
        <?php foreach ($puntuaciones_mes as $row): ?>
          <?= number_format($row['promedio'],2) ?>,
        <?php endforeach; ?>
      ],
      fill: false,
      borderColor: '#6b1a1a',
      backgroundColor: '#f59e0b',
      tension: 0.2,
      pointRadius: 5,
      pointBackgroundColor: '#6b1a1a',
      pointBorderColor: '#fff',
      pointHoverRadius: 7
    }]
  };
  new Chart(ctxLinea, {
    type: 'line',
    data: dataLinea,
    options: {
      responsive: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: { font: { size: 15 } }
        }
      },
      scales: {
        x: {
          title: { display: true, text: 'Mes', font: { size: 14 } }
        },
        y: {
          title: { display: true, text: 'Promedio', font: { size: 14 } },
          beginAtZero: true
        }
      }
    }
  });
});
</script>
