<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>
<?php
// Construir base URL para enlaces a assets
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
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
        <span class="rec-stat-number-large">5</span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Total Recomendaciones</p>
        <p class="rec-stat-sublabel">registradas</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-active">
        <span class="rec-stat-number-large">5</span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Activas</p>
        <p class="rec-stat-sublabel">en uso</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-critical">
        <span class="rec-stat-number-large">1</span>
      </div>
      <div class="rec-stat-info">
        <p class="rec-stat-label">Críticas</p>
        <p class="rec-stat-sublabel">urgentes</p>
      </div>
    </div>

    <div class="rec-stat-card">
      <div class="rec-stat-icon rec-stat-categories">
        <span class="rec-stat-number-large">5</span>
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
          <!-- Recomendación 1 -->
          <tr data-categoria="mental" data-magnitud="3">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid fa-spa rec-icon-mental"></i>
                <div class="rec-cell-text">
                  <strong>Sesión de Mindfulness y Respiración</strong>
                  <p class="rec-description">Practica técnicas de respiración profunda durante 10 minutos al día. Enfócate en inhalar por 4 segundos, mantener por 4, y exhalar por 6 segundos.</p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-mental">
                <i class="fa-solid fa-brain"></i>
                Mental
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-3">
                  <div class="rec-magnitud-fill" style="width: 60%;"></div>
                </div>
                <span class="rec-magnitud-label">Nivel 3</span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-active">
                <i class="fa-solid fa-check-circle"></i>
                Activa
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-edit" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Recomendación 2 -->
          <tr data-categoria="profesional" data-magnitud="5">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid fa-user-doctor rec-icon-profesional"></i>
                <div class="rec-cell-text">
                  <strong>Consulta con Psicólogo de Emergencia</strong>
                  <p class="rec-description">Se recomienda agendar una cita de urgencia con el psicólogo del campus. Disponible de lunes a viernes de 8am a 6pm.</p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-profesional">
                <i class="fa-solid fa-user-tie"></i>
                Profesional
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-5">
                  <div class="rec-magnitud-fill" style="width: 100%;"></div>
                </div>
                <span class="rec-magnitud-label">Nivel 5</span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-active">
                <i class="fa-solid fa-check-circle"></i>
                Activa
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-edit" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Recomendación 3 -->
          <tr data-categoria="fisica" data-magnitud="2">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid fa-dumbbell rec-icon-fisica"></i>
                <div class="rec-cell-text">
                  <strong>Actividad Física Regular</strong>
                  <p class="rec-description">Realiza al menos 30 minutos de ejercicio cardiovascular 3 veces por semana. El gimnasio del campus tiene horarios flexibles.</p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-fisica">
                <i class="fa-solid fa-running"></i>
                Física
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-2">
                  <div class="rec-magnitud-fill" style="width: 40%;"></div>
                </div>
                <span class="rec-magnitud-label">Nivel 2</span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-active">
                <i class="fa-solid fa-check-circle"></i>
                Activa
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-edit" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Recomendación 4 -->
          <tr data-categoria="academica" data-magnitud="3">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid fa-book-open rec-icon-academica"></i>
                <div class="rec-cell-text">
                  <strong>Taller de Gestión del Tiempo</strong>
                  <p class="rec-description">Asiste al taller semanal sobre técnicas de organización y priorización de tareas académicas. Se realiza los miércoles a las 4pm.</p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-academica">
                <i class="fa-solid fa-graduation-cap"></i>
                Académica
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-3">
                  <div class="rec-magnitud-fill" style="width: 60%;"></div>
                </div>
                <span class="rec-magnitud-label">Nivel 3</span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-active">
                <i class="fa-solid fa-check-circle"></i>
                Activa
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-edit" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>

          <!-- Recomendación 5 -->
          <tr data-categoria="social" data-magnitud="1">
            <td>
              <div class="rec-cell-content">
                <i class="fa-solid fa-users rec-icon-social"></i>
                <div class="rec-cell-text">
                  <strong>Grupo de Apoyo Estudiantil</strong>
                  <p class="rec-description">Únete a las sesiones de grupo de apoyo donde puedes compartir experiencias con otros estudiantes en situaciones similares.</p>
                </div>
              </div>
            </td>
            <td>
              <span class="rec-badge rec-badge-social">
                <i class="fa-solid fa-user-friends"></i>
                Social
              </span>
            </td>
            <td>
              <div class="rec-magnitud-cell">
                <div class="rec-magnitud-bar rec-magnitud-1">
                  <div class="rec-magnitud-fill" style="width: 20%;"></div>
                </div>
                <span class="rec-magnitud-label">Nivel 1</span>
              </div>
            </td>
            <td>
              <span class="rec-status rec-status-active">
                <i class="fa-solid fa-check-circle"></i>
                Activa
              </span>
            </td>
            <td>
              <div class="rec-actions">
                <button class="rec-action-btn rec-btn-edit" title="Editar">
                  <i class="fa-solid fa-pen"></i>
                </button>
                <button class="rec-action-btn rec-btn-delete" title="Eliminar">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </div>
            </td>
          </tr>
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
          <label for="recMagnitud">Nivel de Magnitud (1-5) *</label>
          <select id="recMagnitud" name="magnitud" required>
            <option value="">Seleccionar...</option>
            <option value="1">Nivel 1 - Muy Bajo</option>
            <option value="2">Nivel 2 - Bajo</option>
            <option value="3">Nivel 3 - Medio</option>
            <option value="4">Nivel 4 - Alto</option>
            <option value="5">Nivel 5 - Crítico</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>
          <input type="checkbox" id="recActiva" name="activa" checked>
          Recomendación activa
        </label>
      </div>

      <button type="submit" class="cu-btn-primary full-width">
        <i class="fa-solid fa-save"></i>
        Guardar Recomendación
      </button>
    </form>
  </div>
