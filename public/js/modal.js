/**
 * modal.js
 * Sistema de modal reutilizable y adaptable para diferentes casos de uso
 *
 * @author UniMind Team
 * @version 1.0.0
 *
 * Tipos de modal soportados:
 * - confirm: Confirmación con botones Sí/No o personalizado
 * - alert: Alerta informativa con un solo botón
 * - warning: Advertencia
 * - error: Error
 * - success: Éxito
 * - delete: Confirmación de eliminación (crítico)
 * - info: Información general
 * - custom: Modal personalizado con HTML personalizado
 */

class Modal {
  constructor() {
    this.modalContainer = null;
    this.currentResolve = null;
    this.currentReject = null;
    this.isOpen = false;
    this.createModalContainer();
  }

  /**
   * Crear el contenedor del modal en el DOM
   */
  createModalContainer() {
    if (document.getElementById("unimind-modal-container")) {
      this.modalContainer = document.getElementById("unimind-modal-container");
      return;
    }

    this.modalContainer = document.createElement("div");
    this.modalContainer.id = "unimind-modal-container";
    this.modalContainer.className = "unimind-modal-overlay";
    this.modalContainer.style.display = "none";
    document.body.appendChild(this.modalContainer);

    // Cerrar al hacer clic en el overlay (opcional según configuración)
    this.modalContainer.addEventListener("click", (e) => {
      // closeOnOverlay defaults to true; only skip when explicitly false
      if (
        e.target === this.modalContainer &&
        (!this.currentOptions || this.currentOptions.closeOnOverlay !== false)
      ) {
        this.close(false);
      }
    });
  }

  /**
   * Mostrar un modal
   *
   * @param {Object} options - Opciones del modal
   * @param {string} options.type - Tipo de modal (confirm, alert, warning, error, success, delete, info, custom)
   * @param {string} options.title - Título del modal
   * @param {string} options.message - Mensaje del modal (puede incluir HTML)
   * @param {string} options.html - HTML personalizado para el cuerpo (sobrescribe message)
   * @param {string} options.icon - Clase de icono personalizado (sobrescribe el icono por defecto del tipo)
   * @param {string} options.confirmText - Texto del botón de confirmación (por defecto según tipo)
   * @param {string} options.cancelText - Texto del botón de cancelación (por defecto "Cancelar")
   * @param {boolean} options.showCancel - Mostrar botón de cancelación (por defecto true para confirm y delete)
   * @param {boolean} options.closeOnOverlay - Cerrar al hacer clic fuera del modal (por defecto true)
   * @param {Function} options.onConfirm - Callback al confirmar
   * @param {Function} options.onCancel - Callback al cancelar
   * @param {Object} options.confirmButton - Opciones adicionales para el botón de confirmación
   * @param {Object} options.cancelButton - Opciones adicionales para el botón de cancelación
   * @param {string} options.width - Ancho personalizado del modal (ej: '500px', '80%')
   * @param {boolean} options.animate - Animar entrada/salida (por defecto true)
   * @returns {Promise} - Resuelve con true al confirmar, false al cancelar
   */
  show(options = {}) {
    return new Promise((resolve, reject) => {
      this.currentResolve = resolve;
      this.currentReject = reject;
      this.currentOptions = options;

      const {
        type = "alert",
        title = "Información",
        message = "",
        html = "",
        icon = Modal.ICONS[type] || Modal.ICONS.info,
        confirmText = this.getDefaultConfirmText(type),
        cancelText = "Cancelar",
        showCancel = ["confirm", "delete"].includes(type),
        animate = true,
        width = null,
        confirmButton = {},
        cancelButton = {},
      } = options;

      const theme = Modal.THEMES[type] || Modal.THEMES.info;

      // Construir HTML del modal
      const modalHTML = `
        <div class="unimind-modal ${theme} ${animate ? "modal-animate" : ""}" style="${width ? `max-width: ${width}` : ""}">
          <div class="unimind-modal-header">
            <div class="unimind-modal-icon">
              <i class="${icon}"></i>
            </div>
            <h2 class="unimind-modal-title">${this.escapeHtml(title)}</h2>
            <button class="unimind-modal-close" data-action="close">
              <i class="fas fa-times"></i>
            </button>
          </div>
          <div class="unimind-modal-body">
            ${html || `<p>${message}</p>`}
          </div>
          <div class="unimind-modal-footer">
            ${
              showCancel
                ? `
              <button class="unimind-modal-btn unimind-modal-btn-cancel ${cancelButton.class || ""}" 
                      data-action="cancel"
                      ${cancelButton.disabled ? "disabled" : ""}>
                ${cancelButton.icon ? `<i class="${cancelButton.icon}"></i>` : ""}
                ${cancelText}
              </button>
            `
                : ""
            }
            <button class="unimind-modal-btn unimind-modal-btn-confirm ${confirmButton.class || ""}" 
                    data-action="confirm"
                    ${confirmButton.disabled ? "disabled" : ""}>
              ${confirmButton.icon ? `<i class="${confirmButton.icon}"></i>` : ""}
              ${confirmText}
            </button>
          </div>
        </div>
      `;

      this.modalContainer.innerHTML = modalHTML;
      this.modalContainer.style.display = "flex";
      this.isOpen = true;

      // Aplicar animación
      if (animate) {
        requestAnimationFrame(() => {
          this.modalContainer.classList.add("modal-open");
        });
      }

      // Event listeners para los botones
      this.modalContainer.querySelectorAll("[data-action]").forEach((btn) => {
        btn.addEventListener("click", (e) => {
          const action = e.currentTarget.dataset.action;
          if (action === "confirm") {
            this.handleConfirm();
          } else if (action === "cancel" || action === "close") {
            this.handleCancel();
          }
        });
      });

      // Soporte para teclas Escape y Enter
      this.keyHandler = (e) => {
        if (e.key === "Escape" && showCancel !== false) {
          this.handleCancel();
        } else if (e.key === "Enter" && !showCancel) {
          this.handleConfirm();
        }
      };
      document.addEventListener("keydown", this.keyHandler);
    });
  }

