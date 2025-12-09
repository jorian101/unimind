<?php
// Usar controladores (patrón MVC completo)
require_once __DIR__ . '/../../controllers/EscuelasController.php';
require_once __DIR__ . '/../../controllers/CursosController.php';

$escuelasController = new EscuelasController();
$cursosController = new CursosController();

// Obtener datos desde los controladores
$escuelas = $escuelasController->getEscuelas();
$cursos = $cursosController->getCursos();
$profesores = []; // Los profesores se obtienen via API en el frontend

require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

?>
<?php
// Construir base URL para enlaces a assets (funciona cuando la app se sirve desde un subdirectorio)
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
echo '<link rel="stylesheet" href="' . $baseUrl . '/public/css/theme.css">';
echo '<link rel="stylesheet" href="' . $baseUrl . '/views/administrador/tests.css">';
echo '<link rel="stylesheet" href="' . $baseUrl . '/views/administrador/cursos_escuelas.css">';
?>
<div class="cursos-escuelas-container cursos-escuelas-dashboard">
  <div class="cursos-escuelas-card">
    <div class="cursos-escuelas-actions">
      <button class="cu-btn-primary" id="btnNuevaEscuela">Nueva Escuela</button>
      <button class="cu-btn-secondary" id="btnNuevoCurso">Nuevo Curso</button>
    </div>
    <div class="cu-columns">
      <div class="cu-column-escuelas">
        <div class="section-header">
          <h2>Escuelas</h2>
          <div class="search-filter-group">
            <div class="search-box-inline">
              <i class="fas fa-search"></i>
              <input type="text" id="searchEscuelas" placeholder="Buscar escuela..." class="usuarios-search-input">
            </div>
          </div>
        </div>
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
        <div class="section-header">
          <h2>Cursos</h2>
          <div class="search-filter-group">
            <div class="search-box-inline">
              <i class="fas fa-search"></i>
              <input type="text" id="searchCursos" placeholder="Buscar curso..." class="usuarios-search-input">
            </div>
            <select id="filterEscuela" class="usuarios-search-input filter-select">
              <option value="">Todas las escuelas</option>
              <?php foreach ($escuelas as $esc): ?>
              <option value="<?= htmlspecialchars($esc['nombre_escuela']) ?>"><?= htmlspecialchars($esc['nombre_escuela']) ?></option>
              <?php endforeach; ?>
            </select>
            <select id="filterProfesor" class="usuarios-search-input filter-select">
              <option value="">Todos los profesores</option>
              <?php foreach ($profesores as $p): ?>
              <option value="<?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
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
    <!-- Modal Nuevo Curso -->
    <div id="modalNuevoCurso" class="modal">
      <div class="modal-content modal-small">
        <button class="close-modal">&times;</button>
        <h2>Nuevo Curso</h2>
        <form id="formNuevoCurso" method="post" class="modal-form">
          <label>Nombre del curso</label>
          <input type="text" name="nombre_curso" required class="usuarios-search-input">
          
          <label>Escuela</label>
          <select name="id_escuela" required class="usuarios-search-input">
            <option value="">Selecciona una escuela</option>
            <?php foreach ($escuelas as $esc): ?>
            <option value="<?= htmlspecialchars($esc['id_escuela']) ?>"><?= htmlspecialchars($esc['nombre_escuela']) ?></option>
            <?php endforeach; ?>
          </select>
          
          <label>Profesor</label>
          <select name="id_profesor" required class="usuarios-search-input">
            <option value="">Selecciona un profesor</option>
            <?php foreach ($profesores as $p): ?>
            <option value="<?= htmlspecialchars($p['id_usuario']) ?>"><?= htmlspecialchars($p['nombre'] . ' ' . $p['apellido']) ?></option>
            <?php endforeach; ?>
          </select>
          
          <button type="submit" class="cu-btn-primary full-width">Crear Curso</button>
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

// Abrir modal nuevo curso
const btnNuevoCurso = document.getElementById('btnNuevoCurso');
const modalNuevoCurso = document.getElementById('modalNuevoCurso');
if (btnNuevoCurso && modalNuevoCurso) {
  btnNuevoCurso.onclick = () => { modalNuevoCurso.style.display = 'flex'; };
}

// Enviar nuevo curso
const formNuevoCurso = document.getElementById('formNuevoCurso');
if (formNuevoCurso) {
  formNuevoCurso.onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('crear_curso', '1');
    fetch('api/cursos.php', {
      method: 'POST',
      body: formData
    }).then(res => res.json()).then(data => {
      alert(data.Mensaje || 'Curso creado');
      location.reload();
    }).catch(err => {
      console.error(err);
      alert('Error al crear el curso');
    });
  };
}

// Cerrar modal al hacer clic fuera del contenido (overlay) y con Escape
document.querySelectorAll('.modal').forEach(modal => {
  // Cerrar si se hace clic directamente sobre el overlay (fuera de .modal-content)
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});

// Cerrar con tecla Esc
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape' || e.key === 'Esc') {
    document.querySelectorAll('.modal').forEach(m => { m.style.display = 'none'; });
  }
});

// ========== Búsqueda y filtros en tiempo real ==========

// Función para filtrar tabla de Escuelas
function filterEscuelasTable() {
  const searchTerm = document.getElementById('searchEscuelas').value.toLowerCase();
  const tbody = document.getElementById('escuelasTableBody');
  const rows = tbody.getElementsByTagName('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const nombre = row.cells[0].textContent.toLowerCase();
    const telefono = row.cells[1].textContent.toLowerCase();
    
    if (nombre.includes(searchTerm) || telefono.includes(searchTerm)) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  }
}

// Función para filtrar tabla de Cursos
function filterCursosTable() {
  const searchTerm = document.getElementById('searchCursos').value.toLowerCase();
  const escuelaFilter = document.getElementById('filterEscuela').value.toLowerCase();
  const profesorFilter = document.getElementById('filterProfesor').value.toLowerCase();
  const tbody = document.getElementById('cursosTableBody');
  const rows = tbody.getElementsByTagName('tr');
  
  for (let i = 0; i < rows.length; i++) {
    const row = rows[i];
    const nombre = row.cells[0].textContent.toLowerCase();
    const escuela = row.cells[1].textContent.toLowerCase();
    const profesor = row.cells[2].textContent.toLowerCase();
    
    let matchSearch = nombre.includes(searchTerm) || escuela.includes(searchTerm) || profesor.includes(searchTerm);
    let matchEscuela = !escuelaFilter || escuela.includes(escuelaFilter);
    let matchProfesor = !profesorFilter || profesor.includes(profesorFilter);
    
    if (matchSearch && matchEscuela && matchProfesor) {
      row.style.display = '';
    } else {
      row.style.display = 'none';
    }
  }
}

// Event listeners para búsqueda y filtros
const searchEscuelas = document.getElementById('searchEscuelas');
if (searchEscuelas) {
  searchEscuelas.addEventListener('input', filterEscuelasTable);
}

const searchCursos = document.getElementById('searchCursos');
if (searchCursos) {
  searchCursos.addEventListener('input', filterCursosTable);
}

const filterEscuela = document.getElementById('filterEscuela');
if (filterEscuela) {
  filterEscuela.addEventListener('change', filterCursosTable);
}

const filterProfesor = document.getElementById('filterProfesor');
if (filterProfesor) {
  filterProfesor.addEventListener('change', filterCursosTable);
}

// ...similar para cursos y edición/eliminación...
</script>
