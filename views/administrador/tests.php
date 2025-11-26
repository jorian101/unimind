<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>

<link rel="stylesheet" href="views/administrador/tests.css?v=<?php echo time(); ?>">

<main class="admin-tests-container">
    <div class="page-header">
        <p>Crea, edita y administra los tests psicológicos del sistema</p>
        <button class="btn-primary" id="btnNuevoTest">
            <i class="fas fa-plus"></i> Nuevo Test
        </button>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchTest" name="searchTest" placeholder="Buscar por nombre del test...">
        </div>
        <div class="filter-group">
            <label for="sortTests">Ordenar por:</label>
            <select id="sortTests" name="sortTests">
                <option value="nombre">Nombre</option>
                <option value="num_items">Número de ítems</option>
                <option value="fecha">Fecha de creación</option>
            </select>
        </div>
    </div>

    <!-- Lista de tests -->
    <div class="tests-grid" id="testsGrid">
        <!-- Los tests se cargan dinámicamente -->
        <div class="loading-state">
            <i class="fas fa-spinner fa-spin"></i>
            <p>Cargando tests...</p>
        </div>
    </div>

    <!-- Mensaje cuando no hay tests -->
    <div class="empty-state" id="emptyState" style="display: none;">
        <i class="fas fa-clipboard-list"></i>
        <h3>No hay tests disponibles</h3>
        <p>Comienza creando tu primer test psicológico</p>
        <button class="btn-primary" onclick="document.getElementById('btnNuevoTest').click()">
            <i class="fas fa-plus"></i> Crear Primer Test
        </button>
    </div>
</main>

<!-- Modal para crear/editar test -->
<div class="modal" id="testModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">
                <i class="fas fa-clipboard-list"></i> Nuevo Test
            </h2>
            <button class="modal-close" id="closeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="testForm">
            <input type="hidden" id="testId" name="id_test">

            <!-- Información básica del test -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Información Básica</h3>
                
                <div class="form-group">
                    <label for="nombreTest">
                        <i class="fas fa-file-alt"></i> Nombre del Test *
                    </label>
                    <input type="text" id="nombreTest" name="nombre" required
                           placeholder="Ejemplo: Test de Ansiedad Generalizada GAD-7"
                           minlength="3" maxlength="200">
                    <small class="form-hint">Mínimo 3 caracteres, máximo 200</small>
                </div>

                <div class="form-group">
                    <label for="descripcionTest">
                        <i class="fas fa-align-left"></i> Descripción *
                    </label>
                    <textarea id="descripcionTest" name="descripcion" required rows="3"
                              placeholder="Ejemplo: Este test evalúa el nivel de ansiedad generalizada en los últimos 7 días mediante preguntas sobre síntomas comunes."
                              minlength="10" maxlength="500"></textarea>
                    <small class="form-hint">Describe el objetivo y contenido del test (10-500 caracteres)</small>
                </div>

                <div class="form-group">
                    <label for="tipoEscala">
                        <i class="fas fa-list-check"></i> Tipo de Escala de Respuesta *
                    </label>
                    <select id="tipoEscala" name="tipo_escala" required>
                        <option value="">Selecciona el tipo de escala...</option>
                        <!-- Se cargan dinámicamente desde la BD -->
                    </select>
                    <small class="form-hint">Define qué opciones verán los estudiantes al responder cada pregunta</small>
                </div>

            </div>

            <!-- Ítems del test -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-title-group">
                        <h3><i class="fas fa-list-ol"></i> Ítems del Test</h3>
                        <div class="num-items-badge">
                            <i class="fas fa-hashtag"></i>
                            <span id="numItems">0</span>
                            <span class="badge-label">ítems</span>
                        </div>
                    </div>
                    <button type="button" class="btn-primary" id="btnAgregarItem">
                        <i class="fas fa-plus-circle"></i> Agregar Ítem
                    </button>
                </div>
                <input type="hidden" id="numItemsHidden" name="num_items" value="0">
                <div class="info-box info-box-compact">
                    <i class="fas fa-lightbulb"></i>
                    <p>Cada ítem representa una pregunta o afirmación del test. Agrega al menos 1 ítem para poder guardar.</p>
                </div>

                <div id="itemsContainer">
                    <!-- Los ítems se agregan dinámicamente -->
                </div>

                <div class="empty-items" id="emptyItems">
                    <i class="fas fa-inbox"></i>
                    <p class="empty-title">No hay ítems agregados aún</p>
                    <p class="empty-hint">Haz clic en <strong>"Agregar Ítem"</strong> para crear tu primera pregunta</p>
                </div>
            </div>

            <!-- Opciones de respuesta según escala seleccionada -->
            <div class="form-section" id="opcionesSection" style="display: none;">
                <h3><i class="fas fa-check-square"></i> Opciones de Respuesta para este Test</h3>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Los estudiantes verán estas opciones al responder cada pregunta del test.</p>
                </div>
                <div id="opcionesDisponibles" class="opciones-grid">
                    <!-- Se cargan dinámicamente según el tipo de escala -->
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="btnCancelar">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary" id="btnGuardarTest">
                    <i class="fas fa-save"></i> Guardar Test
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            </div>
            <div class="form-status" id="formStatus" style="display: none;"></div>
        </form>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal" id="deleteModal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2><i class="fas fa-exclamation-triangle"></i> Confirmar Eliminación</h2>
        </div>
        <div class="modal-body">
            <p>¿Estás seguro de que deseas eliminar este test?</p>
            <p class="warning-text">Esta acción no se puede deshacer y se eliminarán todos los ítems asociados.</p>
            <p class="test-name-delete" id="testNameDelete"></p>
        </div>
        <div class="form-actions">
            <button type="button" class="btn-secondary" id="btnCancelarDelete">
                Cancelar
            </button>
            <button type="button" class="btn-danger" id="btnConfirmarDelete">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>
</div>

<script>
// Base path para construcción de URLs
if (!window.UNIMIND_BASE) {
    var pathname = window.location.pathname;
    if (pathname.includes('/unimind/') || pathname.startsWith('/unimind')) {
        window.UNIMIND_BASE = '/unimind';
    } else {
        window.UNIMIND_BASE = '';
    }
}
<?php if (isset($base) && $base): ?>
window.UNIMIND_BASE = '<?php echo $base; ?>';
<?php endif; ?>
</script>
<script src="public/js/idb-wrapper.js?v=<?php echo time(); ?>"></script>
<script src="public/js/admin-tests.js?v=<?php echo time(); ?>"></script>
<script>
// Abrir modal automáticamente si la URL contiene ?nuevo=1
if (window.location.search.includes('nuevo=1')) {
    window.addEventListener('DOMContentLoaded', function() {
        var btnNuevoTest = document.getElementById('btnNuevoTest');
        if (btnNuevoTest) btnNuevoTest.click();
    });
}
</script>
