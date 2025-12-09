<?php
require_once dirname(__DIR__) . '/pageHeader.php';
require_once __DIR__ . '/../../utils/asset-version.php';
renderPageHeader();
?>

<link rel="stylesheet" href="public/css/theme.css?v=<?php echo asset_version('public/css/theme.css'); ?>">
<link rel="stylesheet" href="views/administrador/tests.css?v=<?php echo time(); ?>">

<main class="admin-tests-container">
    <section class="tests-card">
        <h2 class="tests-title">Administrar Tests</h2>
        <p class="tests-subtitle">Crea, edita y administra los tests psicológicos del sistema</p>
        
        <div class="tests-actions">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="searchTest" name="searchTest" placeholder="Buscar por nombre del test...">
            </div>
            <button class="btn-primary" id="btnNuevoTest">
                <i class="fas fa-plus"></i> <span>Nuevo Test</span>
            </button>
        </div>

        <div class="tests-table-container" id="testsGrid">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i> Cargando tests...
            </div>
        </div>

        <div class="empty-state" id="emptyState" style="display: none; margin-top:1rem;">
            <i class="fas fa-clipboard-list"></i>
            <h3>No hay tests disponibles</h3>
            <p>Comienza creando tu primer test psicológico</p>
            <button class="btn-primary" onclick="document.getElementById('btnNuevoTest').click()">
                <i class="fas fa-plus"></i> Crear Primer Test
            </button>
        </div>
    </section>
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
                    <div style="display:flex;gap:0.5rem;align-items:center;">
                      <select id="tipoEscala" name="tipo_escala" required style="flex:1;">
                          <option value="">Selecciona el tipo de escala...</option>
                          <!-- Se cargan dinámicamente desde la BD -->
                      </select>
                                            <button type="button" id="btnOpenScaleModal" class="btn-add-item btn-sm" title="Crear nueva escala">
                                                <i class="fas fa-plus"></i> <span style="margin-left:6px;">Crear Escala</span>
                                            </button>
                    </div>
                    <small class="form-hint">Define qué opciones verán los estudiantes al responder cada pregunta</small>
                    <!-- Contenedor de opciones (se muestra/oculta desde JS) -->
                    <div id="opcionesSection" class="form-section" style="display: none; padding: 0; border: none;">
                        <div id="opcionesDisponibles" class="opciones-grid">
                            <!-- Se cargan dinámicamente según el tipo de escala -->
                        </div>
                    </div>
                </div>

            </div>

            <!-- Ítems del test -->
            <!-- (Opciones moved above, under the select) -->

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

<!-- Modal anidado para crear nueva escala -->
<div class="modal" id="scaleModal">
    <div class="modal-content modal-small" style="max-width: 600px;">
        <div class="modal-header">
            <h2><i class="fas fa-sliders-h"></i> Nueva Escala de Respuesta</h2>
            <button class="modal-close" id="closeScaleModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="scaleForm">
            <div class="modal-body">
                <div class="form-group">
                    <label for="nombreEscala">
                        <i class="fas fa-tag"></i> Nombre de la Escala *
                    </label>
                    <input type="text" id="nombreEscala" name="nombre" required
                           placeholder="Ejemplo: Likert 5 puntos"
                           minlength="3" maxlength="100">
                    <small class="form-hint">Mínimo 3 caracteres, máximo 100</small>
                </div>

                <!-- Opciones de respuesta -->
                <div class="form-section" style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--bg-500);">
                    <div class="section-header" style="margin-bottom: 1rem;">
                        <h3 style="font-size: 1rem; margin: 0;"><i class="fas fa-list"></i> Opciones de Respuesta</h3>
                        <button type="button" class="btn-add-item btn-sm" id="btnAgregarOpcion">
                            <i class="fas fa-plus"></i> Agregar Opción
                        </button>
                    </div>
                    <div class="info-box info-box-compact">
                        <i class="fas fa-lightbulb"></i>
                        <p>Define las opciones que verán los estudiantes. Agrega al menos 2 opciones.</p>
                    </div>
                    <div id="opcionesScaleContainer">
                        <!-- Opciones se agregan dinámicamente -->
                    </div>
                    <div class="empty-items" id="emptyOpciones" style="padding: 2rem 1rem;">
                        <i class="fas fa-inbox"></i>
                        <p class="empty-title" style="font-size: 0.95rem;">No hay opciones agregadas</p>
                        <p class="empty-hint" style="font-size: 0.85rem;">Haz clic en <strong>"Agregar Opción"</strong> para crear una opción</p>
                    </div>
                </div>
            </div>

            <!-- Botones de acción del modal de escala -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="btnCancelarScale">
                    <i class="fas fa-times-circle"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary" id="btnGuardarScale">
                    <i class="fas fa-save"></i> Guardar Escala
                    <span class="btn-loading" style="display: none;">
                        <i class="fas fa-spinner fa-spin"></i> Guardando...
                    </span>
                </button>
            </div>
            <div class="form-status" id="scaleFormStatus" style="display: none;"></div>
        </form>
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
