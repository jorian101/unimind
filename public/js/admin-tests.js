/**
 * admin-tests.js
 * Manejo de CRUD de Tests para Administrador
 */

class AdminTestsManager {
  constructor() {
    this.currentTestId = null;
    this.opcionesDisponibles = [];
    this.tiposEscalas = [];
    this.itemCount = 0;
    this.opcionScaleCount = 0;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.loadTiposEscalas();
    this.loadTests();
  }

  setupEventListeners() {
    // Botón para abrir modal de crear test
    document.getElementById("btnNuevoTest").addEventListener("click", () => {
      this.openCreateModal();
    });

    // Botón para cerrar modal
    document.getElementById("closeModal").addEventListener("click", () => {
      this.closeModal();
    });

    // Botón cancelar en modal
    document.getElementById("btnCancelar").addEventListener("click", () => {
      this.closeModal();
    });

    // Botón agregar ítem
    document.getElementById("btnAgregarItem").addEventListener("click", () => {
      this.addItemCard();
    });

    // Submit del formulario
    document.getElementById("testForm").addEventListener("submit", (e) => {
      e.preventDefault();
      this.saveTest();
    });

    // Búsqueda de tests
    const searchInput = document.getElementById("searchTest");
    if (searchInput) {
      searchInput.addEventListener("input", (e) => {
        this.filterTests(e.target.value);
      });
    }

    // Ordenamiento (opcional, solo si existe el elemento)
    const sortSelect = document.getElementById("sortTests");
    if (sortSelect) {
      sortSelect.addEventListener("change", (e) => {
        this.sortTests(e.target.value);
      });
    }

    // Cambio de tipo de escala
    document.getElementById("tipoEscala").addEventListener("change", (e) => {
      if (e.target.value === "add_new") {
        this.openScaleModal();
      } else {
        // Guardar el valor anterior para poder restaurarlo
        e.target.dataset.previousValue = e.target.value;
        this.onTipoEscalaChange(e.target.value);
      }
    });

    // Botón junto al select para abrir modal de nueva escala
    const btnOpenScale = document.getElementById("btnOpenScaleModal");
    if (btnOpenScale) {
      btnOpenScale.addEventListener("click", () => {
        this.openScaleModal();
      });
    }

    // Cerrar modal al hacer clic fuera
    document.getElementById("testModal").addEventListener("click", (e) => {
      if (e.target.id === "testModal") {
        this.closeModal();
      }
    });

    // Modal de eliminación
    document
      .getElementById("btnCancelarDelete")
      .addEventListener("click", () => {
        this.closeDeleteModal();
      });

    document
      .getElementById("btnConfirmarDelete")
      .addEventListener("click", () => {
        this.confirmDelete();
      });

    // Modal de nueva escala
    document.getElementById("closeScaleModal").addEventListener("click", () => {
      this.closeScaleModal();
    });

    document
      .getElementById("btnCancelarScale")
      .addEventListener("click", () => {
        this.closeScaleModal();
      });

    document
      .getElementById("btnAgregarOpcion")
      .addEventListener("click", () => {
        this.addOpcionScale();
      });

    document.getElementById("scaleForm").addEventListener("submit", (e) => {
      e.preventDefault();
      this.saveScale();
    });

    // Cerrar modal de escala al hacer clic fuera
    document.getElementById("scaleModal").addEventListener("click", (e) => {
      if (e.target.id === "scaleModal") {
        this.closeScaleModal();
      }
    });
  }

