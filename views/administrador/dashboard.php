
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
<!-- Dashboard Admin Rediseñado -->
<div class="admin-dashboard" style="background:#f6f6f6;min-height:100vh;padding:32px 0;font-family:'Inter',sans-serif;">
	<div style="max-width:1200px;margin:auto;">
		<h1 style="color:#6b1a1a;font-size:2rem;font-weight:700;margin-bottom:8px;">Panel de Administrador</h1>
		<p style="color:#7c7c7c;margin-bottom:32px;">Gestión del sistema y usuarios</p>

		<!-- Acciones rápidas -->
		<div style="display:flex;gap:16px;margin-bottom:32px;">
			<button style="background:#6b1a1a;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;" onclick="window.location.href='index.php?role=administrador&page=tests&nuevo=1'">Crear Test</button>
			<button style="background:#fff;color:#6b1a1a;border:1px solid #6b1a1a;padding:12px 24px;border-radius:8px;font-weight:600;cursor:pointer;" onclick="window.location.href='index.php?role=administrador&page=reportes'">Ver Reportes</button>
		</div>

		<!-- Tarjetas de gestión -->
		<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;margin-bottom:40px;">
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 8px #0001;text-align:center;">
				<h2 style="color:#6b1a1a;font-size:1.2rem;margin-bottom:8px;">Usuarios</h2>
				<div style="font-size:2rem;font-weight:700;color:#6b1a1a;"><?= $usuarios ?></div>
				<div style="color:#7c7c7c;font-size:0.95rem;">Total Usuarios</div>
			</div>
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 8px #0001;text-align:center;">
				<h2 style="color:#6b1a1a;font-size:1.2rem;margin-bottom:8px;">Cursos</h2>
				<div style="font-size:2rem;font-weight:700;color:#6b1a1a;"><?= $cursos ?></div>
				<div style="color:#7c7c7c;font-size:0.95rem;">Total Cursos</div>
			</div>
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 8px #0001;text-align:center;">
				<h2 style="color:#6b1a1a;font-size:1.2rem;margin-bottom:8px;">Escuelas</h2>
				<div style="font-size:2rem;font-weight:700;color:#6b1a1a;"><?= $escuelas ?></div>
				<div style="color:#7c7c7c;font-size:0.95rem;">Total Escuelas</div>
			</div>
			<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 8px #0001;text-align:center;">
				<h2 style="color:#6b1a1a;font-size:1.2rem;margin-bottom:8px;">Tests</h2>
				<div style="font-size:2rem;font-weight:700;color:#6b1a1a;"><?= $tests ?></div>
				<div style="color:#7c7c7c;font-size:0.95rem;">Total Tests</div>
			</div>
		</div>

		<!-- Tabla de actividad reciente -->
		<div style="background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 8px #0001;">
			<h2 style="color:#6b1a1a;font-size:1.2rem;margin-bottom:16px;">Actividad Reciente</h2>
			<table style="width:100%;border-collapse:collapse;">
				<thead>
					<tr style="background:#f6f6f6;color:#6b1a1a;font-weight:600;">
						<th style="padding:12px 8px;text-align:left;">Fecha</th>
						<th style="padding:12px 8px;text-align:left;">Usuario</th>
						<th style="padding:12px 8px;text-align:left;">Acción</th>
						<th style="padding:12px 8px;text-align:left;">Detalle</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($actividad as $row): ?>
					<tr>
						<td style="padding:10px 8px;"><?= htmlspecialchars($row['fecha']) ?></td>
						<td style="padding:10px 8px;"><?= htmlspecialchars($row['usuario']) ?></td>
						<td style="padding:10px 8px;"><?= htmlspecialchars($row['accion']) ?></td>
						<td style="padding:10px 8px;"><?= htmlspecialchars($row['detalle']) ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
