<?php
/**
 * Vista: Administrador - Gestión de Usuarios
 * Refactorizado con patrón MVC + UserController + UsuariosModel
 */
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();

// Usar el Controller (patrón MVC)
require_once __DIR__ . '/../../controllers/UserController.php';
define('NO_AUTO_HANDLE', true); // Evitar que el controller auto-procese POST

$controller = new UserController();

// Filtros desde GET
$cargo = $_GET['cargo'] ?? '';
$busqueda = $_GET['busqueda'] ?? '';

// Obtener usuarios usando el controller (y este usa el modelo)
$usuarios = $controller->getUsuarios($cargo, $busqueda);

// Obtener cargos disponibles desde el controller
$cargos = $controller->getCargosDisponibles();
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
        <input type="hidden" name="editar_id_usuario" id="editar_id_usuario" value="">
        <input type="hidden" name="editar_codigo_usuario" id="editar_codigo_usuario" value="">
        <div>
          <label for="nuevo_nombre">Nombre</label>
          <input type="text" name="nuevo_nombre" id="nuevo_nombre" placeholder="Nombre" class="usuarios-search-input" required>
        </div>
        <div>
          <label for="nuevo_apellido">Apellido</label>
          <input type="text" name="nuevo_apellido" id="nuevo_apellido" placeholder="Apellido" class="usuarios-search-input" required>
        </div>
        <!-- Código de usuario ahora se genera automáticamente en el servidor -->
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
          <button type="submit" id="modalSubmitButton" class="btn-primary full-width">Crear Usuario</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal Ver Usuario -->
  <div id="modalVerUsuario" class="modal">
    <div class="modal-content modal-ver-usuario">
      <div class="modal-header">
        <h2><i class="fas fa-user"></i> Detalles del Usuario</h2>
        <button class="modal-close close-modal">&times;</button>
      </div>
      <div id="verUsuarioContenido" class="ver-usuario-grid">
        <!-- El contenido se llenará dinámicamente -->
      </div>
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
          if (!data) return alert('No se encontraron datos del usuario');
          // Poblar modal con datos del usuario
          const modal = document.getElementById('modalNuevoUsuario');
          const idInput = document.getElementById('editar_id_usuario');
          const codigoInput = document.getElementById('editar_codigo_usuario');
          const nombre = document.getElementById('nuevo_nombre');
          const apellido = document.getElementById('nuevo_apellido');
          const cargo = document.getElementById('nuevo_cargo');
          const fecha = document.getElementById('nuevo_fecha_nacimiento');
          const genero = document.getElementById('nuevo_genero');
          const password = document.getElementById('nuevo_password');
          const modalTitle = document.getElementById('modalTitle');
          const submitBtn = document.getElementById('modalSubmitButton');

          // Asignar valores
          idInput.value = data.id_usuario || id;
          codigoInput.value = data.codigo_usuario || '';
          nombre.value = data.nombre || '';
          apellido.value = data.apellido || '';
          cargo.value = data.cargo || '';
          fecha.value = data.fecha_nacimiento ? data.fecha_nacimiento.split(' ')[0] : '';
          genero.value = data.genero || '';
          password.value = '';
          // Contraseña opcional al editar
          password.required = false;

          // Deshabilitar cargo para que no se pueda editar (muestra pero no permite cambio)
          cargo.disabled = true;

          // Actualizar título y botón
          modalTitle.innerHTML = '<i class="fas fa-user-edit"></i> Editar Usuario';
          submitBtn.textContent = 'Guardar Cambios';

          // Abrir modal
          modal.classList.add('active');
          
          // Cargar escuelas y cursos (aunque estén deshabilitados, para mostrar valores)
          ensureEscuelasYCursosLoaded();
        })
        .catch(err => { console.error('Error al cargar usuario:', err); alert('Error al cargar datos del usuario'); });
    });
  });
  document.querySelectorAll('.eliminar-usuario').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      // confirmación simple antes de eliminar
      if (!confirm('¿Estás seguro de eliminar este usuario? Esta acción no se puede deshacer.')) return;
      const link = this;
      const originalText = link.textContent;
      link.textContent = 'Eliminando...';
      // preparar datos y enviar petición POST a la API
      const fd = new FormData();
      fd.append('eliminar_id_usuario', id);
      fetch('api/usuarios.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(data => {
          // mostrar mensaje de respuesta (la API devuelve un array/obj con mensaje)
          if (data && (data.Mensaje || data.mensaje || data.message)) {
            alert(data.Mensaje || data.mensaje || data.message);
          } else if (data && data.error) {
            alert('Error: ' + data.error);
          } else {
            alert('Usuario eliminado');
          }
          // refrescar la lista
          try { buscarUsuariosAjax(); } catch (err) { location.reload(); }
        })
        .catch(err => {
          console.error('Error al eliminar usuario', err);
          alert('Error al eliminar usuario');
        })
        .finally(() => { link.textContent = originalText; });
    });
  });
  // Ver (mostrar datos del usuario)
  document.querySelectorAll('.ver-usuario').forEach(btn => {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const id = this.dataset.id;
      fetch('api/usuarios.php?id=' + id)
        .then(res => res.json())
        .then(data => {
          if (!data) return alert('No se encontraron datos del usuario');
          const u = data;
          
          // Formatear fecha de nacimiento
          let fechaNac = 'No especificada';
          if (u.fecha_nacimiento) {
            try {
              const date = new Date(u.fecha_nacimiento);
              fechaNac = date.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
            } catch(e) {
              fechaNac = u.fecha_nacimiento;
            }
          }
          
          // Formatear fecha de registro
          let fechaReg = 'No disponible';
          if (u.fecha_registro) {
            try {
              const date = new Date(u.fecha_registro.replace(' ', 'T'));
              fechaReg = date.toLocaleString('es-ES', { 
                day: '2-digit', 
                month: 'long', 
                year: 'numeric', 
                hour: '2-digit', 
                minute: '2-digit' 
              });
            } catch(e) {
              fechaReg = u.fecha_registro;
            }
          }
          
          // Generar el HTML base con toda la información organizada
          let contenido = `
            <!-- Información Personal -->
            <div class="ver-usuario-section">
              <h3><i class="fas fa-id-card"></i> Información Personal</h3>
              <div class="ver-usuario-field">
                <label>Nombre Completo</label>
                <p>${u.nombre || ''} ${u.apellido || ''}</p>
              </div>
              <div class="ver-usuario-field">
                <label>Código de Usuario</label>
                <p>${u.codigo_usuario || 'No asignado'}</p>
              </div>
              <div class="ver-usuario-field">
                <label>Género</label>
                <p>${u.genero || 'No especificado'}</p>
              </div>
              <div class="ver-usuario-field">
                <label>Fecha de Nacimiento</label>
                <p>${fechaNac}</p>
              </div>
            </div>
            
            <!-- Información del Sistema -->
            <div class="ver-usuario-section">
              <h3><i class="fas fa-cog"></i> Información del Sistema</h3>
              <div class="ver-usuario-field">
                <label>ID de Usuario</label>
                <p>${u.id_usuario || 'No disponible'}</p>
              </div>
              <div class="ver-usuario-field">
                <label>Cargo</label>
                <p><span class="ver-usuario-badge cargo">${u.cargo || 'No asignado'}</span></p>
              </div>
              <div class="ver-usuario-field">
                <label>Fecha de Registro</label>
                <p>${fechaReg}</p>
              </div>
            </div>
            
            <!-- Información Académica (si aplica) -->
            ${(u.escuela_nombre || u.curso_nombre) ? `
            <div class="ver-usuario-section ver-usuario-full">
              <h3><i class="fas fa-graduation-cap"></i> Información Académica</h3>
              ${u.escuela_nombre ? `
              <div class="ver-usuario-field">
                <label>Escuela</label>
                <p>${u.escuela_nombre}</p>
              </div>
              ` : ''}
              ${u.curso_nombre ? `
              <div class="ver-usuario-field">
                <label>Curso</label>
                <p>${u.curso_nombre}</p>
              </div>
              ` : ''}
            </div>
            ` : ''}
            
            <!-- Información de Contacto -->
            <div class="ver-usuario-section ver-usuario-full">
              <h3><i class="fas fa-envelope"></i> Información de Contacto</h3>
              <div class="ver-usuario-field">
                <label>Correo Electrónico</label>
                <p>${u.email || '<span class="empty">No registrado</span>'}</p>
              </div>
            </div>
          `;
          
          // Mostrar en modal
          const modal = document.getElementById('modalVerUsuario');
          const contenidoDiv = document.getElementById('verUsuarioContenido');
          contenidoDiv.innerHTML = contenido;
          modal.classList.add('active');
          
          // Si es estudiante, cargar métricas
          if (u.cargo === 'Estudiante') {
            cargarMetricasEstudiante(u.id_usuario, contenidoDiv);
          }
        })
        .catch(err => { console.error('Error al cargar usuario:', err); alert('Error al cargar datos del usuario'); });
    });
  });

  // Función para cargar y mostrar métricas de estudiante
  function cargarMetricasEstudiante(idEstudiante, contenidoDiv) {
    fetch(`api/estudiante-metrics.php?id_estudiante=${idEstudiante}`)
      .then(res => res.json())
      .then(result => {
        if (!result.success || !result.data.resumen.tiene_datos) {
          // Agregar mensaje de no datos
          const noDataHTML = `
            <div class="ver-usuario-section metrics-section ver-usuario-full">
              <h3><i class="fas fa-chart-line"></i> Métricas de Salud Mental</h3>
              <div class="no-metrics-message">
                <i class="fas fa-info-circle" style="font-size: 2rem; color: var(--var-700); margin-bottom: 0.5rem;"></i>
                <p>Este estudiante aún no ha realizado evaluaciones de estrés o ansiedad.</p>
              </div>
            </div>
          `;
          contenidoDiv.innerHTML += noDataHTML;
          return;
        }
        
        const metrics = result.data;
        
        // Generar HTML para las métricas
        const metricsHTML = `
          <div class="ver-usuario-section metrics-section ver-usuario-full">
            <h3><i class="fas fa-chart-line"></i> Métricas de Salud Mental</h3>
            
            <div class="metrics-grid">
              <!-- Métrica de Estrés -->
              <div class="metric-card">
                <div class="metric-header">
                  <span class="metric-title">
                    <i class="fas fa-chart-bar"></i> Estrés Actual
                  </span>
                </div>
                <div class="metric-value estres">${metrics.estres.actual}%</div>
                <span class="metric-level ${metrics.estres.nivel.toLowerCase()}">${metrics.estres.nivel}</span>
                <div class="metric-bar">
                  <div class="metric-bar-fill estres" style="width: ${metrics.estres.actual}%"></div>
                </div>
                <div class="metric-comparison">
                  <span>Promedio anterior: ${metrics.estres.promedio}%</span>
                  <span class="metric-trend ${metrics.estres.tendencia}">
                    ${metrics.estres.tendencia === 'mejorando' ? '<i class="fas fa-arrow-down"></i> Mejorando' : 
                      metrics.estres.tendencia === 'aumentando' ? '<i class="fas fa-arrow-up"></i> Aumentando' : 
                      '<i class="fas fa-minus"></i> Estable'}
                  </span>
                </div>
                ${metrics.estres.historico.length > 0 ? `
                <div class="metric-history">
                  ${metrics.estres.historico.map(h => `
                    <div class="history-bar estres" style="height: ${h.puntuacion}%">
                      <div class="history-bar-tooltip">
                        ${h.puntuacion}% - ${new Date(h.fecha).toLocaleDateString('es-ES', {day: '2-digit', month: 'short'})}
                      </div>
                    </div>
                  `).join('')}
                </div>
                ` : ''}
              </div>
              
              <!-- Métrica de Ansiedad -->
              <div class="metric-card">
                <div class="metric-header">
                  <span class="metric-title">
                    <i class="fas fa-brain"></i> Ansiedad Actual
                  </span>
                </div>
                <div class="metric-value ansiedad">${metrics.ansiedad.actual}%</div>
                <span class="metric-level ${metrics.ansiedad.nivel.toLowerCase()}">${metrics.ansiedad.nivel}</span>
                <div class="metric-bar">
                  <div class="metric-bar-fill ansiedad" style="width: ${metrics.ansiedad.actual}%"></div>
                </div>
                <div class="metric-comparison">
                  <span>Promedio anterior: ${metrics.ansiedad.promedio}%</span>
                  <span class="metric-trend ${metrics.ansiedad.tendencia}">
                    ${metrics.ansiedad.tendencia === 'mejorando' ? '<i class="fas fa-arrow-down"></i> Mejorando' : 
                      metrics.ansiedad.tendencia === 'aumentando' ? '<i class="fas fa-arrow-up"></i> Aumentando' : 
                      '<i class="fas fa-minus"></i> Estable'}
                  </span>
                </div>
                ${metrics.ansiedad.historico.length > 0 ? `
                <div class="metric-history">
                  ${metrics.ansiedad.historico.map(h => `
                    <div class="history-bar ansiedad" style="height: ${h.puntuacion}%">
                      <div class="history-bar-tooltip">
                        ${h.puntuacion}% - ${new Date(h.fecha).toLocaleDateString('es-ES', {day: '2-digit', month: 'short'})}
                      </div>
                    </div>
                  `).join('')}
                </div>
                ` : ''}
              </div>
            </div>
            
            <!-- Resumen -->
            <div class="metrics-summary">
              <div class="summary-item">
                <div class="summary-value">${metrics.resumen.total_tests}</div>
                <div class="summary-label">Tests Realizados</div>
              </div>
              <div class="summary-item">
                <div class="summary-value">${metrics.resumen.dias_ultimo_test}</div>
                <div class="summary-label">Días desde último test</div>
              </div>
              <div class="summary-item">
                <div class="summary-value">
                  ${metrics.estres.historico.length + metrics.ansiedad.historico.length}
                </div>
                <div class="summary-label">Evaluaciones registradas</div>
              </div>
            </div>
          </div>
        `;
        
        contenidoDiv.innerHTML += metricsHTML;
        
        // Animar las barras después de agregar al DOM
        setTimeout(() => {
          const barFills = contenidoDiv.querySelectorAll('.metric-bar-fill');
          barFills.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
              bar.style.width = width;
            }, 100);
          });
        }, 50);
      })
      .catch(err => {
        console.error('Error al cargar métricas:', err);
        // Agregar mensaje de error
        const errorHTML = `
          <div class="ver-usuario-section metrics-section ver-usuario-full">
            <h3><i class="fas fa-chart-line"></i> Métricas de Salud Mental</h3>
            <div class="no-metrics-message">
              <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: var(--pri-500); margin-bottom: 0.5rem;"></i>
              <p>No se pudieron cargar las métricas. Intente nuevamente más tarde.</p>
            </div>
          </div>
        `;
        contenidoDiv.innerHTML += errorHTML;
      });
  }
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
  resetUsuarioModal();
  document.getElementById('modalNuevoUsuario').classList.add('active');
  // Ensure selects are loaded and visibility set when opening
  ensureEscuelasYCursosLoaded();
});
// Cerrar modal
document.querySelectorAll('.close-modal').forEach(btn => btn.addEventListener('click', function() {
  const modal = this.closest('.modal');
  if (modal) modal.classList.remove('active');
  resetUsuarioModal();
}));
// Cerrar modal al hacer clic fuera
document.querySelectorAll('.modal').forEach(modal => {
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.classList.remove('active');
      resetUsuarioModal();
    }
  });
});

