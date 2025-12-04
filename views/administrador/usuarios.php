<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

require_once __DIR__ . '/../../database/Database.php';
$db = new Database();
$conn = $db->connect();

// Filtros
$cargo = isset($_GET['cargo']) ? $_GET['cargo'] : '';
$busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

// Consulta base
$sql = "SELECT * FROM Usuarios WHERE 1";
$params = [];
if ($cargo && in_array($cargo, ['Estudiante','Docente','Administrador'])) {
    $sql .= " AND cargo = ?";
    $params[] = $cargo;
}
if ($busqueda) {
    $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR codigo_usuario LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}
$sql .= " ORDER BY fecha_registro DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Cargos para filtro
$cargos = ['Estudiante','Docente','Administrador'];
?>
<?php require_once __DIR__ . '/../../utils/asset-version.php'; ?>
<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="public/css/style.css?v=<?php echo asset_version('public/css/style.css'); ?>">
<link rel="stylesheet" href="views/administrador/usuarios.css?v=<?php echo asset_version('views/administrador/usuarios.css'); ?>">

<main class="admin-users-container">
  <div class="page-header">
    <p>Gestión y administración de usuarios del sistema</p>
    <button class="btn-primary" id="btnNuevoUsuario">
      <i class="fas fa-plus"></i> Nuevo Usuario
    </button>
  </div>
  <div style="display:inline-block; margin-left:12px; vertical-align: middle;">
    <button class="btn-secondary" id="btnMostrarTodos" title="Mostrar todos los usuarios">Mostrar Todos</button>
  </div>

  <!-- Filtros y búsqueda -->
  <div class="filters-section">
    <div class="search-box">
      <i class="fas fa-search"></i>
      <input type="text" name="busqueda" id="busquedaInput" value="<?= htmlspecialchars($busqueda) ?>" autocomplete="off" placeholder="Buscar por nombre, apellido o código" class="usuarios-search-input">
      <ul id="autocompleteList" class="autocomplete-list"></ul>
    </div>
    <div class="filter-group">
      <label for="cargoInput">Filtrar por cargo:</label>
      <select name="cargo" id="cargoInput" class="usuarios-filter-select">
        <option value="">Todos los cargos</option>
        <?php foreach ($cargos as $c): ?>
          <option value="<?= $c ?>" <?= $cargo==$c?'selected':'' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <!-- Lista de usuarios -->
  <div class="usuarios-table-section">
    <h2 class="section-title"><i class="fas fa-users"></i> Lista de Usuarios</h2>
    <div class="table-wrapper">
      <table class="usuarios-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Código</th>
            <th>Cargo</th>
            <th>Fecha Registro</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="usuariosTableBody">
          <!-- La tabla se llenará dinámicamente con JavaScript -->
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal Nuevo Usuario -->
  <div id="modalNuevoUsuario" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="modalTitle"><i class="fas fa-user-plus"></i> Nuevo Usuario</h2>
        <button class="modal-close close-modal">&times;</button>
      </div>
      <form id="formNuevoUsuario" method="post" class="modal-form">
        <div>
          <label for="nuevo_nombre">Nombre</label>
          <input type="text" name="nuevo_nombre" id="nuevo_nombre" placeholder="Nombre" class="usuarios-search-input" required>
        </div>
        <div>
          <label for="nuevo_apellido">Apellido</label>
          <input type="text" name="nuevo_apellido" id="nuevo_apellido" placeholder="Apellido" class="usuarios-search-input" required>
        </div>
        <div>
          <label for="nuevo_codigo_usuario">Código de Usuario</label>
          <input type="text" name="nuevo_codigo_usuario" id="nuevo_codigo_usuario" placeholder="Código" class="usuarios-search-input" required>
        </div>
        <div>
          <label for="nuevo_cargo">Cargo</label>
          <select name="nuevo_cargo" id="nuevo_cargo" class="usuarios-filter-select" required>
            <option value="Estudiante">Estudiante</option>
            <option value="Docente">Docente</option>
            <option value="Administrador">Administrador</option>
          </select>
        </div>
        <div class="escuela-field hidden">
          <label for="nuevo_escuela">Escuela</label>
          <select name="nuevo_escuela" id="nuevo_escuela" class="usuarios-filter-select">
            <option value="">Seleccione una escuela</option>
          </select>
        </div>
        <div class="curso-field hidden">
          <label for="nuevo_curso">Curso</label>
          <select name="nuevo_curso" id="nuevo_curso" class="usuarios-filter-select">
            <option value="">Seleccione un curso</option>
          </select>
        </div>
        <div>
          <label for="nuevo_fecha_nacimiento">Fecha de Nacimiento</label>
          <input type="date" name="nuevo_fecha_nacimiento" id="nuevo_fecha_nacimiento" class="usuarios-search-input">
        </div>
        <div>
          <label for="nuevo_genero">Género</label>
          <select name="nuevo_genero" id="nuevo_genero" class="usuarios-filter-select">
            <option value="">Sin especificar</option>
            <option value="Masculino">Masculino</option>
            <option value="Femenino">Femenino</option>
            <option value="Otro">Otro</option>
          </select>
        </div>
        <div class="full-row">
          <label for="nuevo_password">Contraseña</label>
          <input type="password" name="nuevo_password" id="nuevo_password" placeholder="Password" class="usuarios-search-input" required>
        </div>
        <div class="full-row" style="margin-top:6px;">
          <button type="submit" class="btn-primary full-width">Crear Usuario</button>
        </div>
      </form>
    </div>
  </div>

