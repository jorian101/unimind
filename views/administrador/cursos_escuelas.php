<?php
require_once __DIR__ . '/../../database/Database.php';
$db = new Database();
$conn = $db->connect();

require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

// Obtener escuelas
$escuelas = $conn->query('SELECT * FROM Escuelas ORDER BY nombre_escuela')->fetchAll(PDO::FETCH_ASSOC);
// Obtener cursos con nombre de escuela y profesor
$sql = "SELECT c.*, e.nombre_escuela, u.nombre AS profesor_nombre, u.apellido AS profesor_apellido FROM Cursos c
        JOIN Escuelas e ON c.id_escuela = e.id_escuela
        JOIN Usuarios u ON c.id_profesor = u.id_usuario
        ORDER BY c.nombre_curso";
$cursos = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<link rel="stylesheet" href="cursos_escuelas.css">
<div class="cursos-escuelas-dashboard">
  <div class="cursos-escuelas-card">
    <h1 class="cursos-escuelas-title">Gestión de Cursos y Escuelas</h1>
    <p class="cursos-escuelas-desc">Administra las escuelas y cursos registrados</p>
    <div class="cursos-escuelas-actions">
      <button class="cu-btn-primary" id="btnNuevaEscuela">Nueva Escuela</button>
      <button class="cu-btn-secondary" id="btnNuevoCurso">Nuevo Curso</button>
    </div>
    <div class="cu-columns">
      <div class="cu-column-escuelas">
        <h2>Escuelas</h2>
        <table class="cu-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Teléfono</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="escuelasTableBody">
            <?php foreach ($escuelas as $e): ?>
            <tr>
              <td><?= htmlspecialchars($e['nombre_escuela']) ?></td>
              <td><?= htmlspecialchars($e['telefono']) ?></td>
              <td>
                <a href="#" class="action-btn primary editar-escuela" data-id="<?= $e['id_escuela'] ?>">Editar</a>
                <a href="#" class="action-btn secondary eliminar-escuela" data-id="<?= $e['id_escuela'] ?>">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="cu-column-cursos">
        <h2>Cursos</h2>
        <table class="cu-table">
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Escuela</th>
              <th>Profesor</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody id="cursosTableBody">
            <?php foreach ($cursos as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['nombre_curso']) ?></td>
              <td><?= htmlspecialchars($c['nombre_escuela']) ?></td>
              <td><?= htmlspecialchars($c['profesor_nombre'] . ' ' . $c['profesor_apellido']) ?></td>
              <td>
                <a href="#" class="action-btn primary editar-curso" data-id="<?= $c['id_curso'] ?>">Editar</a>
                <a href="#" class="action-btn secondary eliminar-curso" data-id="<?= $c['id_curso'] ?>">Eliminar</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Modales para CRUD -->
    <!-- Modal Nueva Escuela -->
    <div id="modalNuevaEscuela" class="modal">
      <div class="modal-content modal-small">
        <button class="close-modal">&times;</button>
        <h2>Nueva Escuela</h2>
        <form id="formNuevaEscuela" method="post" class="modal-form">
          <label>Nombre de la escuela</label>
          <input type="text" name="nombre_escuela" required class="usuarios-search-input">
          <label>Teléfono</label>
          <input type="text" name="telefono" class="usuarios-search-input">
          <button type="submit" class="cu-btn-primary full-width">Crear Escuela</button>
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
