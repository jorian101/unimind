<?php
require_once __DIR__ . '/../../database/Database.php';
$db = new Database();
$conn = $db->connect();

// Obtener escuelas
$escuelas = $conn->query('SELECT * FROM Escuelas ORDER BY nombre_escuela')->fetchAll(PDO::FETCH_ASSOC);
// Obtener cursos con nombre de escuela y profesor
$sql = "SELECT c.*, e.nombre_escuela, u.nombre AS profesor_nombre, u.apellido AS profesor_apellido FROM Cursos c
        JOIN Escuelas e ON c.id_escuela = e.id_escuela
        JOIN Usuarios u ON c.id_profesor = u.id_usuario
        ORDER BY c.nombre_curso";
$cursos = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="usuarios.css">
<div class="dashboard-container" style="min-height:100vh; background:#f6f6f6; font-family:'Inter',sans-serif;">
  <div style="max-width:98vw; margin:32px auto; padding:0 2vw; background:#fff; border-radius:24px; box-shadow:0 4px 24px #0002;">
    <h1 style="color:#6b1a1a; font-size:2.2rem; font-weight:700; margin-bottom:8px; margin-top:0;">Gestión de Cursos y Escuelas</h1>
    <p style="color:#7c7c7c; margin-bottom:32px; margin-top:0;">Administra las escuelas y cursos registrados</p>
    <div style="display:flex; gap:32px; flex-wrap:wrap; justify-content:center; margin-bottom:40px;">
      <button class="btn btn-primary" id="btnNuevaEscuela" style="padding:10px 24px; border-radius:8px; background:#6b1a1a; color:#fff; border:none; font-weight:600; cursor:pointer;">Nueva Escuela</button>
      <button class="btn btn-secondary" id="btnNuevoCurso" style="padding:10px 24px; border-radius:8px; background:#fff; color:#6b1a1a; border:1px solid #6b1a1a; font-weight:600; cursor:pointer;">Nuevo Curso</button>
    </div>
    <div style="display:flex; gap:48px; flex-wrap:wrap; justify-content:center;">
      <div style="flex:1; min-width:340px;">
        <h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:18px; margin-top:0;">Escuelas</h2>
        <table style="width:100%; border-collapse:separate; border-spacing:0;">
          <thead>
            <tr style="background-color:#f6f6f6; color:#6b1a1a; font-weight:600;">
              <th style="padding:12px 8px; text-align:left;">Nombre</th>
              <th style="padding:12px 8px; text-align:left;">Teléfono</th>
              <th style="padding:12px 8px; text-align:left;">Acciones</th>
            </tr>
          </thead>
          <tbody id="escuelasTableBody">
            <?php foreach ($escuelas as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['nombre_escuela']) ?></td>
              <td><?= htmlspecialchars($e['telefono']) ?></td>
              <td>
                <a href="#" class="btn btn-primary editar-escuela" data-id="<?= $e['id_escuela'] ?>" style="padding:6px 14px; border-radius:6px; background:#6b1a1a; color:#fff; border:none; font-size:0.95rem; margin-right:6px;">Editar</a>
                <a href="#" class="btn btn-secondary eliminar-escuela" data-id="<?= $e['id_escuela'] ?>" style="padding:6px 14px; border-radius:6px; background:#fff; color:#6b1a1a; border:1px solid #6b1a1a; font-size:0.95rem;">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div style="flex:2; min-width:440px;">
        <h2 style="color:#6b1a1a; font-size:1.3rem; margin-bottom:18px; margin-top:0;">Cursos</h2>
        <table style="width:100%; border-collapse:separate; border-spacing:0;">
          <thead>
            <tr style="background-color:#f6f6f6; color:#6b1a1a; font-weight:600;">
              <th style="padding:12px 8px; text-align:left;">Nombre</th>
              <th style="padding:12px 8px; text-align:left;">Escuela</th>
              <th style="padding:12px 8px; text-align:left;">Profesor</th>
              <th style="padding:12px 8px; text-align:left;">Acciones</th>
            </tr>
          </thead>
          <tbody id="cursosTableBody">
            <?php foreach ($cursos as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['nombre_curso']) ?></td>
              <td><?= htmlspecialchars($c['nombre_escuela']) ?></td>
              <td><?= htmlspecialchars($c['profesor_nombre'] . ' ' . $c['profesor_apellido']) ?></td>
              <td>
                <a href="#" class="btn btn-primary editar-curso" data-id="<?= $c['id_curso'] ?>" style="padding:6px 14px; border-radius:6px; background:#6b1a1a; color:#fff; border:none; font-size:0.95rem; margin-right:6px;">Editar</a>
                <a href="#" class="btn btn-secondary eliminar-curso" data-id="<?= $c['id_curso'] ?>" style="padding:6px 14px; border-radius:6px; background:#fff; color:#6b1a1a; border:1px solid #6b1a1a; font-size:0.95rem;">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Modales para CRUD -->
    <!-- Modal Nueva Escuela -->
    <div id="modalNuevaEscuela" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#0007; z-index:9999; align-items:center; justify-content:center;">
      <div class="modal-content" style="background:linear-gradient(135deg,#fff 80%,#f6f6f6 100%); border-radius:20px; padding:40px 32px 32px 32px; max-width:400px; margin:auto; box-shadow:0 4px 24px #0003; position:relative;">
        <span class="close-modal" style="position:absolute; top:18px; right:24px; font-size:2rem; color:#6b1a1a; cursor:pointer;">&times;</span>
        <h2 style="color:#6b1a1a; margin-bottom:24px; font-size:1.3rem; font-weight:700; text-align:center;">Nueva Escuela</h2>
        <form id="formNuevaEscuela" method="post" style="display:flex; flex-direction:column; gap:18px;">
          <label style="font-size:0.97rem; color:#6b1a1a; font-weight:500;">Nombre de la escuela</label>
          <input type="text" name="nombre_escuela" required class="usuarios-search-input">
          <label style="font-size:0.97rem; color:#6b1a1a; font-weight:500;">Teléfono</label>
          <input type="text" name="telefono" class="usuarios-search-input">
          <button type="submit" class="btn btn-primary" style="width:100%; font-size:1.08rem; padding:12px 0;">Crear Escuela</button>
        </form>
      </div>
    </div>
    <!-- Modal Editar/Eliminar Escuela, Modal Nuevo/Editar/Eliminar Curso -->
    <!-- ...similar estructura, se agregan según funcionalidad... -->
  </div>
</div>
<script>
// Abrir modal nueva escuela
const btnNuevaEscuela = document.getElementById('btnNuevaEscuela');
const modalNuevaEscuela = document.getElementById('modalNuevaEscuela');
btnNuevaEscuela.onclick = () => { modalNuevaEscuela.style.display = 'flex'; };
document.querySelectorAll('.close-modal').forEach(btn => {
  btn.onclick = function() { btn.closest('.modal').style.display = 'none'; };
});
// Enviar nueva escuela
const formNuevaEscuela = document.getElementById('formNuevaEscuela');
formNuevaEscuela.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('crear_escuela', '1');
  fetch('api/escuelas.php', {
    method: 'POST',
    body: formData
  }).then(res => res.json()).then(data => {
    alert(data.Mensaje || 'Escuela creada');
    location.reload();
  });
};
// ...similar para cursos y edición/eliminación...
</script>