</main>

<script>
// Búsqueda en tiempo real y autocompletado
const busquedaInput = document.getElementById('busquedaInput');
const cargoInput = document.getElementById('cargoInput');
const autocompleteList = document.getElementById('autocompleteList');
let timeout = null;

function renderUsuariosTable(usuarios) {
  const tbody = document.getElementById('usuariosTableBody');
  tbody.innerHTML = '';
  if (!usuarios || usuarios.length === 0) {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 7;
    td.textContent = 'No se encontraron usuarios.';
    td.style.textAlign = 'center';
    tr.appendChild(td);
    tbody.appendChild(tr);
    return;
  }
  usuarios.forEach((u, idx) => {
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>${idx + 1}</td>
      <td>${u.nombre}</td>
      <td>${u.apellido}</td>
      <td>${u.codigo_usuario}</td>
      <td>${u.cargo}</td>
      <td>${u.fecha_registro ? new Date(u.fecha_registro.replace(' ', 'T')).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''}</td>
      <td>
        <a href="#" class="action-btn tertiary ver-usuario" data-id="${u.id_usuario}">Ver</a>
        <a href="#" class="action-btn primary editar-usuario" data-id="${u.id_usuario}">Editar</a>
        <a href="#" class="action-btn secondary eliminar-usuario" data-id="${u.id_usuario}">Eliminar</a>
      </td>
    `;
    tbody.appendChild(tr);
  });
  // asignar eventos a botones generados
  document.querySelectorAll('.editar-usuario').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      fetch('api/usuarios.php?id=' + id)
        .then(res => res.json())
        .then(data => {
          // abrir modal de edición (si existe) — se dejan campos para implementar
          console.log('Editar usuario', data);
        });
    });
  });
  document.querySelectorAll('.eliminar-usuario').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      // mostrar modal de confirmación (implementar si hace falta)
      console.log('Eliminar usuario', id);
    });
  });
  // Ver (sin funcionalidad por ahora)
  document.querySelectorAll('.ver-usuario').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      // placeholder: mostrar detalles eventualmente
      console.log('Ver usuario', id);
    });
  });
}

busquedaInput && busquedaInput.addEventListener('input', function() {
  clearTimeout(timeout);
  const q = this.value.trim();
  const cargo = cargoInput ? cargoInput.value : '';
  if (q.length < 2) {
    // si es vacío, cargar todos
    timeout = setTimeout(() => { buscarUsuariosAjax(); }, 150);
    return;
  }
  timeout = setTimeout(() => {
    fetch(`api/usuarios-buscar.php?q=${encodeURIComponent(q)}&cargo=${encodeURIComponent(cargo)}`)
      .then(res => res.json())
      .then(data => {
        autocompleteList.innerHTML = '';
        if (!Array.isArray(data) || data.length === 0) {
          autocompleteList.style.display = 'none';
          renderUsuariosTable(Array.isArray(data) ? data : []);
          return;
        }
        data.forEach(u => {
          const li = document.createElement('li');
          li.textContent = `${u.nombre} ${u.apellido} (${u.codigo_usuario}) - ${u.cargo}`;
          li.style.padding = '10px 16px';
          li.style.cursor = 'pointer';
          li.onmousedown = function() {
            busquedaInput.value = `${u.nombre} ${u.apellido}`;
            autocompleteList.style.display = 'none';
            buscarUsuariosAjax();
          };
          autocompleteList.appendChild(li);
        });
        autocompleteList.style.display = 'block';
        renderUsuariosTable(data);
      });
  }, 250);
});

busquedaInput && busquedaInput.addEventListener('blur', function() { setTimeout(() => { autocompleteList.style.display = 'none'; }, 150); });
cargoInput && cargoInput.addEventListener('change', function() { buscarUsuariosAjax(); });

function buscarUsuariosAjax() {
  const q = busquedaInput ? busquedaInput.value.trim() : '';
  const cargo = cargoInput ? cargoInput.value : '';
  fetch(`api/usuarios-buscar.php?q=${encodeURIComponent(q)}&cargo=${encodeURIComponent(cargo)}`)
    .then(res => res.json())
    .then(data => {
      renderUsuariosTable(Array.isArray(data) ? data : []);
    });
}

// Abrir modal nuevo usuario
document.getElementById('btnNuevoUsuario') && document.getElementById('btnNuevoUsuario').addEventListener('click', function() {
  document.getElementById('modalNuevoUsuario').classList.add('active');
  // Ensure selects are loaded and visibility set when opening
  ensureEscuelasYCursosLoaded();
});
// Cerrar modal
document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', function() { this.closest('.modal').classList.remove('active'); }));
// Cerrar modal al hacer clic fuera
document.querySelectorAll('.modal').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === modal) modal.classList.remove('active');
  });
});

// Enviar nuevo usuario
document.getElementById('formNuevoUsuario') && document.getElementById('formNuevoUsuario').addEventListener('submit', function(e) {
  e.preventDefault();
  const formData = new FormData(this);
  formData.append('crear_usuario', '1');
  fetch('api/usuarios.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => { alert(data.Mensaje || 'Usuario creado'); buscarUsuariosAjax(); document.getElementById('modalNuevoUsuario').classList.remove('active'); })
    .catch(err => { console.error(err); alert('Error al crear usuario'); });
});

// Mostrar todos - limpia filtros y carga todos los usuarios
document.getElementById('btnMostrarTodos') && document.getElementById('btnMostrarTodos').addEventListener('click', function() {
  if (busquedaInput) busquedaInput.value = '';
  if (cargoInput) cargoInput.value = '';
  // hide autocomplete
  if (autocompleteList) autocompleteList.style.display = 'none';
  buscarUsuariosAjax();
});
// --- New: load escuelas and cursos and show selects when cargo requires them ---
let escuelasCache = null;
let cursosCache = {}; // cache courses per escuela_id: { '<id>': [courses] }

function populateSelect(selectEl, items, valueKey = 'id', labelKey = 'nombre') {
  if (!selectEl) return;
  selectEl.innerHTML = '<option value="">Seleccione</option>';
  items.forEach(it => {
    const opt = document.createElement('option');
    opt.value = it[valueKey];
    opt.textContent = it[labelKey];
    selectEl.appendChild(opt);
  });
}

function ensureEscuelasYCursosLoaded() {
  const cargoEl = document.getElementById('nuevo_cargo');
  const escuelaEl = document.getElementById('nuevo_escuela');
  const cursoEl = document.getElementById('nuevo_curso');

  // Show/hide fields based on cargo
  function toggleFields() {
    const val = cargoEl.value;
    const needExtra = (val === 'Estudiante' || val === 'Docente');
    document.querySelectorAll('.escuela-field, .curso-field').forEach(el => {
      if (needExtra) el.classList.remove('hidden'); else el.classList.add('hidden');
    });
  }

  // Load escuelas if needed
  if (!escuelasCache) {
    fetch('api/escuelas.php')
      .then(r => r.json())
      .then(data => {
        escuelasCache = Array.isArray(data) ? data : [];
        populateSelect(escuelaEl, escuelasCache, 'id_escuela', 'nombre_escuela');
        // si hay escuelas, seleccionar la primera por defecto
        if (escuelasCache.length > 0 && escuelaEl && !escuelaEl.value) {
          escuelaEl.value = escuelasCache[0].id_escuela;
        }
        // Después de poblar el select, disparar el change para precargar cursos
        try {
          const ev = new Event('change');
          escuelaEl && escuelaEl.dispatchEvent(ev);
        } catch (e) { /* silencioso */ }
      })
      .catch(() => { escuelasCache = []; });
  } else {
    populateSelect(escuelaEl, escuelasCache, 'id_escuela', 'nombre_escuela');
    if (escuelasCache.length > 0 && escuelaEl && !escuelaEl.value) {
      escuelaEl.value = escuelasCache[0].id_escuela;
    }
    // Si ya teníamos cache, también disparar el change para precargar cursos
    try {
      const ev = new Event('change');
      escuelaEl && escuelaEl.dispatchEvent(ev);
    } catch (e) { /* silencioso */ }
  }

  // Helper to load cursos for a given escuela id and populate the curso select
  function loadCursosForEscuela(id) {
    if (!cursoEl) return;
    if (!id) {
      populateSelect(cursoEl, [], 'id_curso', 'nombre');
      return;
    }
    if (cursosCache[id]) {
      populateSelect(cursoEl, cursosCache[id], 'id_curso', 'nombre');
      return;
    }
    fetch(`api/cursos.php?escuela_id=${encodeURIComponent(id)}`)
      .then(r => r.json())
      .then(data => {
        const list = Array.isArray(data) ? data : [];
        cursosCache[id] = list;
        populateSelect(cursoEl, list, 'id_curso', 'nombre');
      })
      .catch(() => { populateSelect(cursoEl, [], 'id_curso', 'nombre'); });
  }

  // Attach listeners (safe to call multiple times)
  if (!window._usuarios_listeners_attached) {
    if (escuelaEl) {
      escuelaEl.addEventListener('change', function() { loadCursosForEscuela(this.value); });
    }
    if (cargoEl) {
      cargoEl.addEventListener('change', toggleFields);
    }
    // mark globally to avoid duplicate listeners across re-invocations
    window._usuarios_listeners_attached = true;
  }

  // Immediately load courses for the selected (or first) escuela
  setTimeout(() => {
    if (escuelaEl) {
      const sel = escuelaEl.value || (escuelasCache && escuelasCache.length ? escuelasCache[0].id_escuela : '');
      if (sel) loadCursosForEscuela(sel);
    }
  }, 120);

  // Set initial visibility
  toggleFields();
}

// Cargar inicialmente
document.addEventListener('DOMContentLoaded', function() { buscarUsuariosAjax(); });
</script>
