<?php
require_once __DIR__ . '/../../database/Database.php';
$db = new Database();
$conn = $db->connect();
$usuarios = $conn->query('SELECT COUNT(*) FROM Usuarios')->fetchColumn();
$cursos = $conn->query('SELECT COUNT(*) FROM Cursos')->fetchColumn();
$escuelas = $conn->query('SELECT COUNT(*) FROM Escuelas')->fetchColumn();
$tests = $conn->query('SELECT COUNT(*) FROM Tests')->fetchColumn();
$actividad = $conn->query('
    SELECT a.fecha_aplicacion AS fecha, CONCAT(u.nombre, " ", u.apellido) AS usuario, "Creó Test" AS accion, t.nombre AS detalle
    FROM Aplicaciones a
    JOIN Usuarios u ON a.id_usuario = u.id_usuario
    JOIN Tests t ON a.id_test = t.id_test
    ORDER BY a.fecha_aplicacion DESC
    LIMIT 10
')->fetchAll(PDO::FETCH_ASSOC);

// Distribución de niveles de riesgo (Estrés y Ansiedad)
$niveles = $conn->query('
  SELECT resultado_nivel, COUNT(*) as total
  FROM Aplicaciones
  GROUP BY resultado_nivel
')->fetchAll(PDO::FETCH_ASSOC);

// Escuelas y promedio de puntuación total o recuento de casos de "Riesgo Alto"
$escuelas_riesgo = $conn->query('
  SELECT e.nombre_escuela,
       AVG(a.puntuacion_total) as promedio_puntuacion,
       SUM(CASE WHEN a.resultado_nivel = "Alto" THEN 1 ELSE 0 END) as casos_alto
  FROM Aplicaciones a
  JOIN Usuarios u ON a.id_usuario = u.id_usuario
  JOIN Usuario_Escuela ue ON ue.id_usuario = u.id_usuario
  JOIN Escuelas e ON ue.id_escuela = e.id_escuela
  GROUP BY e.id_escuela
  ORDER BY casos_alto DESC, promedio_puntuacion DESC
')->fetchAll(PDO::FETCH_ASSOC);

// Promedio de puntuaciones mes a mes
$puntuaciones_mes = $conn->query('
  SELECT DATE_FORMAT(fecha_aplicacion, "%Y-%m") as mes, AVG(puntuacion_total) as promedio
  FROM Aplicaciones
  GROUP BY mes
  ORDER BY mes ASC
')->fetchAll(PDO::FETCH_ASSOC);
?>
<div style="max-width:900px;margin:40px auto;background:#fff;padding:32px;border-radius:16px;box-shadow:0 2px 8px #0001;font-family:'Inter',sans-serif;">
  <h2 style="color:#6b1a1a;font-size:1.5rem;font-weight:700;margin-bottom:16px;">Reportes del Sistema</h2>
  <div style="margin-bottom:24px;">
      <h3 style="color:#6b1a1a;font-size:1.1rem;margin-bottom:18px;">Resumen General</h3>
      <div style="display:flex;gap:24px;flex-wrap:wrap;justify-content:center;">
        <div class="card-resumen" style="flex:1 1 180px;min-width:180px;max-width:220px;background:#f6f6f6;border-radius:12px;padding:24px 18px;box-shadow:0 2px 8px #0001;display:flex;flex-direction:column;align-items:center;">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-users" style="margin-right:8px;"></i><?= $usuarios ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Usuarios</div>
        </div>
        <div class="card-resumen" style="flex:1 1 180px;min-width:180px;max-width:220px;background:#f6f6f6;border-radius:12px;padding:24px 18px;box-shadow:0 2px 8px #0001;display:flex;flex-direction:column;align-items:center;">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-book" style="margin-right:8px;"></i><?= $cursos ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Cursos</div>
        </div>
        <div class="card-resumen" style="flex:1 1 180px;min-width:180px;max-width:220px;background:#f6f6f6;border-radius:12px;padding:24px 18px;box-shadow:0 2px 8px #0001;display:flex;flex-direction:column;align-items:center;">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-school" style="margin-right:8px;"></i><?= $escuelas ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Escuelas</div>
        </div>
        <div class="card-resumen" style="flex:1 1 180px;min-width:180px;max-width:220px;background:#f6f6f6;border-radius:12px;padding:24px 18px;box-shadow:0 2px 8px #0001;display:flex;flex-direction:column;align-items:center;">
          <div style="font-size:2.2rem;color:#6b1a1a;font-weight:700;margin-bottom:8px;"><i class="fas fa-clipboard-list" style="margin-right:8px;"></i><?= $tests ?></div>
          <div style="font-size:1.1rem;color:#333;font-weight:500;">Tests</div>
        </div>
      </div>
  </div>
  <!-- Distribución de Niveles de Riesgo -->
  <div style="margin:32px 0 24px 0;">
    <h3 style="color:#6b1a1a;font-size:1.1rem;margin-bottom:18px;">Distribución de Niveles de Riesgo</h3>
    <div style="display:flex;gap:32px;flex-wrap:wrap;align-items:center;justify-content:center;">
      <div style="flex:1 1 320px;min-width:320px;max-width:400px;">
        <canvas id="graficoRiesgo" width="320" height="320"></canvas>
      </div>
      <div style="flex:1 1 220px;min-width:220px;max-width:320px;">
        <ul style="list-style:none;padding:0;margin:0;">
          <?php foreach ($niveles as $nivel): ?>
            <li style="margin-bottom:12px;font-size:1.05rem;color:#333;">
              <span style="display:inline-block;width:18px;height:18px;border-radius:50%;background:#<?= ($nivel['resultado_nivel']=='Alto'?'e53e3e':($nivel['resultado_nivel']=='Moderado'?'f59e0b':'10b981')) ?>;margin-right:8px;"></span>
              <b><?= htmlspecialchars($nivel['resultado_nivel']) ?>:</b> <?= $nivel['total'] ?> casos
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <!-- Escuelas y riesgo -->
  <div style="margin-bottom:32px;">
    <h3 style="color:#6b1a1a;font-size:1.1rem;margin-bottom:18px;">Escuelas y Promedio de Puntuación / Casos de Riesgo Alto</h3>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f6f6f6;color:#6b1a1a;font-weight:600;">
          <th style="padding:12px 8px;text-align:left;">Escuela</th>
          <th style="padding:12px 8px;text-align:left;">Promedio Puntuación</th>
          <th style="padding:12px 8px;text-align:left;">Casos Riesgo Alto</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($escuelas_riesgo as $row): ?>
        <tr>
          <td style="padding:10px 8px;"> <?= htmlspecialchars($row['nombre_escuela']) ?> </td>
          <td style="padding:10px 8px;"> <?= number_format($row['promedio_puntuacion'],2) ?> </td>
          <td style="padding:10px 8px;"> <?= $row['casos_alto'] ?> </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

    <!-- Línea de tiempo: Promedio de puntuaciones mes a mes -->
    <div style="margin-bottom:32px;">
      <h3 style="color:#6b1a1a;font-size:1.1rem;margin-bottom:18px;">Promedio de Puntuaciones a lo largo del tiempo</h3>
      <div style="width:100%;max-width:700px;margin:0 auto;">
        <canvas id="graficoLineaTiempo" width="700" height="320"></canvas>
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