</div>

<!-- Script básico para funcionalidad visual -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Search functionality
  const searchInput = document.getElementById('searchRecomendaciones');
  const filterCategoria = document.getElementById('filterCategoria');
  const filterMagnitud = document.getElementById('filterMagnitud');
  const tableRows = document.querySelectorAll('#recomendacionesTable tbody tr');
  const emptyState = document.querySelector('.rec-empty-state');
  const tableWrapper = document.querySelector('.rec-table-wrapper');

  function filterTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const categoriaFilter = filterCategoria.value;
    const magnitudFilter = filterMagnitud.value;
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

  searchInput.addEventListener('input', filterTable);
  filterCategoria.addEventListener('change', filterTable);
  filterMagnitud.addEventListener('change', filterTable);

  // Modal functionality
  const btnNueva = document.getElementById('btnNuevaRecomendacion');
  const modal = document.getElementById('modalRecomendacion');

  btnNueva.addEventListener('click', function() {
    document.getElementById('modalTitle').textContent = 'Nueva Recomendación';
    document.getElementById('formRecomendacion').reset();
    modal.classList.add('active');
  });

  // Edit buttons
  document.querySelectorAll('.rec-btn-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      document.getElementById('modalTitle').textContent = 'Editar Recomendación';
      modal.classList.add('active');
      // Aquí se cargarían los datos de la recomendación
    });
  });

  // Delete buttons
  document.querySelectorAll('.rec-btn-delete').forEach(btn => {
    btn.addEventListener('click', function() {
      if (confirm('¿Estás seguro de que deseas eliminar esta recomendación?')) {
        const row = this.closest('tr');
        row.style.opacity = '0';
        row.style.transform = 'translateX(-20px)';
        setTimeout(() => {
          row.remove();
          filterTable();
        }, 300);
      }
    });
  });

  // Form submission
  document.getElementById('formRecomendacion').addEventListener('submit', function(e) {
    e.preventDefault();
    console.log('Guardando recomendación...');
    cerrarModalRecomendacion();
    // Aquí se enviaría al servidor
  });
});

function cerrarModalRecomendacion() {
  const modal = document.getElementById('modalRecomendacion');
  modal.classList.remove('active');
}

// Close modal on outside click
document.getElementById('modalRecomendacion').addEventListener('click', function(e) {
  if (e.target === this) {
    cerrarModalRecomendacion();
  }
});
</script>