  /**
   * Manejar confirmación
   */
  async handleConfirm() {
    if (this.currentOptions && this.currentOptions.onConfirm) {
      try {
        await this.currentOptions.onConfirm();
      } catch (error) {
        console.error("Error en onConfirm:", error);
      }
    }
    this.close(true);
  }

  /**
   * Manejar cancelación
   */
  async handleCancel() {
    if (this.currentOptions && this.currentOptions.onCancel) {
      try {
        await this.currentOptions.onCancel();
      } catch (error) {
        console.error("Error en onCancel:", error);
      }
    }
    this.close(false);
  }

  /**
   * Cerrar el modal
   */
  close(result) {
    if (!this.isOpen) return;

    // animate defaults to true unless explicitly false
    var animate = !(
      this.currentOptions && this.currentOptions.animate === false
    );

    if (animate) {
      this.modalContainer.classList.remove("modal-open");
      this.modalContainer.classList.add("modal-closing");

      setTimeout(() => {
        this.modalContainer.style.display = "none";
        this.modalContainer.classList.remove("modal-closing");
        this.cleanup(result);
      }, 300);
    } else {
      this.modalContainer.style.display = "none";
      this.cleanup(result);
    }
  }

  /**
   * Limpieza después de cerrar
   */
  cleanup(result) {
    if (this.keyHandler) {
      document.removeEventListener("keydown", this.keyHandler);
      this.keyHandler = null;
    }

    this.isOpen = false;
    if (this.currentResolve) {
      this.currentResolve(result);
      this.currentResolve = null;
      this.currentReject = null;
    }
  }

  /**
   * Obtener texto por defecto del botón de confirmación según tipo
   */
  getDefaultConfirmText(type) {
    const texts = {
      confirm: "Confirmar",
      alert: "Entendido",
      warning: "Aceptar",
      error: "Cerrar",
      success: "Continuar",
      delete: "Eliminar",
      info: "OK",
      custom: "Aceptar",
    };
    return texts[type] || "Aceptar";
  }

  /**
   * Escapar HTML para prevenir XSS
   */
  escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
  }

  // ============================================
  // Métodos de conveniencia para cada tipo
  // ============================================

  /**
   * Mostrar modal de confirmación
   */
  confirm(title, message, options = {}) {
    return this.show({
      type: "confirm",
      title,
      message,
      ...options,
    });
  }

  /**
   * Mostrar alerta
   */
  alert(title, message, options = {}) {
    return this.show({
      type: "alert",
      title,
      message,
      showCancel: false,
      ...options,
    });
  }

  /**
   * Mostrar advertencia
   */
  warning(title, message, options = {}) {
    return this.show({
      type: "warning",
      title,
      message,
      ...options,
    });
  }

  /**
   * Mostrar error
   */
  error(title, message, options = {}) {
    return this.show({
      type: "error",
      title,
      message,
      showCancel: false,
      ...options,
    });
  }

  /**
   * Mostrar éxito
   */
  success(title, message, options = {}) {
    return this.show({
      type: "success",
      title,
      message,
      showCancel: false,
      ...options,
    });
  }

  /**
   * Mostrar confirmación de eliminación (crítico)
   */
  delete(itemName, options = {}) {
    return this.show({
      type: "delete",
      title: "¿Eliminar elemento?",
      message: `¿Estás seguro que deseas eliminar "${itemName}"? Esta acción no se puede deshacer.`,
      confirmText: "Sí, eliminar",
      cancelText: "Cancelar",
      showCancel: true,
      ...options,
    });
  }

  /**
   * Mostrar información
   */
  info(title, message, options = {}) {
    return this.show({
      type: "info",
      title,
      message,
      showCancel: false,
      ...options,
    });
  }

  /**
   * Mostrar modal personalizado
   */
  custom(options = {}) {
    return this.show({
      type: "custom",
      ...options,
    });
  }
}

// ============================================
// Asignaciones estáticas (compatible con parsers más antiguos)
Modal.ICONS = {
  confirm: "fas fa-question-circle",
  alert: "fas fa-info-circle",
  warning: "fas fa-exclamation-triangle",
  error: "fas fa-times-circle",
  success: "fas fa-check-circle",
  delete: "fas fa-trash-alt",
  info: "fas fa-info-circle",
  custom: "fas fa-bell",
};

Modal.THEMES = {
  confirm: "modal-confirm",
  alert: "modal-alert",
  warning: "modal-warning",
  error: "modal-error",
  success: "modal-success",
  delete: "modal-delete",
  info: "modal-info",
  custom: "modal-custom",
};

// Instancia global del modal
// ============================================
if (typeof window !== "undefined") {
  window.Modal = new Modal();
}

// Exportar para uso como módulo (opcional)
if (typeof module !== "undefined" && module.exports) {
  module.exports = Modal;
}