// Enviar nuevo usuario o editar usuario existente
document.getElementById('formNuevoUsuario') && document.getElementById('formNuevoUsuario').addEventListener('submit', function(e) {
  e.preventDefault();
  const form = this;
  const editarId = document.getElementById('editar_id_usuario').value;
  let formData;
  
  if (editarId) {
    // Modo edición: crear FormData limpio con solo los campos editables
    console.log('Modo EDICIÓN - ID:', editarId);
    formData = new FormData();
    formData.append('editar_id_usuario', editarId);
    formData.append('editar_nombre', document.getElementById('nuevo_nombre').value);
    formData.append('editar_apellido', document.getElementById('nuevo_apellido').value);
    formData.append('editar_codigo_usuario', document.getElementById('editar_codigo_usuario').value);
    formData.append('editar_cargo', document.getElementById('nuevo_cargo').value);
    formData.append('editar_fecha_nacimiento', document.getElementById('nuevo_fecha_nacimiento').value);
    formData.append('editar_genero', document.getElementById('nuevo_genero').value);
    const pw = document.getElementById('nuevo_password').value;
    if (pw) formData.append('editar_password', pw);
    console.log('FormData edición:', Array.from(formData.entries()));
  } else {
    // Modo creación: usar FormData original del formulario
    console.log('Modo CREACIÓN');
    formData = new FormData(form);
    // Remover campos de edición que no deben enviarse al crear
    formData.delete('editar_id_usuario');
    formData.delete('editar_codigo_usuario');
    formData.append('crear_usuario', '1');
    console.log('FormData creación:', Array.from(formData.entries()));
  }

  fetch('api/usuarios.php', { method: 'POST', body: formData })
    .then(res => {
      console.log('Respuesta status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('Respuesta del servidor:', data);
      if (data.error) {
        alert('Error: ' + (data.error + (data.message ? ' - ' + data.message : '')));
      } else {
        alert(data.Mensaje || data.mensaje || data.message || (editarId ? 'Usuario actualizado' : 'Usuario creado'));
        buscarUsuariosAjax();
        const modal = document.getElementById('modalNuevoUsuario');
        if (modal) modal.classList.remove('active');
        resetUsuarioModal();
      }
    })
    .catch(err => { console.error('Error en fetch:', err); alert('Error al procesar usuario: ' + err.message); });
});

// Función para resetear el modal al estado de crear
function resetUsuarioModal() {
  console.log('Reseteando modal a modo CREAR');
  const form = document.getElementById('formNuevoUsuario');
  if (!form) return;
  form.reset();
  document.getElementById('editar_id_usuario').value = '';
  document.getElementById('editar_codigo_usuario').value = '';
  document.getElementById('modalTitle').innerHTML = '<i class="fas fa-user-plus"></i> Nuevo Usuario';
  document.getElementById('modalSubmitButton').textContent = 'Crear Usuario';
  const pw = document.getElementById('nuevo_password');
  if (pw) pw.required = true;
  // Habilitar cargo de nuevo
  const cargo = document.getElementById('nuevo_cargo');
  if (cargo) cargo.disabled = false;
  // Restaurar visibilidad de campos según cargo
  try { ensureEscuelasYCursosLoaded(); } catch (e) {}
}

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
