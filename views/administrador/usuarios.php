<script>
// ...existing autocompletado y funciones...

// Al cargar la página, mostrar todos los usuarios
document.addEventListener('DOMContentLoaded', function() {
  buscarUsuariosAjax();
});
</script>
<?php
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
<link rel="stylesheet" href="usuarios.css">
<div class="usuarios-dashboard" style="min-height:100vh; background:#f6f6f6; padding:0; font-family:'Inter',sans-serif;">
  <div class="usuarios-container" style="max-width:98vw; margin:32px auto; padding:0 2vw; background:#fff; border-radius:24px; box-shadow:0 4px 24px #0002;">
    <h1 class="usuarios-title" style="color:#6b1a1a; font-size:2.2rem; font-weight:700; margin-bottom:8px; margin-top:0;">Panel de Usuarios</h1>
    <p class="usuarios-subtitle" style="color:#7c7c7c; margin-bottom:32px; margin-top:0;">Gestión y administración de usuarios</p>

    <div class="usuarios-actions" style="display:flex; gap:24px; margin-bottom:40px; justify-content:center; flex-wrap:wrap;">
      <form method="get" class="usuarios-search-form" style="display:flex; gap:16px; align-items:center; position:relative;">
        <div style="position:relative;">
          <input type="text" name="busqueda" id="busquedaInput" value="<?= htmlspecialchars($busqueda) ?>" autocomplete="off" placeholder="Buscar por nombre, apellido o código" class="usuarios-search-input" style="padding:10px 16px; border-radius:8px; border:1px solid #bdbdbd; font-size:1rem; min-width:220px;">
          <ul id="autocompleteList" style="position:absolute; top:40px; left:0; right:0; background:#fff; border-radius:8px; box-shadow:0 2px 8px #0002; z-index:10; list-style:none; margin:0; padding:0; display:none; max-height:220px; overflow-y:auto;"></ul>
        </div>
        <select name="cargo" id="cargoInput" class="usuarios-filter-select" style="padding:10px 16px; border-radius:8px; border:1px solid #bdbdbd; font-size:1rem;">
          <option value="">Todos los cargos</option>
          <?php foreach ($cargos as $c): ?>
            <option value="<?= $c ?>" <?= $cargo==$c?'selected':'' ?>><?= $c ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary" style="padding:10px 24px; border-radius:8px; background:#6b1a1a; color:#fff; border:none; font-weight:600; cursor:pointer;">Buscar</button>
      </form>
      <script>
      // Búsqueda en tiempo real y autocompletado
      const busquedaInput = document.getElementById('busquedaInput');
      const cargoInput = document.getElementById('cargoInput');
      const autocompleteList = document.getElementById('autocompleteList');
      let timeout = null;

      busquedaInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value.trim();
        const cargo = cargoInput.value;
        if (q.length < 2) {
          autocompleteList.style.display = 'none';
          return;
        }
        timeout = setTimeout(() => {
          fetch(`api/usuarios-buscar.php?q=${encodeURIComponent(q)}&cargo=${encodeURIComponent(cargo)}`)
            .then(res => res.json())
            .then(data => {
              autocompleteList.innerHTML = '';
              if (data.length === 0) {
                autocompleteList.style.display = 'none';
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
                };
                autocompleteList.appendChild(li);
              });
              autocompleteList.style.display = 'block';
            });
        }, 250);
      });

      busquedaInput.addEventListener('blur', function() {
        setTimeout(() => { autocompleteList.style.display = 'none'; }, 150);
      });

      cargoInput.addEventListener('change', function() {
        if (busquedaInput.value.trim().length >= 2) {
          busquedaInput.dispatchEvent(new Event('input'));
        }
      });
      </script>
      <button class="btn btn-secondary" id="btnNuevoUsuario" style="padding:10px 24px; border-radius:8px; background:#fff; color:#6b1a1a; border:1px solid #6b1a1a; font-weight:600; cursor:pointer;">Nuevo Usuario</button>
          <!-- Modal Nuevo Usuario -->
          <div id="modalNuevoUsuario" class="modal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:#0007; z-index:9999; align-items:center; justify-content:center;">
            <div class="modal-content" style="background:linear-gradient(135deg,#fff 80%,#f6f6f6 100%); border-radius:20px; padding:40px 32px 32px 32px; max-width:480px; margin:auto; box-shadow:0 4px 24px #0003; position:relative;">
              <span class="close-modal" style="position:absolute; top:18px; right:24px; font-size:2rem; color:#6b1a1a; cursor:pointer;">&times;</span>
              <h2 style="color:#6b1a1a; margin-bottom:24px; font-size:1.6rem; font-weight:700; text-align:center; letter-spacing:0.5px;">Nuevo Usuario</h2>
              <form id="formNuevoUsuario" method="post" style="display:grid; grid-template-columns:1fr 1fr; gap:18px 24px;">
                <div style="grid-column:1/2;">
                  <label for="nuevo_nombre" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Nombre</label>
                  <input type="text" name="nuevo_nombre" id="nuevo_nombre" placeholder="Nombre" class="usuarios-search-input" required>
                </div>
                <div style="grid-column:2/3;">
                  <label for="nuevo_apellido" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Apellido</label>
                  <input type="text" name="nuevo_apellido" id="nuevo_apellido" placeholder="Apellido" class="usuarios-search-input" required>
                </div>
                <div style="grid-column:1/2;">
                  <label for="nuevo_codigo_usuario" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Código de Usuario</label>
                  <input type="text" name="nuevo_codigo_usuario" id="nuevo_codigo_usuario" placeholder="Código" class="usuarios-search-input" required>
                </div>
                <div style="grid-column:2/3;">
                  <label for="nuevo_cargo" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Cargo</label>
                  <select name="nuevo_cargo" id="nuevo_cargo" class="usuarios-filter-select" required>
                    <option value="Estudiante">Estudiante</option>
                    <option value="Docente">Docente</option>
                    <option value="Administrador">Administrador</option>
                  </select>
                </div>
                <div style="grid-column:1/2;">
                  <label for="nuevo_fecha_nacimiento" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Fecha de Nacimiento</label>
                  <input type="date" name="nuevo_fecha_nacimiento" id="nuevo_fecha_nacimiento" class="usuarios-search-input">
                </div>
                <div style="grid-column:2/3;">
                  <label for="nuevo_genero" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Género</label>
                  <select name="nuevo_genero" id="nuevo_genero" class="usuarios-filter-select">
                    <option value="">Sin especificar</option>
                    <option value="Masculino">Masculino</option>
                    <option value="Femenino">Femenino</option>
                    <option value="Otro">Otro</option>
                  </select>
                </div>
                <div style="grid-column:1/3;">
                  <label for="nuevo_password" style="font-size:0.97rem; color:#6b1a1a; font-weight:500; margin-bottom:4px; display:block;">Contraseña</label>
                  <input type="password" name="nuevo_password" id="nuevo_password" placeholder="Password" class="usuarios-search-input" required>
                </div>
                <div style="grid-column:1/3; margin-top:10px;">
                  <button type="submit" class="btn btn-primary" style="width:100%; font-size:1.08rem; padding:12px 0;">Crear Usuario</button>
                </div>
              </form>
            </div>
          </div>
    <script>
    // Abrir modal nuevo usuario
    document.getElementById('btnNuevoUsuario').onclick = function() {
      document.getElementById('modalNuevoUsuario').style.display = 'flex';
    };
    // Cerrar modal nuevo usuario
    document.querySelectorAll('.close-modal').forEach(btn => {
      btn.onclick = function() {
        btn.closest('.modal').style.display = 'none';
      };
    });
    // Enviar nuevo usuario
    document.getElementById('formNuevoUsuario').onsubmit = function(e) {
      e.preventDefault();
      const formData = new FormData(this);
      formData.append('crear_usuario', '1');
      fetch('api/usuarios.php', {
        method: 'POST',
        body: formData
      }).then(res => res.json()).then(data => {
        alert(data.Mensaje || 'Usuario creado');
        location.reload();
      });
    };
    </script>
    </div>

    <div class="usuarios-table-section" style="background:#fff; border-radius:18px; padding:32px; box-shadow:0 2px 12px #0001;">
      <h2 class="section-title" style="color:#6b1a1a; font-size:1.3rem; margin-bottom:18px; margin-top:0;">Lista de Usuarios</h2>
      <table class="usuarios-table" style="width:100%; border-collapse:separate; border-spacing:0;">
        <thead>
          <tr style="background-color:#f6f6f6; color:#6b1a1a; font-weight:600;">
            <th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Nombre</th>
            <th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Apellido</th>
            <th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Código</th>
            <th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Cargo</th>
            <th style="padding:12px 8px; text-align:left; border-right:2px solid #bdbdbd;">Fecha Registro</th>
            <th style="padding:12px 8px; text-align:left;">Acciones</th>
          </tr>
        </thead>
        <tbody id="usuariosTableBody">
          <!-- La tabla se llenará dinámicamente con JavaScript -->
        </tbody>
      </table>
      <script>
      // ...existing autocompletado script...

      function renderUsuariosTable(usuarios) {
        const tbody = document.getElementById('usuariosTableBody');
        tbody.innerHTML = '';
        if (!usuarios.length) {
          const tr = document.createElement('tr');
          const td = document.createElement('td');
          td.colSpan = 6;
          td.textContent = 'No se encontraron usuarios.';
          td.style.textAlign = 'center';
          tr.appendChild(td);
          tbody.appendChild(tr);
          return;
        }
        usuarios.forEach(u => {
          const tr = document.createElement('tr');
          tr.style.borderBottom = '3px solid #bdbdbd';
          tr.innerHTML = `
            <td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">${u.nombre}</td>
            <td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">${u.apellido}</td>
            <td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">${u.codigo_usuario}</td>
            <td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">${u.cargo}</td>
            <td style="border-right:2px solid #bdbdbd; border-bottom:2px solid #bdbdbd; padding:12px 8px;">${u.fecha_registro ? new Date(u.fecha_registro.replace(' ', 'T')).toLocaleString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : ''}</td>
            <td style="border-bottom:2px solid #bdbdbd; padding:12px 8px;">
              <a href="#" class="btn btn-primary editar-usuario" data-id="${u.id_usuario}" style="padding:6px 14px; border-radius:6px; background:#6b1a1a; color:#fff; border:none; font-size:0.95rem; margin-right:6px;">Editar</a>
              <a href="#" class="btn btn-secondary eliminar-usuario" data-id="${u.id_usuario}" style="padding:6px 14px; border-radius:6px; background:#fff; color:#6b1a1a; border:1px solid #6b1a1a; font-size:0.95rem;">Eliminar</a>
            </td>
          `;
          tbody.appendChild(tr);
        });
        // Reasignar eventos a los nuevos botones
        document.querySelectorAll('.editar-usuario').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            fetch('api/usuarios.php?id=' + id)
              .then(res => res.json())
              .then(data => {
                document.getElementById('editar_id_usuario').value = data.id_usuario;
                document.getElementById('editar_nombre').value = data.nombre;
                document.getElementById('editar_apellido').value = data.apellido;
                document.getElementById('editar_codigo_usuario').value = data.codigo_usuario;
                document.getElementById('editar_cargo').value = data.cargo;
                document.getElementById('editar_fecha_nacimiento').value = data.fecha_nacimiento || '';
                document.getElementById('editar_genero').value = data.genero || '';
                document.getElementById('editar_password').value = '';
                document.getElementById('modalEditarUsuario').style.display = 'flex';
              });
          });
        });
        document.querySelectorAll('.eliminar-usuario').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            const id = this.dataset.id;
            document.getElementById('eliminar_id_usuario').value = id;
            document.getElementById('modalEliminarUsuario').style.display = 'flex';
          });
        });
      }

      function buscarUsuariosAjax() {
        const q = busquedaInput.value.trim();
        const cargo = cargoInput.value;
        fetch(`api/usuarios-buscar.php?q=${encodeURIComponent(q)}&cargo=${encodeURIComponent(cargo)}`)
          .then(res => res.json())
          .then(data => {
            renderUsuariosTable(data);
          });
      }

      busquedaInput.addEventListener('input', function() {
        if (this.value.trim().length >= 2) {
          buscarUsuariosAjax();
        } else {
          buscarUsuariosAjax(); // Muestra todos si vacío
        }
      });

      cargoInput.addEventListener('change', function() {
        buscarUsuariosAjax();
      });

      document.querySelector('.usuarios-search-form').addEventListener('submit', function(e) {
        e.preventDefault();
        buscarUsuariosAjax();
      });
      </script>
    </div>
  </div>
</div>
