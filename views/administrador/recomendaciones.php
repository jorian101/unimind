<?php
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../controllers/RecomendacionesController.php';

// Inicializar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['user_role'])) {
    header('Location: /unimind/index.php');
    exit;
}

// Verificar rol de administrador
$role = strtolower($_SESSION['user_role']);
if ($role !== 'administrador' && $role !== 'admin') {
    header('Location: /unimind/index.php');
    exit;
}

// Obtener datos del controlador
$controller = new RecomendacionesController();
$estadisticas = $controller->getEstadisticas();
$recomendaciones = $controller->getRecomendaciones();

renderPageHeader();
?>
<?php
// Construir base URL para enlaces a assets
if (!function_exists('unimind_detect_base')) {
    function unimind_detect_base() {
        $derived = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
        $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');
        $candidates = ['', '/unimind', $derived];
        foreach ($candidates as $c) {
            $swPath = $docroot . ($c === '' ? '' : $c) . '/sw.js';
            if (file_exists($swPath)) {
                return $c;
            }
        }
        return $derived;
    }
}
$baseUrl = unimind_detect_base();
echo '<link rel="stylesheet" href="' . $baseUrl . '/public/css/theme.css">';
echo '<link rel="stylesheet" href="' . $baseUrl . '/views/administrador/recomendaciones.css">';
?>
<!-- Vista de Recomendaciones - Administrador -->
<div class="recomendaciones-container">
  
  <!-- Page Header -->
  <div class="rec-page-header">
    <div class="rec-header-content">
      <h1 class="rec-page-title">
        <i class="fa-solid fa-lightbulb"></i>
        Recomendaciones
      </h1>
      <p class="rec-page-subtitle">Panel de Control - Monitoreo de Bienestar Estudiantil</p>
    </div>
    <button class="rec-new-btn" id="btnNuevaRecomendacion">
      <i class="fa-solid fa-plus"></i>
      Nueva Recomendación
    </button>
  </div>

  <!-- Stats Cards -->
  <div class="rec-stats-grid">
    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-total">
        <span class="rec-stat-number-large"><?php echo $estadisticas['total']; ?></span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Total Recomendaciones</p>
        <p class="rec-stat-sublabel">registradas</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-active">
        <span class="rec-stat-number-large"><?php echo $estadisticas['activas']; ?></span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Activas</p>
        <p class="rec-stat-sublabel">en uso</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-critical">
        <span class="rec-stat-number-large"><?php echo $estadisticas['criticas']; ?></span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Críticas</p>
        <p class="rec-stat-sublabel">urgentes</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-categories">
        <span class="rec-stat-number-large"><?php echo $estadisticas['categorias']; ?></span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Categorías</p>
        <p class="rec-stat-sublabel">diferentes</p>
      </div>
    </div>
  </div>

  <!-- Main Section -->
  <div class="rec-main-section">
    <div class="rec-section-header">
      <h2 class="rec-section-title">Gestión de Recomendaciones</h2>
    </div>

    <!-- Search and Filters -->
    <div class="rec-controls">
      <div class="rec-search-box">
        <i class="fa-solid fa-search"></i>
        <input type="text" id="searchRecomendaciones" placeholder="Buscar recomendaciones..." class="rec-search-input">
      </div>

      <div class="rec-filters">
        <div class="rec-filter-group">
          <i class="fa-solid fa-filter"></i>
          <select id="filterCategoria" class="rec-filter-select">
            <option value="">Todas las categorías</option>
            <option value="mental">Mental</option>
            <option value="profesional">Profesional</option>
            <option value="fisica">Física</option>
            <option value="academica">Académica</option>
            <option value="social">Social</option>
          </select>
        </div>

        <div class="rec-filter-group">
          <i class="fa-solid fa-signal"></i>
          <select id="filterMagnitud" class="rec-filter-select">
            <option value="">Todas las magnitudes</option>
            <option value="1">Nivel 1 - Muy Bajo</option>
            <option value="2">Nivel 2 - Bajo</option>
            <option value="3">Nivel 3 - Medio</option>
            <option value="4">Nivel 4 - Alto</option>
            <option value="5">Nivel 5 - Crítico</option>
          </select>
        </div>
      </div>
    </div>

    <!-- Recommendations Table -->
    <div class="rec-table-wrapper">
      <table class="rec-table" id="recomendacionesTable">
        <thead>
          <tr>
            <th class="th-recomendacion">Recomendación</th>
            <th class="th-categoria">Categoría</th>
            <th class="th-magnitud">Magnitud</th>
            <th class="th-estado">Estado</th>
            <th class="th-acciones">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $iconosCategorias = [
              'mental' => 'fa-spa',
              'profesional' => 'fa-user-doctor',
              'fisica' => 'fa-dumbbell',
              'academica' => 'fa-book-open',
              'social' => 'fa-users'
          ];
          
          $nombresCategorias = [
              'mental' => 'Mental',
              'profesional' => 'Profesional',
              'fisica' => 'Física',
              'academica' => 'Académica',
              'social' => 'Social'
          ];

          $nombresNiveles = [
              1 => 'Nivel 1 - Muy Bajo',
              2 => 'Nivel 2 - Bajo',
              3 => 'Nivel 3 - Medio',
              4 => 'Nivel 4 - Alto',
              5 => 'Nivel 5 - Crítico'
          ];
          
          if (!empty($recomendaciones)):
            foreach ($recomendaciones as $rec): 
              $categoria = $rec['categoria'];
              $icono = $iconosCategorias[$categoria] ?? 'fa-lightbulb';
              $nombreCat = $nombresCategorias[$categoria] ?? ucfirst($categoria);
              $prioridad = (int)$rec['prioridad'];
              $nivelMin = (int)$rec['nivel_minimo'];
              $nivelMax = (int)$rec['nivel_maximo'];
              $activa = (int)$rec['activa'];
              
              // Calcular nivel promedio para la barra
              $nivelPromedio = round(($nivelMin + $nivelMax) / 2);
              $porcentajeBarra = $nivelPromedio * 20; // 20% por nivel
          ?>
          <tr data-id="<?php echo $rec['id_recomendacion']; ?>" 
              data-categoria="<?php echo htmlspecialchars($categoria); ?>" 
              data-magnitud="<?php echo $nivelPromedio; ?>">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid <?php echo $icono; ?> rec-icon-<?php echo $categoria; ?>"></i>
                <div class="rec-cell-text">
                  <strong><?php echo htmlspecialchars($rec['titulo']); ?></strong>
                  <p class="rec-description"><?php echo htmlspecialchars($rec['descripcion']); ?></p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-<?php echo $categoria; ?>">
                <i class="fa-solid fa-brain"></i>
                <?php echo $nombreCat; ?>
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-<?php echo $nivelPromedio; ?>">
                  <div class="rec-magnitud-fill" style="width: <?php echo $porcentajeBarra; ?>%;"></div>
                </div>
                <span class="rec-magnitud-label"><?php echo $nombresNiveles[$nivelMin]; ?> - <?php echo $nombresNiveles[$nivelMax]; ?></span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-<?php echo $activa ? 'active' : 'inactive'; ?>">
                <i class="fa-solid <?php echo $activa ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                <?php echo $activa ? 'Activa' : 'Inactiva'; ?>
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-view" title="Ver" data-id="<?php echo $rec['id_recomendacion']; ?>">
                  <i class="fa-solid fa-eye"></i>
                </button>
                <button class="rec-action-btn rec-btn-edit" title="Editar" data-id="<?php echo $rec['id_recomendacion']; ?>">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar" data-id="<?php echo $rec['id_recomendacion']; ?>">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
          <?php 
            endforeach;
          else:
          ?>
          <tr>
            <td colspan="5" style="text-align: center; padding: 2rem;">
              <p>No hay recomendaciones registradas. Crea una nueva para comenzar.</p>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Empty State (hidden by default) -->
    <div class="rec-empty-state" style="display: none;">
      <i class="fa-solid fa-lightbulb"></i>
      <h3>No hay recomendaciones</h3>
      <p>No se encontraron recomendaciones que coincidan con los filtros seleccionados.</p>
    </div>

  </div>

