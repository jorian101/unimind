<?php
require_once __DIR__ . '/../../database/Database.php';
$db = new Database();
$conn = $db->connect();

// Totales
$usuarios = $conn->query('SELECT COUNT(*) FROM Usuarios')->fetchColumn();
$cursos = $conn->query('SELECT COUNT(*) FROM Cursos')->fetchColumn();
$escuelas = $conn->query('SELECT COUNT(*) FROM Escuelas')->fetchColumn();
$tests = $conn->query('SELECT COUNT(*) FROM Tests')->fetchColumn();

// Actividad reciente: últimas aplicaciones y creaciones
$actividad = $conn->query('
		SELECT a.fecha_aplicacion AS fecha, CONCAT(u.nombre, " ", u.apellido) AS usuario, "Creó Test" AS accion, t.nombre AS detalle
		FROM Aplicaciones a
		JOIN Usuarios u ON a.id_usuario = u.id_usuario
		JOIN Tests t ON a.id_test = t.id_test
		ORDER BY a.fecha_aplicacion DESC
		LIMIT 5
')->fetchAll(PDO::FETCH_ASSOC);
?>

<link rel="stylesheet" href="dashboard.css">
<div class="admin-dashboard" style="min-height:100vh; background:#f6f6f6; padding:0; font-family:'Inter',sans-serif;">
	<div class="dashboard-container" style="max-width:98vw; margin:32px auto; padding:0 2vw; background:#fff; border-radius:24px; box-shadow:0 4px 24px #0002;">
		<h1 class="page-title" style="color:#6b1a1a; font-size:2.2rem; font-weight:700; margin-bottom:8px; margin-top:0;">Panel de Administrador</h1>
		<p class="page-subtitle" style="color:#7c7c7c; margin-bottom:32px; margin-top:0;">Gestión del sistema y usuarios</p>

		<div class="quick-actions" style="display:flex; gap:24px; margin-bottom:40px; justify-content:center;">
			<button class="btn btn-primary" onclick="window.location.href='index.php?role=administrador&page=tests&nuevo=1'">Crear Test</button>
			<button class="btn btn-secondary" onclick="window.location.href='index.php?role=administrador&page=reportes'">Ver Reportes</button>
		</div>

		<div class="stats-grid" style="display:grid; grid-template-columns:repeat(4,1fr); gap:32px; margin-bottom:48px;">
			<div class="stat-card" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001; text-align:center;">
				<h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:8px; margin-top:0;">Usuarios</h2>
				<div class="stat-value" style="font-size:2.3rem; font-weight:700; color:#6b1a1a;">
					<i class="fas fa-users" style="margin-right:8px;"></i><?= $usuarios ?>
				</div>
				<div class="stat-label" style="color:#7c7c7c; font-size:1rem;">Total Usuarios</div>
			</div>
			<div class="stat-card" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001; text-align:center;">
				<h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:8px; margin-top:0;">Cursos</h2>
				<div class="stat-value" style="font-size:2.3rem; font-weight:700; color:#6b1a1a;">
					<i class="fas fa-book" style="margin-right:8px;"></i><?= $cursos ?>
				</div>
				<div class="stat-label" style="color:#7c7c7c; font-size:1rem;">Total Cursos</div>
			</div>
			<div class="stat-card" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001; text-align:center;">
				<h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:8px; margin-top:0;">Escuelas</h2>
				<div class="stat-value" style="font-size:2.3rem; font-weight:700; color:#6b1a1a;">
					<i class="fas fa-school" style="margin-right:8px;"></i><?= $escuelas ?>
				</div>
				<div class="stat-label" style="color:#7c7c7c; font-size:1rem;">Total Escuelas</div>
			</div>
			<div class="stat-card" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001; text-align:center;">
				<h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:8px; margin-top:0;">Tests</h2>
				<div class="stat-value" style="font-size:2.3rem; font-weight:700; color:#6b1a1a;">
					<i class="fas fa-clipboard-list" style="margin-right:8px;"></i><?= $tests ?>
				</div>
				<div class="stat-label" style="color:#7c7c7c; font-size:1rem;">Total Tests</div>
			</div>
		</div>

		<div class="activity-section" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001;">
			<h2 class="section-title" style="color:#6b1a1a; font-size:1.3rem; margin-bottom:18px; margin-top:0;">Actividad Reciente</h2>
			<table class="activity-table" style="width:100%; border-collapse:separate; border-spacing:0;">
				<thead>
					<tr style="background-color:#f6f6f6; color:#6b1a1a; font-weight:600;">
						<th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Fecha</th>
						<th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Usuario</th>
						<th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Acción</th>
						<th style="padding:12px 8px; text-align:left;">Detalle</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($actividad as $row): ?>
					<tr style="border-bottom:3px solid #bdbdbd;">
						<td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">
							<?php 
								$fecha = DateTime::createFromFormat('Y-m-d H:i:s', $row['fecha']);
								echo $fecha ? $fecha->format('d/m/Y H:i') : htmlspecialchars($row['fecha']);
							?>
						</td>
						<td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;"> <?= htmlspecialchars($row['usuario']) ?> </td>
						<td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;"> <?= htmlspecialchars($row['accion']) ?> </td>
						<td style="border-bottom:2px solid #bdbdbd; padding:12px 8px;"> <?= htmlspecialchars($row['detalle']) ?> </td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>