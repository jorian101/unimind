<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

require_once __DIR__ . '/../../controllers/ReportsController.php';

$reportsController = new ReportsController();

// Totales (delegado al ReportsController / ReportsModel)
$counts = $reportsController->getSummaryCounts();
$usuarios = $counts['usuarios'] ?? 0;
$cursos = $counts['cursos'] ?? 0;
$escuelas = $counts['escuelas'] ?? 0;
$tests = $counts['tests'] ?? 0;

// Actividad reciente: delegada al controller (ya devuelve rows con las mismas columnas)
$actividad = $reportsController->getActividadReciente(5);
?>

<?php require_once __DIR__ . '/../../utils/asset-version.php'; ?>
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="public/css/style.css?v=<?php echo asset_version('public/css/style.css'); ?>">
<link rel="stylesheet" href="views/administrador/dashboard.css?v=<?php echo asset_version('views/administrador/dashboard.css'); ?>">

<main class="admin-dashboard-container">
	<div class="page-header">
		<div class="header-content">
			<h1><i class="fas fa-tachometer-alt"></i> Panel de Administrador</h1>
			<p class="subtitle">Resumen del sistema, métricas y actividad reciente</p>
		</div>
		<div class="header-actions">
			<button class="btn btn-primary" onclick="window.location.href='index.php?role=administrador&page=tests&nuevo=1'">
				<i class="fas fa-plus"></i> Crear Test
			</button>
			<button class="btn btn-secondary" onclick="window.location.href='index.php?role=administrador&page=reportes'">
				<i class="fas fa-chart-line"></i> Ver Reportes
			</button>
		</div>
	</div>

	<section class="stats-grid">
		<article class="stat-card">
			<div class="stat-header">
				<h3>Usuarios</h3>
			</div>
			<div class="stat-value"><i class="fas fa-users"></i><span><?= $usuarios ?></span></div>
			<div class="stat-label">Total Usuarios</div>
		</article>

		<article class="stat-card">
			<div class="stat-header">
				<h3>Cursos</h3>
			</div>
			<div class="stat-value"><i class="fas fa-book"></i><span><?= $cursos ?></span></div>
			<div class="stat-label">Total Cursos</div>
		</article>

		<article class="stat-card">
			<div class="stat-header">
				<h3>Escuelas</h3>
			</div>
			<div class="stat-value"><i class="fas fa-school"></i><span><?= $escuelas ?></span></div>
			<div class="stat-label">Total Escuelas</div>
		</article>

		<article class="stat-card">
			<div class="stat-header">
				<h3>Tests</h3>
			</div>
			<div class="stat-value"><i class="fas fa-clipboard-list"></i><span><?= $tests ?></span></div>
			<div class="stat-label">Total Tests</div>
		</article>
	</section>

	<section class="activity-section">
		<h2 class="section-title">Actividad Reciente</h2>
		<div class="table-wrapper">
			<table class="activity-table">
				<thead>
					<tr>
						<th>Fecha</th>
						<th>Usuario</th>
						<th>Acción</th>
						<th>Detalle</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($actividad as $row): ?>
						<tr>
							<td>
								<?php
									$fecha = DateTime::createFromFormat('Y-m-d H:i:s', $row['fecha']);
									echo $fecha ? $fecha->format('d/m/Y H:i') : htmlspecialchars($row['fecha']);
								?>
							</td>
							<td><?= htmlspecialchars($row['usuario']) ?></td>
							<td><?= htmlspecialchars($row['accion']) ?></td>
							<td><?= htmlspecialchars($row['detalle']) ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>
</main>