</div>

<!-- Modal para Nueva/Editar Recomendación -->
<div class="modal" id="modalRecomendacion">
  <div class="modal-content">
    <button class="close-modal" onclick="cerrarModalRecomendacion()">&times;</button>
    <h2 id="modalTitle">Nueva Recomendación</h2>
    <form class="modal-form" id="formRecomendacion">
      <input type="hidden" id="recId" name="id_recomendacion">
      
      <div class="form-group">
        <label for="recTitulo">Título de la Recomendación *</label>
        <input type="text" id="recTitulo" name="titulo" required placeholder="Ej: Sesión de Mindfulness">
      </div>

      <div class="form-group">
        <label for="recDescripcion">Descripción *</label>
        <textarea id="recDescripcion" name="descripcion" rows="4" required placeholder="Describe la recomendación en detalle..."></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="recCategoria">Categoría *</label>
          <select id="recCategoria" name="categoria" required>
            <option value="">Seleccionar...</option>
            <option value="mental">Mental</option>
            <option value="profesional">Profesional</option>
            <option value="fisica">Física</option>
            <option value="academica">Académica</option>
            <option value="social">Social</option>
          </select>
        </div>

        <div class="form-group">
          <label for="recTipoTest">Tipo de Test *</label>
          <select id="recTipoTest" name="tipo_test" required>
            <option value="ambos">Ambos (Estrés y Ansiedad)</option>
            <option value="estres">Solo Estrés</option>
            <option value="ansiedad">Solo Ansiedad</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="recNivelMin">Nivel Mínimo (1-5) *</label>
          <select id="recNivelMin" name="nivel_minimo" required>
            <option value="1">Nivel 1 - Muy Bajo</option>
            <option value="2">Nivel 2 - Bajo</option>
            <option value="3">Nivel 3 - Medio</option>
            <option value="4">Nivel 4 - Alto</option>
            <option value="5">Nivel 5 - Crítico</option>
          </select>
        </div>

        <div class="form-group">
          <label for="recNivelMax">Nivel Máximo (1-5) *</label>
          <select id="recNivelMax" name="nivel_maximo" required>
            <option value="1">Nivel 1 - Muy Bajo</option>
            <option value="2">Nivel 2 - Bajo</option>
            <option value="3" selected>Nivel 3 - Medio</option>
            <option value="4">Nivel 4 - Alto</option>
            <option value="5">Nivel 5 - Crítico</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="recPrioridad">Prioridad (1-5) *</label>
          <select id="recPrioridad" name="prioridad" required>
            <option value="1">1 - Baja</option>
            <option value="2">2 - Media-Baja</option>
            <option value="3" selected>3 - Media</option>
            <option value="4">4 - Alta</option>
            <option value="5">5 - Crítica</option>
          </select>
        </div>

        <div class="form-group">
          <label>
            <input type="checkbox" id="recActiva" name="activa" checked value="1">
            Recomendación activa
          </label>
        </div>
      </div>

      <button type="submit" class="cu-btn-primary full-width">
        <i class="fa-solid fa-save"></i>
        Guardar Recomendación
      </button>
    </form>
  </div>
