<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>

<link rel="stylesheet" href="views/administrador/tests.css?v=<?php echo time(); ?>">

<main class="admin-tests-container">
    <div class="page-header">
        <div class="header-content">
            <h1><i class="fas fa-clipboard-list"></i> Gestión de Tests</h1>
            <p class="subtitle">Crea, edita y administra los tests psicológicos del sistema</p>
        </div>
        <button class="btn-primary" id="btnNuevoTest">
            <i class="fas fa-plus"></i> Nuevo Test
        </button>
    </div>

    <!-- Filtros y búsqueda -->
    <div class="filters-section">
        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchTest" placeholder="Buscar por nombre del test...">
        </div>
        <div class="filter-group">
            <label>Ordenar por:</label>
            <select id="sortTests">
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
                    <label for="nombreTest">Nombre del Test *</label>
                    <input type="text" id="nombreTest" name="nombre" required
                           placeholder="Ej: Test de Ansiedad Generalizada GAD-7">
                </div>

                <div class="form-group">
                    <label for="descripcionTest">Descripción *</label>
                    <textarea id="descripcionTest" name="descripcion" required rows="3"
                              placeholder="Describe brevemente el objetivo del test..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="numItems">Número de Ítems *</label>
                        <input type="number" id="numItems" name="num_items" required min="1" value="10"
                               placeholder="Ej: 10">
                    </div>
                </div>
            </div>

            <!-- Ítems del test -->
            <div class="form-section">
                <div class="section-header">
                    <h3><i class="fas fa-list-ol"></i> Ítems del Test</h3>
                    <button type="button" class="btn-secondary btn-sm" id="btnAgregarItem">
                        <i class="fas fa-plus"></i> Agregar Ítem
                    </button>
                </div>

                <div id="itemsContainer">
                    <!-- Los ítems se agregan dinámicamente -->
                </div>

                <div class="empty-items" id="emptyItems">
                    <i class="fas fa-list"></i>
                    <p>No hay ítems agregados. Haz clic en "Agregar Ítem" para comenzar.</p>
                </div>
            </div>

            <!-- Opciones de respuesta disponibles -->
            <div class="form-section">
                <h3><i class="fas fa-check-square"></i> Opciones de Respuesta Disponibles</h3>
                <div class="info-box">
                    <i class="fas fa-info-circle"></i>
                    <p>Estas son las opciones de respuesta disponibles en el sistema. Cada ítem puede usar cualquiera de estas opciones.</p>
                </div>
                <div id="opcionesDisponibles" class="opciones-grid">
                    <!-- Se cargan dinámicamente -->
                </div>
            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="button" class="btn-secondary" id="btnCancelar">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Test
                </button>
            </div>
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

<script src="public/js/admin-tests.js?v=<?php echo time(); ?>"></script>