  /**
   * Cargar tipos de escalas disponibles
   */
  async loadTiposEscalas() {
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const origin = window.location.origin || "";

      const makeCandidates = (action, extra = "") => [
        `${baseUrl}/controllers/TestsController.php?action=${action}${extra}`,
        `${origin}/unimind/controllers/TestsController.php?action=${action}${extra}`,
        `${origin}/controllers/TestsController.php?action=${action}${extra}`,
      ];

      const tryFetchJson = async (urls) => {
        for (const url of urls) {
          try {
            const resp = await fetch(url, { credentials: "include" });
            if (resp && resp.ok) {
              try {
                return await resp.json();
              } catch {
                return null;
              }
            }
          } catch {
            // continuar al siguiente candidato
          }
        }
        return null;
      };

      // 1) Intentar obtener tipos+opciones directamente
      const tiposEscalasResp = await tryFetchJson(
        makeCandidates("getTiposEscalas"),
      );
      if (
        tiposEscalasResp &&
        tiposEscalasResp.success &&
        Array.isArray(tiposEscalasResp.data) &&
        tiposEscalasResp.data.length > 0
      ) {
        this.tiposEscalas = tiposEscalasResp.data;
        this.renderTiposEscalas();
        return;
      }

      // 2) Intentar obtener lista de tipos (sin opciones) y luego pedir opciones por tipo
      const tiposResp = await tryFetchJson(makeCandidates("getTipos"));
      if (
        tiposResp &&
        tiposResp.success &&
        Array.isArray(tiposResp.data) &&
        tiposResp.data.length > 0
      ) {
        const tipos = tiposResp.data;
        const assembled = [];
        for (const tipo of tipos) {
          const optsResp = await tryFetchJson(
            makeCandidates(
              "getOpcionesByTipoEscala",
              `&tipo_escala=${encodeURIComponent(tipo.id_tipo_escala)}`,
            ),
          );
          const opciones =
            optsResp && optsResp.success && Array.isArray(optsResp.data)
              ? optsResp.data
              : [];
          assembled.push({
            id_tipo_escala: tipo.id_tipo_escala,
            nombre: tipo.nombre,
            descripcion: tipo.descripcion,
            opciones: opciones,
          });
        }
        this.tiposEscalas = assembled;
        this.renderTiposEscalas();
        return;
      }

      // 3) Fallback: usar todas las opciones como una escala por defecto
      const allOptsResp = await tryFetchJson(makeCandidates("getOpciones"));
      if (
        allOptsResp &&
        allOptsResp.success &&
        Array.isArray(allOptsResp.data) &&
        allOptsResp.data.length > 0
      ) {
        this.tiposEscalas = [
          {
            id_tipo_escala: "default",
            nombre: "Escala por defecto",
            descripcion: "Opciones cargadas desde opciones generales",
            opciones: allOptsResp.data.map((o) => ({
              id_opcion: o.id_opcion,
              texto_opcion: o.texto_opcion,
              valor_puntuacion: o.valor_puntuacion,
            })),
          },
        ];
        this.renderTiposEscalas();
        return;
      }
    } catch {
      // Silenciar error en entorno offline o fallos de red
    }
  }

  /**
   * Renderizar selector de tipos de escalas
   */
  renderTiposEscalas() {
    const select = document.getElementById("tipoEscala");
    if (!select) return;
    const currentValue = select.value || "";

    // Mantener la opción por defecto
    select.innerHTML =
      '<option value="">Selecciona el tipo de escala...</option>';

    this.tiposEscalas.forEach((tipo) => {
      const option = document.createElement("option");
      option.value = tipo.id_tipo_escala;
      option.textContent = tipo.nombre || tipo.id_tipo_escala;
      option.title = tipo.descripcion || "";
      select.appendChild(option);
    });

    // Agregar opción para crear nueva escala
    const addOption = document.createElement("option");
    addOption.value = "add_new";
    addOption.textContent = "+ Agregar nueva escala";
    addOption.style.fontWeight = "600";
    addOption.style.color = "var(--acc-700)";
    select.appendChild(addOption);

    // Restaurar valor si existía
    if (currentValue && currentValue !== "add_new") select.value = currentValue;
  }

  /**
   * Cargar y mostrar opciones cuando cambia el tipo de escala
   */
  async onTipoEscalaChange(tipoEscalaId) {
    if (!tipoEscalaId) {
      document.getElementById("opcionesSection").style.display = "none";
      return;
    }

    // Buscar las opciones en la estructura de tiposEscalas
    const tipo = this.tiposEscalas.find(
      (t) => String(t.id_tipo_escala) === String(tipoEscalaId),
    );
    if (tipo && tipo.opciones && tipo.opciones.length > 0) {
      this.opcionesDisponibles = tipo.opciones;
      this.renderOpciones();
      document.getElementById("opcionesSection").style.display = "block";
    } else {
      this.opcionesDisponibles = [];
      document.getElementById("opcionesSection").style.display = "none";
    }
  }

  /**
   * Renderizar opciones disponibles en el modal
   */
  renderOpciones() {
    const container = document.getElementById("opcionesDisponibles");
    container.innerHTML = "";

    this.opcionesDisponibles.forEach((opcion) => {
      const opcionDiv = document.createElement("div");
      opcionDiv.className = "opcion-item";
      opcionDiv.innerHTML = `
                <span class="opcion-text">${opcion.texto_opcion}</span>
                <span class="opcion-value">${opcion.valor_puntuacion}</span>
            `;
      container.appendChild(opcionDiv);
    });
  }

  /**
   * Cargar todos los tests
   */
  async loadTests() {
    let serverTests = [];
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php?action=getAll`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success) {
        serverTests = data.data || [];
      }
    } catch {
      // Silenciar error en entorno offline
    }

    // Cargar tests encolados desde IndexedDB (pendientes de sincronización)
    let localTests = [];
    try {
      if (window.UnimindSync && window.UnimindSync.getQueuedTests) {
        const queued = await window.UnimindSync.getQueuedTests(100);
        localTests = queued.map((q) => ({
          id_test: q.client_uuid
            ? `local-${q.client_uuid}`
            : `local-${Date.now()}`,
          nombre: q.nombre || "Test sin nombre",
          descripcion: q.descripcion || "",
          num_items: q.num_items || 0,
          items: q.items || [],
          isLocal: true,
        }));
      }
    } catch {
      // Ignorar errores al cargar tests locales desde IndexedDB
    }

    // Combinar y renderizar
    const allTests = [...localTests, ...serverTests];
    if (allTests.length === 0) {
      this.showEmptyState();
    } else {
      this.renderTests(allTests);
    }
  }

  /**
   * Renderizar lista de tests como tabla
   */
  renderTests(tests) {
    const container = document.getElementById("testsGrid");
    const emptyState = document.getElementById("emptyState");

    if (!tests || tests.length === 0) {
      this.showEmptyState();
      return;
    }

    emptyState.style.display = "none";

    // Construir filas de la tabla
    const rowsHTML = tests.map((test) => this.createTestRow(test)).join("");

    // Construir tabla completa
    container.innerHTML = `
      <table class="tests-table">
        <thead class="tests-table-head">
          <tr class="tests-table-row">
            <th class="tests-table-header">Test</th>
            <th class="tests-table-header">Items</th>
            <th class="tests-table-header">Escala</th>
            <th class="tests-table-header">Creado</th>
            <th class="tests-table-header">Opciones</th>
            <th class="tests-table-header">Acciones</th>
          </tr>
        </thead>
        <tbody>
          ${rowsHTML}
        </tbody>
      </table>
    `;
  }

  /**
   * Crear fila de test para tabla
   */
  createTestRow(test) {
    const isLocal = test.isLocal || String(test.id_test).startsWith("local");

    // Determinar icono según el tipo de test
    let icon = "fa-clipboard-list";
    const nombre = (test.nombre || "").toLowerCase();

    if (nombre.includes("estrés") || nombre.includes("estres")) {
      icon = "fa-chart-bar";
    } else if (nombre.includes("ansiedad")) {
      icon = "fa-brain";
    } else if (nombre.includes("depresión") || nombre.includes("depresion")) {
      icon = "fa-heart-broken";
    } else if (nombre.includes("burnout")) {
      icon = "fa-fire";
    }

    // Formatear fechas
    const fechaCreacion = test.created_at
      ? this.formatearFecha(test.created_at)
      : "N/A";

    // Construir tags de opciones de la escala (versión compacta)
    let opcionesTags = "";
    if (test.opciones && test.opciones.length > 0) {
      opcionesTags = test.opciones
        .map(
          (opcion) =>
            `<span class="option-tag-small" title="${this.escapeHtml(opcion.texto_opcion)}: ${opcion.valor_puntuacion}">
              ${this.escapeHtml(opcion.texto_opcion.substring(0, 15))}${opcion.texto_opcion.length > 15 ? "..." : ""}
            </span>`,
        )
        .join("");
    } else {
      opcionesTags = '<span class="option-tag-empty">N/A</span>';
    }

    const syncBadge = isLocal
      ? '<span class="sync-badge" style="color:#f59e0b;font-size:0.75em;margin-left:4px;" title="Pendiente de sincronización"><i class="fas fa-sync-alt"></i></span>'
      : "";

    const actionsDisabled = isLocal
      ? 'style="opacity:0.5;pointer-events:none;"'
      : "";

    return `
      <tr class="tests-table-row" data-test-id="${test.id_test}">
        <td class="tests-table-cell">
          <div class="test-name-cell">
            <div class="test-icon">
              <i class="fas ${icon}"></i>
            </div>
            <div>
              <div class="test-name">${this.escapeHtml(test.nombre)} ${syncBadge}</div>
              <div class="test-description">${this.escapeHtml(test.descripcion || "")}</div>
            </div>
          </div>
        </td>
        <td class="tests-table-cell">
          <span class="items-badge">
            <i class="fas fa-list-ol"></i>
            ${test.num_items || 0}
          </span>
        </td>
        <td class="tests-table-cell">
          <span class="escala-name">${this.escapeHtml(test.nombre_escala || "No definida")}</span>
        </td>
        <td class="tests-table-cell">
          <div class="date-cell">${fechaCreacion}</div>
        </td>
        <td class="tests-table-cell">
          <div class="opciones-compact">
            ${opcionesTags}
          </div>
        </td>
        <td class="tests-table-cell">
          <div class="action-buttons" ${actionsDisabled}>
            <button class="btn-action btn-edit" onclick="adminTests.editTest('${test.id_test}')" title="${isLocal ? "No disponible offline" : "Editar test"}">
              <i class="fas fa-edit"></i>
            </button>
            <button class="btn-action btn-delete" onclick="adminTests.deleteTest('${test.id_test}', '${this.escapeHtml(test.nombre).replace(/'/g, "\\'")}')" title="${isLocal ? "No disponible offline" : "Eliminar test"}">
              <i class="fas fa-trash"></i>
            </button>
          </div>
        </td>
      </tr>
    `;
  }

  /**
   * Formatear fecha a formato legible
   */
  formatearFecha(fecha) {
    if (!fecha) return "N/A";
    const date = new Date(fecha);
    const dia = String(date.getDate()).padStart(2, "0");
    const mes = String(date.getMonth() + 1).padStart(2, "0");
    const anio = date.getFullYear();
    return `${dia}/${mes}/${anio}`;
  }

  /**
   * Mostrar estado vacío
   */
  showEmptyState() {
    const container = document.getElementById("testsGrid");
    const emptyState = document.getElementById("emptyState");

    container.innerHTML = "";
    emptyState.style.display = "block";
  }

  /**
   * Abrir modal para crear test
   */
  openCreateModal() {
    this.currentTestId = null;
    document.getElementById("modalTitle").innerHTML =
      '<i class="fas fa-clipboard-list"></i> Nuevo Test';
    document.getElementById("testForm").reset();
    document.getElementById("testId").value = "";

    // Limpiar items
    this.itemCount = 0;
    document.getElementById("itemsContainer").innerHTML = "";
    document.getElementById("emptyItems").style.display = "block";

    // Ocultar sección de opciones hasta que se seleccione tipo de escala
    document.getElementById("opcionesSection").style.display = "none";

    // Actualizar display
    this.updateDisplay();

    // Asegurarse que el botón guardar no quede en estado loading
    this.setSaveButtonLoading(false);
    this.openModal();
  }

  /**
   * Ver detalles de un test
   */
  async viewTest(id_test) {
    if (String(id_test).startsWith("local")) {
      this.showNotification(
        "No se puede ver un test pendiente de sincronización",
        "warning",
      );
      return;
    }
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php?action=getById&id_test=${id_test}`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success) {
        this.openViewModal(data.data);
      } else {
        this.showNotification("Error al cargar el test", "error");
      }
    } catch {
      this.showNotification("Error al cargar el test", "error");
    }
  }

  /**
   * Abrir modal en modo vista (solo lectura)
   */
  openViewModal(test) {
    this.currentTestId = test.id_test;
    document.getElementById("modalTitle").innerHTML =
      '<i class="fas fa-eye"></i> Ver Test';

    // Llenar formulario
    document.getElementById("testId").value = test.id_test;
    document.getElementById("nombreTest").value = test.nombre;
    document.getElementById("descripcionTest").value = test.descripcion;
    // Deshabilitar campos
    document.getElementById("nombreTest").disabled = true;
    document.getElementById("descripcionTest").disabled = true;
    // Mostrar el número de ítems como texto (no editable)
    const numDisplayView = document.getElementById("numItems");
    const numHiddenView = document.getElementById("numItemsHidden");
    if (numDisplayView) numDisplayView.textContent = test.num_items;
    if (numHiddenView) numHiddenView.value = test.num_items;

    // (fechas de creación/edición removidas de la vista según petición)

    // Cargar items en modo lectura
    this.loadItemsReadOnly(test.items);

    // Ocultar botones de acción
    document.getElementById("btnAgregarItem").style.display = "none";
    document.querySelector(
      '.form-actions button[type="submit"]',
    ).style.display = "none";

    this.openModal();
  }

  /**
   * Cargar items en modo solo lectura
   */
  loadItemsReadOnly(items) {
    const container = document.getElementById("itemsContainer");
    const emptyItems = document.getElementById("emptyItems");

    container.innerHTML = "";

    if (!items || items.length === 0) {
      emptyItems.style.display = "block";
      return;
    }

    emptyItems.style.display = "none";

    items.forEach((item) => {
      const itemDiv = document.createElement("div");
      itemDiv.className = "item-card";

      // Generar IDs únicos para modo lectura
      const textoId = `readonly-texto-${item.orden}`;

      itemDiv.innerHTML = `
            <div class="item-card-header">
              <span class="item-number">Ítem ${item.orden}</span>
            </div>
            <div class="item-form-group">
              <label for="${textoId}">Pregunta:</label>
              <textarea id="${textoId}" 
                    name="readonly_items[${item.orden}][texto]" 
                    disabled>${this.escapeHtml(item.texto_item)}</textarea>
            </div>
          `;
      container.appendChild(itemDiv);
    });
  }

  /**
   * Actualizar display/hidden del contador de ítems
   */
  updateDisplay() {
    const count = document.querySelectorAll(".item-card").length;
    const numDisplay = document.getElementById("numItems");
    const numHidden = document.getElementById("numItemsHidden");
    if (numDisplay) numDisplay.textContent = count;
    if (numHidden) numHidden.value = count;
  }

  /**
   * Editar un test
   */
  async editTest(id_test, retryCount = 0) {
    if (String(id_test).startsWith("local")) {
      this.showNotification(
        "No se puede editar un test pendiente de sincronización",
        "warning",
      );
      return;
    }
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php?action=getById&id_test=${id_test}`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success && data.data) {
        // Verificar que los items estén cargados (si no, aún así abrimos el modal)
        this.openEditModal(data.data);
      } else {
        // Si el test no se encuentra y es el primer intento, recargar tests y reintentar
        if (!data.success && retryCount === 0) {
          await this.loadTests();
          await new Promise((resolve) => setTimeout(resolve, 300));
          return this.editTest(id_test, 1);
        }
        this.showNotification(
          data.message || "Error al cargar el test",
          "error",
        );
      }
    } catch {
      this.showNotification("Error al cargar el test", "error");
    }
  }

  /**
   * Abrir modal en modo edición
   */
  openEditModal(test) {
    if (!test || !test.id_test) {
      this.showNotification("Datos de test inválidos", "error");
      return;
    }

    this.currentTestId = test.id_test;
    document.getElementById("modalTitle").innerHTML =
      '<i class="fas fa-edit"></i> Editar Test';

    // Llenar formulario
    document.getElementById("testId").value = test.id_test;
    document.getElementById("nombreTest").value = test.nombre || "";
    document.getElementById("descripcionTest").value = test.descripcion || "";

    // Establecer tipo de escala y cargar opciones
    if (test.tipo_escala) {
      document.getElementById("tipoEscala").value = test.tipo_escala;
      this.onTipoEscalaChange(test.tipo_escala);
    }

    // Habilitar campos de texto (el número se muestra como texto y no es editable)
    document.getElementById("nombreTest").disabled = false;
    document.getElementById("descripcionTest").disabled = false;

    // Cargar items en modo edición
    this.loadItemsForEdit(test.items || []);

    // Mostrar botones de acción
    document.getElementById("btnAgregarItem").style.display = "inline-flex";
    document.querySelector(
      '.form-actions button[type="submit"]',
    ).style.display = "inline-flex";

    this.openModal();
  }

  /**
   * Cargar items para edición
   */
  loadItemsForEdit(items) {
    const container = document.getElementById("itemsContainer");
    const emptyItems = document.getElementById("emptyItems");

    container.innerHTML = "";
    this.itemCount = 0;

    if (!items || items.length === 0) {
      emptyItems.style.display = "block";
      return;
    }

    emptyItems.style.display = "none";

    items.forEach((item) => {
      this.itemCount++;
      const itemDiv = this.createItemCard(this.itemCount, item.texto_item);
      container.appendChild(itemDiv);
    });
    // Sincronizar el display y el hidden con la cantidad real de ítems cargados
    this.updateDisplay();
  }

  /**
   * Eliminar test
   */
  deleteTest(id_test, nombre) {
    if (String(id_test).startsWith("local")) {
      this.showNotification(
        "No se puede eliminar un test pendiente de sincronización",
        "warning",
      );
      return;
    }
    this.currentTestId = id_test;
    document.getElementById("testNameDelete").textContent = nombre;
    document.getElementById("deleteModal").classList.add("active");
  }

  /**
   * Confirmar eliminación
   */
  async confirmDelete() {
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id_test", this.currentTestId);

      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php`,
        {
          method: "POST",
          body: formData,
          credentials: "include",
        },
      );

      const data = await response.json();

      if (data.success) {
        this.showNotification(data.message, "success");
        this.closeDeleteModal();
        this.loadTests();
      } else {
        this.showNotification(data.message, "error");
      }
    } catch {
      this.showNotification("Error al eliminar el test", "error");
    }
  }

  /**
   * Agregar tarjeta de ítem
   */
  addItemCard() {
    this.itemCount++;
    const container = document.getElementById("itemsContainer");
    const emptyItems = document.getElementById("emptyItems");

    emptyItems.style.display = "none";

    const itemDiv = this.createItemCard(this.itemCount);
    container.appendChild(itemDiv);
    this.updateDisplay();
  }

  /**
   * Crear tarjeta de ítem
   */
  createItemCard(orden, textoItem = "") {
    const itemDiv = document.createElement("div");
    itemDiv.className = "item-card";
    itemDiv.dataset.orden = orden;

    // Generar IDs únicos para los campos
    const textoId = `item-texto-${orden}`;

    // Crear elementos del header
    const header = document.createElement("div");
    header.className = "item-card-header";
    header.innerHTML = `
        <span class="item-number"><i class="fas fa-question-circle"></i> Ítem ${orden}</span>
        <button type="button" class="btn-remove-item" onclick="adminTests.removeItem(this)" title="Eliminar este ítem">
            <i class="fas fa-trash-alt"></i>
        </button>
    `;

    // Crear grupo de pregunta
    const preguntaGroup = document.createElement("div");
    preguntaGroup.className = "item-form-group";

    const preguntaLabel = document.createElement("label");
    preguntaLabel.setAttribute("for", textoId);
    preguntaLabel.innerHTML =
      '<i class="fas fa-comment-dots"></i> Pregunta/Afirmación *';

    const preguntaTextarea = document.createElement("textarea");
    preguntaTextarea.id = textoId;
    preguntaTextarea.name = `items[${orden}][texto]`;
    preguntaTextarea.className = "item-texto";
    preguntaTextarea.placeholder =
      "Ejemplo: Durante las últimas 2 semanas, ¿con qué frecuencia te has sentido nervioso/a o ansioso/a?";
    preguntaTextarea.required = true;
    preguntaTextarea.minLength = 10;
    preguntaTextarea.maxLength = 500;
    preguntaTextarea.value = textoItem;

    const preguntaHint = document.createElement("small");
    preguntaHint.className = "form-hint";
    preguntaHint.textContent = "Mínimo 10 caracteres, máximo 500";

    preguntaGroup.appendChild(preguntaLabel);
    preguntaGroup.appendChild(preguntaTextarea);
    preguntaGroup.appendChild(preguntaHint);

    // Ensamblar el itemDiv (sin subescala)
    itemDiv.appendChild(header);
    itemDiv.appendChild(preguntaGroup);

    return itemDiv;
  }

  /**
   * Eliminar ítem
   */
  removeItem(button) {
    const itemCard = button.closest(".item-card");
    itemCard.remove();

    // Actualizar numeración
    this.updateItemNumbers();

    // Mostrar mensaje vacío si no hay items
    const container = document.getElementById("itemsContainer");
    const emptyItems = document.getElementById("emptyItems");

    if (container.children.length === 0) {
      emptyItems.style.display = "block";
      this.itemCount = 0;
    }
    this.updateDisplay();
  }

  /**
   * Actualizar numeración de items
   */
  updateItemNumbers() {
    const items = document.querySelectorAll(".item-card");
    items.forEach((item, index) => {
      const orden = index + 1;
      item.dataset.orden = orden;
      item.querySelector(".item-number").textContent = `Ítem ${orden}`;
    });
    this.itemCount = items.length;
    this.updateDisplay();
  }

  /**
   * Guardar test (crear o actualizar)
   */
  async saveTest() {
    // Validar datos básicos
    const nombre = document.getElementById("nombreTest").value.trim();
    const descripcion = document.getElementById("descripcionTest").value.trim();
    const tipoEscala = document.getElementById("tipoEscala").value;
    // El número de ítems se calcula a partir de las tarjetas (no editable manualmente)
    const numItems = document.querySelectorAll(".item-card").length;

    if (!nombre || !descripcion || !tipoEscala || numItems <= 0) {
      this.showFormStatus(
        "Por favor completa todos los campos obligatorios. Revisa el nombre, descripción, tipo de escala y agrega al menos 1 ítem.",
        "warning",
      );
      this.showNotification(
        "Completa todos los campos obligatorios",
        "warning",
      );
      return;
    }

    // Recopilar items y validar completitud
    const items = this.collectItems();
    // Si hay tarjetas con textarea vacío, resaltarlas y evitar guardar
    const itemCards = Array.from(document.querySelectorAll(".item-card"));
    const emptyCards = [];
    itemCards.forEach((card) => {
      const ta = card.querySelector(".item-texto");
      if (!ta || !ta.value || ta.value.trim() === "") {
        card.classList.add("item-empty");
        emptyCards.push(card);
      } else {
        card.classList.remove("item-empty");
      }
    });

    if (emptyCards.length > 0) {
      this.showNotification(
        `Hay ${emptyCards.length} ítem(s) sin completar. Por favor complétalos antes de guardar.`,
        "warning",
      );
      // desplazar hasta el primer vacío
      emptyCards[0].scrollIntoView({ behavior: "smooth", block: "center" });
      return;
    }

    if (items.length !== numItems) {
      this.showNotification(
        `El número de ítems (${items.length}) no coincide con el valor esperado (${numItems})`,
        "warning",
      );
      return;
    }

    // Preparar datos
    const formData = new FormData();
    const action = this.currentTestId ? "update" : "create";
    formData.append("action", action);

    if (this.currentTestId) {
      formData.append("id_test", this.currentTestId);
    }

    formData.append("nombre", nombre);
    formData.append("descripcion", descripcion);
    formData.append("num_items", numItems);
    formData.append("tipo_escala", tipoEscala);
    formData.append("items", JSON.stringify(items));

    // Mostrar estado de guardando
    this.setSaveButtonLoading(true);
    this.showFormStatus("Guardando test...", "info");

    // Si estamos offline, no intentamos hacer el POST (evitamos el error net::ERR_INTERNET_DISCONNECTED)
    if (typeof navigator !== "undefined" && !navigator.onLine) {
      try {
        if (window.UnimindSync && window.UnimindSync.addTest) {
          // Encolar operación. Para updates incluimos el id_test y action:'update'
          const payload = Object.assign(
            { nombre, descripcion, num_items: numItems, items },
            this.currentTestId
              ? { id_test: this.currentTestId, action: "update" }
              : {},
          );

          await window.UnimindSync.addTest(payload);

          this.closeModal();
          this.showFormStatus("Guardado localmente (sin conexión)", "success");
          this.showNotification(
            "Test guardado localmente. Se sincronizará cuando vuelvas online.",
            "info",
          );
          this.setSaveButtonLoading(false);
          // Reload to show the test (or just insert card - but reload is safer for consistency)
          this.loadTests();
          return;
        } else {
          this.showFormStatus(
            "No hay conexión y el sincronizador no está disponible.",
            "error",
          );
          this.setSaveButtonLoading(false);
          return;
        }
      } catch {
        this.showFormStatus(
          "Error al encolar localmente. Verifica el almacenamiento del navegador.",
          "error",
        );
        this.setSaveButtonLoading(false);
        return;
      }
    }

    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php`,
        {
          method: "POST",
          body: formData,
          credentials: "include",
        },
      );

      const data = await response.json();

      if (data.success) {
        this.showFormStatus(data.message, "success");
        this.showNotification(data.message, "success");
        this.setSaveButtonLoading(false);
        setTimeout(() => {
          this.closeModal();
          this.loadTests();
        }, 1000);
      } else {
        this.showFormStatus(data.message, "error");
        this.showNotification(data.message, "error");
        this.setSaveButtonLoading(false);
      }
    } catch {
      // Intentar guardar localmente en IndexedDB para sincronizar luego
      try {
        if (window.UnimindSync && window.UnimindSync.addTest) {
          const rec = await window.UnimindSync.addTest({
            nombre: nombre,
            descripcion: descripcion,
            num_items: numItems,
            items: items,
          });

          // Mostrar el test en la interfaz como si se hubiera guardado online
          const localTest = {
            id_test: `local-${rec.client_uuid}`,
            nombre: nombre,
            descripcion: descripcion,
            num_items: numItems,
            items: items,
          };

          // Cerrar modal y añadir tarjeta nueva al grid sin notificaciones
          this.closeModal();
          const container = document.getElementById("testsGrid");
          const emptyState = document.getElementById("emptyState");
          if (emptyState) emptyState.style.display = "none";
          const card = this.createTestCard(localTest);
          container.insertBefore(card, container.firstChild);

          // Opcional: actualizar cualquier UI relacionada
          this.showFormStatus("Guardado localmente", "success");
          this.setSaveButtonLoading(false);
        } else {
          this.showFormStatus(
            "Error al conectar con el servidor. Verifica tu conexión.",
            "error",
          );
        }
      } catch {
        this.showFormStatus(
          "Error al guardar localmente. Verifica el almacenamiento del navegador.",
          "error",
        );
      } finally {
        this.setSaveButtonLoading(false);
      }
    }
  }

  /**
   * Controlar estado de carga del botón guardar
   */
  setSaveButtonLoading(isLoading) {
    const btn = document.getElementById("btnGuardarTest");
    const btnIcon = btn.querySelector(".fa-save");
    const btnLoading = btn.querySelector(".btn-loading");

    if (isLoading) {
      btn.classList.add("is-loading");
      btn.disabled = true;
      if (btnIcon) btnIcon.style.display = "none";
      if (btnLoading) btnLoading.style.display = "inline-flex";
    } else {
      btn.classList.remove("is-loading");
      btn.disabled = false;
      if (btnIcon) btnIcon.style.display = "inline";
      if (btnLoading) btnLoading.style.display = "none";
    }
  }

  /**
   * Mostrar estado en el formulario
   */
  showFormStatus(message, type = "info") {
    const statusDiv = document.getElementById("formStatus");
    if (!statusDiv) return;

    statusDiv.className = "form-status status-" + type;
    statusDiv.innerHTML = `
      <i class="fas fa-${
        type === "success"
          ? "check-circle"
          : type === "error"
            ? "exclamation-circle"
            : type === "warning"
              ? "exclamation-triangle"
              : "info-circle"
      }"></i>
      <span>${message}</span>
    `;
    statusDiv.style.display = "flex";

    // Auto-ocultar después de 5 segundos si es success
    if (type === "success") {
      setTimeout(() => {
        statusDiv.style.display = "none";
      }, 5000);
    }
  }

  /**
   * Recopilar datos de items del formulario
   */
  collectItems() {
    const items = [];
    const itemCards = document.querySelectorAll(".item-card");

    itemCards.forEach((card) => {
      const textoItem = card.querySelector(".item-texto").value.trim();
      const orden = parseInt(card.dataset.orden) || items.length + 1;

      if (textoItem) {
        items.push({
          texto_item: textoItem,
          subescala: "General",
          orden: orden,
        });
      }
    });

    return items;
  }

  /**
   * Filtrar tests por búsqueda (trabaja con tabla)
   */
  filterTests(searchTerm) {
    const rows = document.querySelectorAll(".tests-table-row");
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;

    rows.forEach((row) => {
      // Saltar el header
      if (row.parentElement.tagName === "THEAD") return;

      const testId = row.dataset.testId;
      if (!testId) return;

      const nameCell = row.querySelector(".test-name");
      const descCell = row.querySelector(".test-description");
      const name = nameCell ? nameCell.textContent.toLowerCase() : "";
      const desc = descCell ? descCell.textContent.toLowerCase() : "";

      const shouldShow = name.includes(term) || desc.includes(term);
      row.style.display = shouldShow ? "" : "none";
      if (shouldShow) visibleCount++;
    });

    // Mostrar estado vacío si no hay resultados
    const emptyState = document.getElementById("emptyState");
    const container = document.getElementById("testsGrid");
    if (visibleCount === 0 && term) {
      if (container) {
        container.innerHTML = `
          <div class="no-tests">
            <i class="fas fa-search"></i>
            <p>No se encontraron tests con "${this.escapeHtml(term)}"</p>
          </div>
        `;
      }
    } else if (visibleCount === 0) {
      if (emptyState) {
        emptyState.style.display = "block";
      }
    } else {
      if (emptyState) {
        emptyState.style.display = "none";
      }
    }
  }

  /**
   * Ordenar tests (trabaja con tabla)
   */
  sortTests(sortBy) {
    const tbody = document.querySelector(".tests-table tbody");
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll(".tests-table-row"));

    rows.sort((a, b) => {
      switch (sortBy) {
        case "nombre":
          const aName = a.querySelector(".test-name")?.textContent || "";
          const bName = b.querySelector(".test-name")?.textContent || "";
          return aName.localeCompare(bName);
        case "num_items":
          const itemsA = parseInt(
            a.querySelector(".items-badge")?.textContent.replace(/\D/g, "") ||
              "0",
          );
          const itemsB = parseInt(
            b.querySelector(".items-badge")?.textContent.replace(/\D/g, "") ||
              "0",
          );
          return itemsB - itemsA;
        case "fecha":
          const dateA = a.querySelector(".date-cell")?.textContent || "";
          const dateB = b.querySelector(".date-cell")?.textContent || "";
          return dateB.localeCompare(dateA);
        default:
          return 0;
      }
    });

    // Reordenar en el DOM
    tbody.innerHTML = "";
    rows.forEach((row) => tbody.appendChild(row));
  }

  /**
   * Abrir modal
   */
  openModal() {
    document.getElementById("testModal").classList.add("active");
    document.body.style.overflow = "hidden";
  }

  /**
   * Cerrar modal
   */
  closeModal() {
    document.getElementById("testModal").classList.remove("active");
    document.body.style.overflow = "auto";

    // Restaurar campos
    document.getElementById("nombreTest").disabled = false;
    document.getElementById("descripcionTest").disabled = false;
    // `numItems` es ahora un display + hidden; no hay input que habilitar
    document.getElementById("btnAgregarItem").style.display = "inline-flex";
    document.querySelector(
      '.form-actions button[type="submit"]',
    ).style.display = "inline-flex";
    // Asegurar que el botón guardar quede habilitado
    try {
      this.setSaveButtonLoading(false);
    } catch {}
  }

  /**
   * Cerrar modal de eliminación
   */
  closeDeleteModal() {
    document.getElementById("deleteModal").classList.remove("active");
    this.currentTestId = null;
  }

  /**
   * Abrir modal para crear nueva escala
   */
  openScaleModal() {
    // Resetear el select de tipo de escala al valor anterior
    const select = document.getElementById("tipoEscala");
    const previousValue = select.dataset.previousValue || "";
    select.value = previousValue;

    // Limpiar formulario
    document.getElementById("scaleForm").reset();
    this.opcionScaleCount = 0;
    document.getElementById("opcionesScaleContainer").innerHTML = "";
    document.getElementById("emptyOpciones").style.display = "block";

    // Resetear estado del formulario
    const statusDiv = document.getElementById("scaleFormStatus");
    if (statusDiv) statusDiv.style.display = "none";
    this.setScaleSaveButtonLoading(false);

    // Abrir modal
    document.getElementById("scaleModal").classList.add("active");
  }

  /**
   * Cerrar modal de escala
   */
  closeScaleModal() {
    document.getElementById("scaleModal").classList.remove("active");

    // Resetear el select de tipo de escala
    const select = document.getElementById("tipoEscala");
    const previousValue = select.dataset.previousValue || "";
    select.value = previousValue;
  }

  /**
   * Agregar opción a la escala
   */
  addOpcionScale() {
    this.opcionScaleCount++;
    const container = document.getElementById("opcionesScaleContainer");
    const emptyOpciones = document.getElementById("emptyOpciones");

    emptyOpciones.style.display = "none";

    // asignar valor automáticamente: primer item = 0, segundo = 1, ...
    const defaultValor = this.opcionScaleCount - 1;
    const opcionDiv = this.createOpcionScaleCard(
      this.opcionScaleCount,
      "",
      defaultValor,
    );
    container.appendChild(opcionDiv);
  }

  /**
   * Crear tarjeta de opción para la escala
   */
  createOpcionScaleCard(orden, textoOpcion = "", valorPuntuacion = "") {
    const opcionDiv = document.createElement("div");
    opcionDiv.className = "opcion-scale-item";
    opcionDiv.dataset.orden = orden;

    const textoId = `opcion-texto-${orden}`;
    const valorId = `opcion-valor-${orden}`;

    opcionDiv.innerHTML = `
      <div class="opcion-scale-header">
        <span class="opcion-scale-number">
          <i class="fas fa-check-circle"></i> Opción ${orden}
        </span>
        <button type="button" class="btn-remove-opcion" onclick="adminTests.removeOpcionScale(this)" title="Eliminar esta opción">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
      <div class="opcion-scale-fields">
        <div class="opcion-scale-field">
          <label for="${textoId}">Texto *</label>
          <input type="text" id="${textoId}" name="opciones[${orden}][texto]" 
                 placeholder="Ej: Totalmente de acuerdo" 
                 required minlength="1" maxlength="100" value="${textoOpcion}">
        </div>
        <div class="opcion-scale-field">
          <label for="${valorId}">Valor *</label>
          <input type="number" id="${valorId}" name="opciones[${orden}][valor]" 
                 placeholder="0-100" 
                 required min="0" max="100" value="${valorPuntuacion}" readonly>
        </div>
      </div>
    `;

    return opcionDiv;
  }

  /**
   * Eliminar opción de escala
   */
  removeOpcionScale(button) {
    const opcionCard = button.closest(".opcion-scale-item");
    opcionCard.remove();

    // Actualizar numeración
    this.updateOpcionScaleNumbers();

    // Mostrar mensaje vacío si no hay opciones
    const container = document.getElementById("opcionesScaleContainer");
    const emptyOpciones = document.getElementById("emptyOpciones");

    if (container.children.length === 0) {
      emptyOpciones.style.display = "block";
      this.opcionScaleCount = 0;
    }
  }

  /**
   * Actualizar numeración de opciones
   */
  updateOpcionScaleNumbers() {
    const opciones = document.querySelectorAll(".opcion-scale-item");
    opciones.forEach((opcion, index) => {
      const orden = index + 1;
      opcion.dataset.orden = orden;
      opcion.querySelector(".opcion-scale-number").innerHTML = `
        <i class="fas fa-check-circle"></i> Opción ${orden}
      `;

      // actualizar valor automáticamente para mantener 0..n-1
      const valorInput = opcion.querySelector('input[name*="[valor]"]');
      if (valorInput) {
        valorInput.value = index;
      }
    });
    this.opcionScaleCount = opciones.length;
  }

  /**
   * Guardar nueva escala
   */
  async saveScale() {
    const nombre = document.getElementById("nombreEscala").value.trim();
    const descripcion = "";

    // Recopilar opciones
    const opciones = this.collectOpcionesScale();

    // Validar
    if (!nombre) {
      this.showScaleFormStatus(
        "Por favor ingresa un nombre para la escala",
        "warning",
      );
      return;
    }

    if (opciones.length < 2) {
      this.showScaleFormStatus(
        "Debes agregar al menos 2 opciones de respuesta",
        "warning",
      );
      return;
    }

    // Preparar datos
    const formData = new FormData();
    formData.append("action", "createTipoEscala");
    formData.append("nombre", nombre);
    formData.append("descripcion", descripcion);
    formData.append("opciones", JSON.stringify(opciones));

    this.setScaleSaveButtonLoading(true);
    this.showScaleFormStatus("Guardando escala...", "info");

    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;

      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php`,
        {
          method: "POST",
          body: formData,
          credentials: "include",
        },
      );

      const data = await response.json();

      if (data.success) {
        this.showScaleFormStatus(
          data.message || "Escala creada exitosamente",
          "success",
        );

        // Agregar la nueva escala a la lista
        const nuevaEscala = {
          id_tipo_escala: data.id_tipo_escala,
          nombre: nombre,
          descripcion: descripcion,
          opciones: opciones.map((op, idx) => ({
            id_opcion: data.opciones_ids ? data.opciones_ids[idx] : null,
            texto_opcion: op.texto_opcion,
            valor_puntuacion: op.valor_puntuacion,
          })),
        };

        this.tiposEscalas.push(nuevaEscala);
        this.renderTiposEscalas();

        // Seleccionar automáticamente la nueva escala
        document.getElementById("tipoEscala").value = data.id_tipo_escala;
        document.getElementById("tipoEscala").dataset.previousValue =
          data.id_tipo_escala;
        this.onTipoEscalaChange(data.id_tipo_escala);

        // Cerrar modal después de un breve delay
        setTimeout(() => {
          this.closeScaleModal();
          this.showNotification(
            "Escala creada y seleccionada exitosamente",
            "success",
          );
        }, 1000);
      } else {
        this.showScaleFormStatus(
          data.message || "Error al crear la escala",
          "error",
        );
      }
    } catch (err) {
      console.error(err);
      this.showScaleFormStatus("Error al conectar con el servidor", "error");
    } finally {
      this.setScaleSaveButtonLoading(false);
    }
  }

  /**
   * Recopilar opciones de la escala
   */
  collectOpcionesScale() {
    const opciones = [];
    const opcionCards = document.querySelectorAll(".opcion-scale-item");

    opcionCards.forEach((card) => {
      const textoInput = card.querySelector('input[name*="[texto]"]');
      const valorInput = card.querySelector('input[name*="[valor]"]');

      const texto = textoInput ? textoInput.value.trim() : "";
      const valor = valorInput ? parseInt(valorInput.value) : 0;

      if (texto) {
        opciones.push({
          texto_opcion: texto,
          valor_puntuacion: valor,
        });
      }
    });

    return opciones;
  }

  /**
   * Controlar estado de carga del botón guardar escala
   */
  setScaleSaveButtonLoading(isLoading) {
    const btn = document.getElementById("btnGuardarScale");
    const btnIcon = btn.querySelector(".fa-save");
    const btnLoading = btn.querySelector(".btn-loading");

    if (isLoading) {
      btn.classList.add("is-loading");
      btn.disabled = true;
      if (btnIcon) btnIcon.style.display = "none";
      if (btnLoading) btnLoading.style.display = "inline-flex";
    } else {
      btn.classList.remove("is-loading");
      btn.disabled = false;
      if (btnIcon) btnIcon.style.display = "inline";
      if (btnLoading) btnLoading.style.display = "none";
    }
  }

  /**
   * Mostrar estado en el formulario de escala
   */
  showScaleFormStatus(message, type = "info") {
    const statusDiv = document.getElementById("scaleFormStatus");
    if (!statusDiv) return;

    statusDiv.className = "form-status status-" + type;
    statusDiv.innerHTML = `
      <i class="fas fa-${
        type === "success"
          ? "check-circle"
          : type === "error"
            ? "exclamation-circle"
            : type === "warning"
              ? "exclamation-triangle"
              : "info-circle"
      }"></i>
      <span>${message}</span>
    `;
    statusDiv.style.display = "flex";

    // Auto-ocultar después de 5 segundos si es success
    if (type === "success") {
      setTimeout(() => {
        statusDiv.style.display = "none";
      }, 5000);
    }
  }

  /**
   * Mostrar notificación
   */
  showNotification(message, type = "info") {
    // Usar el Toast global si está disponible
    if (window.Toast) {
      window.Toast.show({
        message: message,
        type: type,
        duration: 3000,
      });
    } else {
      // Fallback: mostrar en consola
      console.warn("Toast no está disponible:", message);
    }
  }

  /**
   * Escapar HTML
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }
}

// Inicializar cuando el DOM esté listo
window.adminTests = null;
document.addEventListener("DOMContentLoaded", () => {
  window.adminTests = new AdminTestsManager();
});