</div>

<!-- Script para funcionalidad CRUD completa -->
<script>
const API_URL = '<?php echo $baseUrl; ?>/api/recomendaciones.php';
let recomendacionesData = [];

document.addEventListener('DOMContentLoaded', function() {
  // Cargar recomendaciones al inicio
  cargarRecomendaciones();
  
  // Search functionality
  const searchInput = document.getElementById('searchRecomendaciones');
  const filterCategoria = document.getElementById('filterCategoria');
  const filterMagnitud = document.getElementById('filterMagnitud');
  const emptyState = document.querySelector('.rec-empty-state');
  const tableWrapper = document.querySelector('.rec-table-wrapper');

  searchInput.addEventListener('input', filtrarTabla);
  filterCategoria.addEventListener('change', filtrarTabla);
  filterMagnitud.addEventListener('change', filtrarTabla);

  // Modal functionality
  const btnNueva = document.getElementById('btnNuevaRecomendacion');
  const modal = document.getElementById('modalRecomendacion');

  btnNueva.addEventListener('click', abrirModalNueva);

  // Form submission
  document.getElementById('formRecomendacion').addEventListener('submit', guardarRecomendacion);

  // Close modal on outside click
  modal.addEventListener('click', function(e) {
    if (e.target === this) {
      cerrarModalRecomendacion();
    }
  });
});

// ========================================
// FUNCIONES DE CARGA Y FILTRADO
// ========================================

