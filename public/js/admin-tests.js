/**
 * admin-tests.js
 * Manejo de CRUD de Tests para Administrador
 */

class AdminTestsManager {
  constructor() {
    this.currentTestId = null;
    this.opcionesDisponibles = [];
    this.itemCount = 0;

    this.init();
  }

  init() {
    this.setupEventListeners();
    this.loadOpciones();
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
    document.getElementById("searchTest").addEventListener("input", (e) => {
      this.filterTests(e.target.value);
    });

    // Ordenamiento
    document.getElementById("sortTests").addEventListener("change", (e) => {
      this.sortTests(e.target.value);
    });

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
  }

  /**
   * Cargar opciones de respuesta disponibles
   */
  async loadOpciones() {
    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const response = await fetch(
        `${baseUrl}/controllers/TestsController.php?action=getOpciones`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success) {
        this.opcionesDisponibles = data.data;
        this.renderOpciones();
      } else if (data.offline) {
        // Modo offline detectado - no operable actualmente
      }
    } catch {
      // Silenciar error en entorno offline
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
   * Renderizar lista de tests
   */
  renderTests(tests) {
    const container = document.getElementById("testsGrid");
    const emptyState = document.getElementById("emptyState");

    if (!tests || tests.length === 0) {
      this.showEmptyState();
      return;
    }

    emptyState.style.display = "none";
    container.innerHTML = "";

    tests.forEach((test) => {
      const testCard = this.createTestCard(test);
      container.appendChild(testCard);
    });
  }

  /**
   * Crear tarjeta de test
   */
  createTestCard(test) {
    const card = document.createElement("div");
    card.className = "test-card";
    card.dataset.testId = test.id_test;
    // Añadir created_at como atributo data para ordenar por fecha
    if (test.created_at) {
      card.dataset.created = test.created_at;
    }
    card.dataset.testName = test.nombre;
    const isLocal = test.isLocal || String(test.id_test).startsWith("local");
    const actionsDisabled = isLocal
      ? 'disabled style="opacity:0.5;cursor:not-allowed;pointer-events:none;"'
      : "";
    const syncBadge = isLocal
      ? '<span class="sync-badge" style="color:#f59e0b;font-size:0.85em;margin-left:8px;" title="Pendiente de sincronización"><i class="fas fa-sync-alt"></i> Pendiente</span>'
      : "";

    card.innerHTML = `
            <div class="test-card-header">
                <h3>${this.escapeHtml(test.nombre)} ${syncBadge}</h3>
                <div class="test-actions-inline">
                    <button class="btn-icon" title="${isLocal ? "No disponible offline" : "Ver detalles"}" onclick="adminTests.viewTest('${test.id_test}')" ${actionsDisabled}>
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit" title="${isLocal ? "No disponible offline" : "Editar"}" onclick="adminTests.editTest('${test.id_test}')" ${actionsDisabled}>
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" title="${isLocal ? "No disponible offline" : "Eliminar"}" onclick="adminTests.deleteTest('${test.id_test}', '${this.escapeHtml(test.nombre)}')" ${actionsDisabled}>
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <p class="test-description">${this.escapeHtml(test.descripcion)}</p>
            <div class="test-meta">
                <div class="meta-item">
                    <i class="fas fa-list-ol"></i>
                    <span><strong>${test.num_items}</strong> ítems</span>
                </div>
                  <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>${test.created_at ? new Date(test.created_at).toLocaleString() : "—"}</span>
                  </div>
                  ${
                    test.updated_at
                      ? `
                  <div class="meta-item">
                    <i class="fas fa-edit"></i>
                    <span>Últ. edición: ${new Date(test.updated_at).toLocaleString()}</span>
                  </div>`
                      : ""
                  }
            </div>
        `;

    return card;
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
      const subescalaId = `readonly-subescala-${item.orden}`;

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
                <div class="item-form-group">
                    <label for="${subescalaId}">Subescala:</label>
                    <input type="text" 
                           id="${subescalaId}" 
                           name="readonly_items[${item.orden}][subescala]" 
                           value="${this.escapeHtml(item.subescala || "General")}" 
                           disabled>
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
      const itemDiv = this.createItemCard(
        this.itemCount,
        item.texto_item,
        item.subescala,
      );
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
  createItemCard(orden, textoItem = "", subescala = "") {
    const itemDiv = document.createElement("div");
    itemDiv.className = "item-card";
    itemDiv.dataset.orden = orden;

    // Generar IDs únicos para los campos
    const textoId = `item-texto-${orden}`;
    const subescalaId = `item-subescala-${orden}`;

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

    // Crear grupo de subescala
    const subescalaGroup = document.createElement("div");
    subescalaGroup.className = "item-form-group";

    const subescalaLabel = document.createElement("label");
    subescalaLabel.setAttribute("for", subescalaId);
    subescalaLabel.innerHTML =
      '<i class="fas fa-tag"></i> Subescala (opcional)';

    const subescalaInput = document.createElement("input");
    subescalaInput.type = "text";
    subescalaInput.id = subescalaId;
    subescalaInput.name = `items[${orden}][subescala]`;
    subescalaInput.className = "item-subescala";
    subescalaInput.placeholder =
      "Ejemplo: Ansiedad emocional, Ansiedad física, etc.";
    subescalaInput.maxLength = 100;
    subescalaInput.value = subescala;

    const subescalaHint = document.createElement("small");
    subescalaHint.className = "form-hint";
    subescalaHint.textContent = "Categoría o dimensión que evalúa este ítem";

    subescalaGroup.appendChild(subescalaLabel);
    subescalaGroup.appendChild(subescalaInput);
    subescalaGroup.appendChild(subescalaHint);

    // Ensamblar el itemDiv
    itemDiv.appendChild(header);
    itemDiv.appendChild(preguntaGroup);
    itemDiv.appendChild(subescalaGroup);

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
    // El número de ítems se calcula a partir de las tarjetas (no editable manualmente)
    const numItems = document.querySelectorAll(".item-card").length;

    if (!nombre || !descripcion || numItems <= 0) {
      this.showFormStatus(
        "Por favor completa todos los campos obligatorios. Revisa el nombre, descripción y agrega al menos 1 ítem.",
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
      const subescala =
        card.querySelector(".item-subescala").value.trim() || "General";
      const orden = parseInt(card.dataset.orden) || items.length + 1;

      if (textoItem) {
        items.push({
          texto_item: textoItem,
          subescala: subescala,
          orden: orden,
        });
      }
    });

    return items;
  }

  /**
   * Filtrar tests por búsqueda
   */
  filterTests(searchTerm) {
    const cards = document.querySelectorAll(".test-card");
    const term = searchTerm.toLowerCase();
    let visibleCount = 0;

    cards.forEach((card) => {
      const nombre = card.dataset.testName.toLowerCase();
      const shouldShow = nombre.includes(term);

      card.style.display = shouldShow ? "block" : "none";
      if (shouldShow) visibleCount++;
    });

    // Mostrar estado vacío si no hay resultados
    const emptyState = document.getElementById("emptyState");
    if (visibleCount === 0) {
      emptyState.style.display = "block";
      emptyState.querySelector("h3").textContent = "No se encontraron tests";
      emptyState.querySelector("p").textContent = "Intenta con otra búsqueda";
    } else {
      emptyState.style.display = "none";
    }
  }

  /**
   * Ordenar tests
   */
  sortTests(sortBy) {
    const container = document.getElementById("testsGrid");
    const cards = Array.from(container.querySelectorAll(".test-card"));
    // no-op: el contador de ítems es un display y se actualiza desde las tarjetas
    cards.sort((a, b) => {
      switch (sortBy) {
        case "nombre":
          return a.dataset.testName.localeCompare(b.dataset.testName);
        case "num_items":
          const itemsA = parseInt(
            a.querySelector(".meta-item strong").textContent,
          );
          const itemsB = parseInt(
            b.querySelector(".meta-item strong").textContent,
          );
          return itemsB - itemsA;
        case "fecha":
          // Ordenar por fecha de creación (más recientes primero)
          const dateA = a.dataset.created
            ? new Date(a.dataset.created)
            : new Date(0);
          const dateB = b.dataset.created
            ? new Date(b.dataset.created)
            : new Date(0);
          return dateB - dateA;
        default:
          return 0;
      }
    });

    // Reordenar en el DOM
    cards.forEach((card) => container.appendChild(card));
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
   * Mostrar notificación
   */
  showNotification(message, type = "info") {
    // Crear notificación
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
            <div class="notification-content">
                <i class="fas fa-${
                  type === "success"
                    ? "check-circle"
                    : type === "error"
                      ? "exclamation-circle"
                      : "info-circle"
                }"></i>
                <span>${message}</span>
            </div>
        `;

    // Agregar estilos si no existen
    if (!document.getElementById("notification-styles")) {
      const style = document.createElement("style");
      style.id = "notification-styles";
      style.textContent = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 1rem 1.5rem;
                    border-radius: 8px;
                    background: white;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    z-index: 10000;
                    animation: slideIn 0.3s ease;
                }
                .notification-success { border-left: 4px solid #10b981; }
                .notification-error { border-left: 4px solid #ef4444; }
                .notification-warning { border-left: 4px solid #f59e0b; }
                .notification-info { border-left: 4px solid #3b82f6; }
                .notification-content {
                    display: flex;
                    align-items: center;
                    gap: 0.75rem;
                }
                @keyframes slideIn {
                    from { transform: translateX(400px); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
      document.head.appendChild(style);
    }

    document.body.appendChild(notification);

    // Eliminar después de 3 segundos
    setTimeout(() => {
      notification.style.animation = "slideIn 0.3s ease reverse";
      setTimeout(() => notification.remove(), 300);
    }, 3000);
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
