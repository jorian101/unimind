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
      const response = await fetch(
        `${base}/controllers/TestsController.php?action=getOpciones`,
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
    try {
      const base = window.UNIMIND_BASE || "";
      const response = await fetch(
        `${base}/controllers/TestsController.php?action=getAll`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success) {
        this.renderTests(data.data);
      } else if (data.offline) {
        // Modo offline - mostrar mensaje informativo en lugar de error
        this.showEmptyState();
      } else {
        this.showEmptyState();
      }
    } catch {
      // Silenciar error en entorno offline
      this.showEmptyState();
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
    card.dataset.testName = test.nombre;

    card.innerHTML = `
            <div class="test-card-header">
                <h3>${this.escapeHtml(test.nombre)}</h3>
                <div class="test-actions-inline">
                    <button class="btn-icon" title="Ver detalles" onclick="adminTests.viewTest(${
                      test.id_test
                    })">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn-icon edit" title="Editar" onclick="adminTests.editTest(${
                      test.id_test
                    })">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-icon delete" title="Eliminar" onclick="adminTests.deleteTest(${
                      test.id_test
                    }, '${this.escapeHtml(test.nombre)}')">
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
                    <span>Creado</span>
                </div>
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

    // Establecer valor por defecto
    document.getElementById("numItems").value = 10;

    this.openModal();
  }

  /**
   * Ver detalles de un test
   */
  async viewTest(id_test) {
    try {
      const base = window.UNIMIND_BASE || "";
      const response = await fetch(
        `${base}/controllers/TestsController.php?action=getById&id_test=${id_test}`,
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
    document.getElementById("numItems").value = test.num_items;

    // Deshabilitar campos
    document.getElementById("nombreTest").disabled = true;
    document.getElementById("descripcionTest").disabled = true;
    document.getElementById("numItems").disabled = true;

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
      itemDiv.innerHTML = `
                <div class="item-card-header">
                    <span class="item-number">Ítem ${item.orden}</span>
                </div>
                <div class="item-form-group">
                    <label>Pregunta:</label>
                    <textarea disabled>${this.escapeHtml(
                      item.texto_item,
                    )}</textarea>
                </div>
                <div class="item-form-group">
                    <label>Subescala:</label>
                    <input type="text" value="${this.escapeHtml(
                      item.subescala || "General",
                    )}" disabled>
                </div>
            `;
      container.appendChild(itemDiv);
    });
  }

  /**
   * Editar un test
   */
  async editTest(id_test) {
    try {
      const base = window.UNIMIND_BASE || "";
      const response = await fetch(
        `${base}/controllers/TestsController.php?action=getById&id_test=${id_test}`,
        { credentials: "include" },
      );
      const data = await response.json();

      if (data.success) {
        this.openEditModal(data.data);
      } else {
        this.showNotification("Error al cargar el test", "error");
      }
    } catch {
      this.showNotification("Error al cargar el test", "error");
    }
  }

  /**
   * Abrir modal en modo edición
   */
  openEditModal(test) {
    this.currentTestId = test.id_test;
    document.getElementById("modalTitle").innerHTML =
      '<i class="fas fa-edit"></i> Editar Test';

    // Llenar formulario
    document.getElementById("testId").value = test.id_test;
    document.getElementById("nombreTest").value = test.nombre;
    document.getElementById("descripcionTest").value = test.descripcion;
    document.getElementById("numItems").value = test.num_items;

    // Habilitar campos
    document.getElementById("nombreTest").disabled = false;
    document.getElementById("descripcionTest").disabled = false;
    document.getElementById("numItems").disabled = false;

    // Cargar items en modo edición
    this.loadItemsForEdit(test.items);

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
  }

  /**
   * Eliminar test
   */
  deleteTest(id_test, nombre) {
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
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id_test", this.currentTestId);

      const response = await fetch(`${base}/controllers/TestsController.php`, {
        method: "POST",
        body: formData,
        credentials: "include",
      });

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
  }

  /**
   * Crear tarjeta de ítem
   */
  createItemCard(orden, textoItem = "", subescala = "") {
    const itemDiv = document.createElement("div");
    itemDiv.className = "item-card";
    itemDiv.dataset.orden = orden;

    itemDiv.innerHTML = `
            <div class="item-card-header">
                <span class="item-number">Ítem ${orden}</span>
                <button type="button" class="btn-remove-item" onclick="adminTests.removeItem(this)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="item-form-group">
                <label>Pregunta/Afirmación *</label>
                <textarea class="item-texto" placeholder="Ej: Me siento nervioso/a o ansioso/a" required>${this.escapeHtml(
                  textoItem,
                )}</textarea>
            </div>
            <div class="item-form-group">
                <label>Subescala (opcional)</label>
                <input type="text" class="item-subescala" placeholder="Ej: Ansiedad emocional" value="${this.escapeHtml(
                  subescala,
                )}">
            </div>
        `;

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
  }

  /**
   * Guardar test (crear o actualizar)
   */
  async saveTest() {
    // Validar datos básicos
    const nombre = document.getElementById("nombreTest").value.trim();
    const descripcion = document.getElementById("descripcionTest").value.trim();
    const numItems = parseInt(document.getElementById("numItems").value);

    if (!nombre || !descripcion || numItems <= 0) {
      this.showNotification(
        "Por favor completa todos los campos obligatorios",
        "warning",
      );
      return;
    }

    // Recopilar items
    const items = this.collectItems();

    if (items.length !== numItems) {
      this.showNotification(
        `El número de ítems (${items.length}) no coincide con el valor especificado (${numItems})`,
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

    try {
      const base = window.UNIMIND_BASE || "";
      const response = await fetch(`${base}/controllers/TestsController.php`, {
        method: "POST",
        body: formData,
        credentials: "include",
      });

      const data = await response.json();

      if (data.success) {
        this.showNotification(data.message, "success");
        this.closeModal();
        this.loadTests();
      } else {
        this.showNotification(data.message, "error");
      }
    } catch {
      this.showNotification("Error al guardar el test", "error");
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
          // Por defecto ya está ordenado por fecha
          return 0;
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
    document.getElementById("numItems").disabled = false;
    document.getElementById("btnAgregarItem").style.display = "inline-flex";
    document.querySelector(
      '.form-actions button[type="submit"]',
    ).style.display = "inline-flex";
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