async function cargarRecomendaciones() {
  try {
    const response = await fetch(API_URL);
    const data = await response.json();
    
    if (data.success) {
      recomendacionesData = data.recomendaciones;
      renderizarRecomendaciones(recomendacionesData);
      actualizarEstadisticas();
    } else {
      console.error('Error al cargar recomendaciones:', data.error);
      mostrarNotificacion('Error al cargar recomendaciones', 'error');
    }
  } catch (error) {
    console.error('Error de red:', error);
    mostrarNotificacion('Error de conexión', 'error');
  }
}

function renderizarRecomendaciones(recomendaciones) {
  const tbody = document.querySelector('#recomendacionesTable tbody');
  
  if (!recomendaciones || recomendaciones.length === 0) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 2rem;"><p>No hay recomendaciones registradas.</p></td></tr>';
    return;
  }

  const iconosCategorias = {
    'mental': 'fa-spa',
    'profesional': 'fa-user-doctor',
    'fisica': 'fa-dumbbell',
    'academica': 'fa-book-open',
    'social': 'fa-users'
  };

  const nombresCategorias = {
    'mental': 'Mental',
    'profesional': 'Profesional',
    'fisica': 'Física',
    'academica': 'Académica',
    'social': 'Social'
  };

  const nombresNiveles = {
    1: 'Nivel 1',
    2: 'Nivel 2',
    3: 'Nivel 3',
    4: 'Nivel 4',
    5: 'Nivel 5'
  };

  tbody.innerHTML = recomendaciones.map(rec => {
    const categoria = rec.categoria;
    const icono = iconosCategorias[categoria] || 'fa-lightbulb';
    const nombreCat = nombresCategorias[categoria] || categoria;
    const nivelMin = parseInt(rec.nivel_minimo);
    const nivelMax = parseInt(rec.nivel_maximo);
    const nivelPromedio = Math.round((nivelMin + nivelMax) / 2);
    const porcentajeBarra = nivelPromedio * 20;
    const activa = parseInt(rec.activa);

    return `
      <tr data-id="${rec.id_recomendacion}" 
          data-categoria="${categoria}" 
          data-magnitud="${nivelPromedio}">
        <td>
          <div class="rec-cell-content">
            <i class="fa-solid ${icono} rec-icon-${categoria}"></i>
            <div class="rec-cell-text">
              <strong>${escapeHtml(rec.titulo)}</strong>
              <p class="rec-description">${escapeHtml(rec.descripcion)}</p>
            </div>
          </div>
        </td>
        <td>
          <span class="rec-badge rec-badge-${categoria}">
            <i class="fa-solid fa-brain"></i>
            ${nombreCat}
          </span>
        </td>
        <td>
          <div class="rec-magnitud-cell">
            <div class="rec-magnitud-bar rec-magnitud-${nivelPromedio}">
              <div class="rec-magnitud-fill" style="width: ${porcentajeBarra}%;"></div>
            </div>
            <span class="rec-magnitud-label">${nombresNiveles[nivelMin]} - ${nombresNiveles[nivelMax]}</span>
          </div>
        </td>
        <td>
          <span class="rec-status rec-status-${activa ? 'active' : 'inactive'}">
            <i class="fa-solid ${activa ? 'fa-check-circle' : 'fa-times-circle'}"></i>
            ${activa ? 'Activa' : 'Inactiva'}
          </span>
        </td>
        <td>
          <div class="rec-actions">
            <button class="rec-action-btn rec-btn-view" title="Ver" onclick="verRecomendacion(${rec.id_recomendacion})">
              <i class="fa-solid fa-eye"></i>
            </button>
            <button class="rec-action-btn rec-btn-edit" title="Editar" onclick="editarRecomendacion(${rec.id_recomendacion})">
              <i class="fa-solid fa-pen"></i>
            </button>
            <button class="rec-action-btn rec-btn-delete" title="Eliminar" onclick="eliminarRecomendacion(${rec.id_recomendacion})">
              <i class="fa-solid fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function filtrarTabla() {
  const searchTerm = document.getElementById('searchRecomendaciones').value.toLowerCase();
  const categoriaFilter = document.getElementById('filterCategoria').value;
  const magnitudFilter = document.getElementById('filterMagnitud').value;
  const tableRows = document.querySelectorAll('#recomendacionesTable tbody tr');
  const emptyState = document.querySelector('.rec-empty-state');
  const tableWrapper = document.querySelector('.rec-table-wrapper');
  
  let visibleCount = 0;

  tableRows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const categoria = row.dataset.categoria;
    const magnitud = row.dataset.magnitud;

    const matchSearch = text.includes(searchTerm);
    const matchCategoria = !categoriaFilter || categoria === categoriaFilter;
    const matchMagnitud = !magnitudFilter || magnitud === magnitudFilter;

    if (matchSearch && matchCategoria && matchMagnitud) {
      row.style.display = '';
      visibleCount++;
    } else {
      row.style.display = 'none';
    }
  });

  // Show/hide empty state
  if (visibleCount === 0) {
    tableWrapper.style.display = 'none';
    emptyState.style.display = 'block';
  } else {
    tableWrapper.style.display = 'block';
    emptyState.style.display = 'none';
  }
}

async function actualizarEstadisticas() {
  try {
    const response = await fetch(API_URL + '?stats=true');
    const data = await response.json();
    
    if (data.success) {
      const stats = data.estadisticas;
      document.querySelectorAll('.rec-stat-number-large')[0].textContent = stats.total;
      document.querySelectorAll('.rec-stat-number-large')[1].textContent = stats.activas;
      document.querySelectorAll('.rec-stat-number-large')[2].textContent = stats.criticas;
      document.querySelectorAll('.rec-stat-number-large')[3].textContent = stats.categorias;
    }
  } catch (error) {
    console.error('Error al actualizar estadísticas:', error);
  }
}

// ========================================
// FUNCIONES DE MODAL
// ========================================

function abrirModalNueva() {
  document.getElementById('modalTitle').textContent = 'Nueva Recomendación';
  document.getElementById('formRecomendacion').reset();
  document.getElementById('recId').value = '';
  document.getElementById('recActiva').checked = true;
  document.getElementById('modalRecomendacion').classList.add('active');
}

function verRecomendacion(id) {
  const rec = recomendacionesData.find(r => r.id_recomendacion == id);
  if (!rec) {
    mostrarNotificacion('Recomendación no encontrada', 'error');
    return;
  }

  document.getElementById('modalTitle').textContent = 'Ver Recomendación';
  document.getElementById('recId').value = rec.id_recomendacion;
  document.getElementById('recTitulo').value = rec.titulo;
  document.getElementById('recDescripcion').value = rec.descripcion;
  document.getElementById('recCategoria').value = rec.categoria;
  document.getElementById('recTipoTest').value = rec.tipo_test || 'ambos';
  document.getElementById('recNivelMin').value = rec.nivel_minimo;
  document.getElementById('recNivelMax').value = rec.nivel_maximo;
  document.getElementById('recPrioridad').value = rec.prioridad || 3;
  document.getElementById('recActiva').checked = parseInt(rec.activa) === 1;
  
  // Deshabilitar campos para solo lectura
  document.getElementById('recTitulo').disabled = true;
  document.getElementById('recDescripcion').disabled = true;
  document.getElementById('recCategoria').disabled = true;
  document.getElementById('recTipoTest').disabled = true;
  document.getElementById('recNivelMin').disabled = true;
  document.getElementById('recNivelMax').disabled = true;
  document.getElementById('recPrioridad').disabled = true;
  document.getElementById('recActiva').disabled = true;
  
  // Ocultar botón de guardar
  document.querySelector('#formRecomendacion button[type="submit"]').style.display = 'none';
  
  document.getElementById('modalRecomendacion').classList.add('active');
}

async function editarRecomendacion(id) {
  const rec = recomendacionesData.find(r => r.id_recomendacion == id);
  if (!rec) {
    mostrarNotificacion('Recomendación no encontrada', 'error');
    return;
  }

  document.getElementById('modalTitle').textContent = 'Editar Recomendación';
  document.getElementById('recId').value = rec.id_recomendacion;
  document.getElementById('recTitulo').value = rec.titulo;
  document.getElementById('recDescripcion').value = rec.descripcion;
  document.getElementById('recCategoria').value = rec.categoria;
  document.getElementById('recTipoTest').value = rec.tipo_test || 'ambos';
  document.getElementById('recNivelMin').value = rec.nivel_minimo;
  document.getElementById('recNivelMax').value = rec.nivel_maximo;
  document.getElementById('recPrioridad').value = rec.prioridad || 3;
  document.getElementById('recActiva').checked = parseInt(rec.activa) === 1;
  
  document.getElementById('modalRecomendacion').classList.add('active');
}

function cerrarModalRecomendacion() {
  document.getElementById('modalRecomendacion').classList.remove('active');
  document.getElementById('formRecomendacion').reset();
  
  // Rehabilitar todos los campos
  document.getElementById('recTitulo').disabled = false;
  document.getElementById('recDescripcion').disabled = false;
  document.getElementById('recCategoria').disabled = false;
  document.getElementById('recTipoTest').disabled = false;
  document.getElementById('recNivelMin').disabled = false;
  document.getElementById('recNivelMax').disabled = false;
  document.getElementById('recPrioridad').disabled = false;
  document.getElementById('recActiva').disabled = false;
  
  // Mostrar botón de guardar
  document.querySelector('#formRecomendacion button[type="submit"]').style.display = 'inline-flex';
}

// ========================================
// FUNCIONES DE CRUD
// ========================================

async function guardarRecomendacion(e) {
  e.preventDefault();
  
  const formData = new FormData(e.target);
  const id = document.getElementById('recId').value;
  
  const data = {
    titulo: formData.get('titulo'),
    descripcion: formData.get('descripcion'),
    categoria: formData.get('categoria'),
    tipo_test: formData.get('tipo_test') || 'ambos',
    nivel_minimo: parseInt(formData.get('nivel_minimo')),
    nivel_maximo: parseInt(formData.get('nivel_maximo')),
    prioridad: parseInt(formData.get('prioridad')),
    activa: document.getElementById('recActiva').checked ? 1 : 0
  };

  // Validar niveles
  if (data.nivel_minimo > data.nivel_maximo) {
    mostrarNotificacion('El nivel mínimo no puede ser mayor que el máximo', 'error');
    return;
  }

  try {
    let response;
    if (id) {
      // Actualizar
      response = await fetch(API_URL + '?id=' + id, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
    } else {
      // Crear
      response = await fetch(API_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
    }

    const result = await response.json();
    
    if (result.success) {
      mostrarNotificacion(result.message || 'Operación exitosa', 'success');
      cerrarModalRecomendacion();
      await cargarRecomendaciones();
    } else {
      mostrarNotificacion(result.error || 'Error al guardar', 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error de conexión', 'error');
  }
}

async function eliminarRecomendacion(id) {
  if (!confirm('¿Estás seguro de que deseas eliminar esta recomendación? Esta acción no se puede deshacer.')) {
    return;
  }

  try {
    const response = await fetch(API_URL + '?id=' + id, {
      method: 'DELETE'
    });

    const result = await response.json();
    
    if (result.success) {
      mostrarNotificacion('Recomendación eliminada exitosamente', 'success');
      
      // Animación de eliminación
      const row = document.querySelector(`tr[data-id="${id}"]`);
      if (row) {
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
          cargarRecomendaciones();
        }, 300);
      }
    } else {
      mostrarNotificacion(result.error || 'Error al eliminar', 'error');
    }
  } catch (error) {
    console.error('Error:', error);
    mostrarNotificacion('Error de conexión', 'error');
  }
}

// ========================================
// FUNCIONES AUXILIARES
// ========================================

function escapeHtml(text) {
  const map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };
  return text.replace(/[&<>"']/g, m => map[m]);
}

function mostrarNotificacion(mensaje, tipo = 'info') {
  // Crear elemento de notificación
  const notif = document.createElement('div');
  notif.className = `notification notification-${tipo}`;
  notif.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 1rem 1.5rem;
    background: ${tipo === 'success' ? '#28a745' : tipo === 'error' ? '#dc3545' : '#17a2b8'};
    color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    animation: slideIn 0.3s ease;
    max-width: 400px;
  `;
  notif.textContent = mensaje;
  
  document.body.appendChild(notif);
  
  setTimeout(() => {
    notif.style.animation = 'slideOut 0.3s ease';
    setTimeout(() => notif.remove(), 300);
  }, 3000);
}

// Estilos para animaciones de notificación
const style = document.createElement('style');
style.textContent = `
  @keyframes slideIn {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }
  @keyframes slideOut {
    from {
      transform: translateX(0);
      opacity: 1;
    }
    to {
      transform: translateX(100%);
      opacity: 0;
    }
  }
`;
document.head.appendChild(style);
</script>
