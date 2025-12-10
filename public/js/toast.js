/**
 * toast.js
 * Sistema de notificaciones toast reutilizable tipo "snackbar"
 *
 * @author UniMind Team
 * @version 1.0.0
 *
 * Tipos de toast soportados:
 * - success: Éxito (verde)
 * - error: Error (rojo)
 * - warning: Advertencia (amarillo)
 * - info: Información (azul)
 */

class Toast {
  constructor() {
    this.toasts = [];
    this.maxToasts = 5;
    this.defaultDuration = 4000;
    this.containerCreated = false;
  }

  /**
   * Crear el contenedor de toasts en el DOM si no existe
   */
  createContainer() {
    if (this.containerCreated) return;

    let container = document.getElementById("unimind-toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "unimind-toast-container";
      container.className = "unimind-toast-container";
      document.body.appendChild(container);
    }

    this.containerCreated = true;
  }

  /**
   * Mostrar un toast
   *
   * @param {Object} options - Opciones del toast
   * @param {string} options.message - Mensaje a mostrar (requerido)
   * @param {string} options.type - Tipo de toast (success, error, warning, info) - por defecto 'info'
   * @param {number} options.duration - Duración en ms antes de auto-cerrar (por defecto 4000, 0 = no cerrar)
   * @param {string} options.icon - Clase de icono personalizado (opcional, sobrescribe el icono por defecto)
   * @param {string} options.actionLabel - Texto del botón de acción (opcional)
   * @param {Function} options.actionCallback - Callback al hacer clic en el botón de acción
   * @param {Function} options.onClose - Callback al cerrar el toast
   * @param {string} options.position - Posición del contenedor (top-right, top-left, bottom-right, bottom-left) - por defecto 'top-right'
   * @returns {Object} - Objeto con método close() para cerrar manualmente
   */
  show(options = {}) {
    const {
      message = "",
      type = "info",
      duration = this.defaultDuration,
      icon = Toast.ICONS[type] || Toast.ICONS.info,
      actionLabel = null,
      actionCallback = null,
      onClose = null,
      position = "top-right",
    } = options;

    if (!message) {
      console.warn("Toast: mensaje vacío");
      return;
    }

    this.createContainer();

    // Crear elemento del toast
    const toastElement = document.createElement("div");
    toastElement.className = `unimind-toast unimind-toast-${type}`;
    toastElement.setAttribute("role", "alert");
    toastElement.setAttribute("aria-live", "polite");

    // Construir HTML interno
    toastElement.innerHTML = `
      <div class="unimind-toast-inner">
        <i class="${icon} unimind-toast-icon"></i>
        <div class="unimind-toast-text">${this.escapeHtml(String(message))}</div>
        ${actionLabel ? `<button class="unimind-toast-action" type="button">${this.escapeHtml(String(actionLabel))}</button>` : ""}
        <button class="unimind-toast-close" type="button" aria-label="Cerrar">
          <i class="fas fa-times"></i>
        </button>
      </div>
    `;

    // Agregar al contenedor
    const container = document.getElementById("unimind-toast-container");

    // Ajustar posición del contenedor
    container.className = `unimind-toast-container unimind-toast-position-${position}`;

    container.appendChild(toastElement);

    // Limitar cantidad de toasts visibles
    if (this.toasts.length >= this.maxToasts) {
      const oldest = this.toasts.shift();
      if (oldest && oldest.element) {
        this.removeToast(oldest.element);
      }
    }

    // Registrar toast
    const toastObj = {
      element: toastElement,
      timeoutId: null,
      onClose,
    };
    this.toasts.push(toastObj);

    // Event listener para botón de cerrar
    const closeBtn = toastElement.querySelector(".unimind-toast-close");
    closeBtn.addEventListener("click", () => {
      this.removeToast(toastElement);
    });

    // Event listener para botón de acción
    if (actionLabel && actionCallback) {
      const actionBtn = toastElement.querySelector(".unimind-toast-action");
      actionBtn.addEventListener("click", (e) => {
        e.stopPropagation();
        try {
          if (typeof actionCallback === "function") {
            actionCallback();
          }
        } catch (err) {
          console.error("Error en acción de toast:", err);
        }
        this.removeToast(toastElement);
      });
    }

    // Animar entrada
    requestAnimationFrame(() => {
      toastElement.classList.add("unimind-toast-show");
    });

    // Auto-cerrar después de la duración especificada
    if (duration > 0) {
      toastObj.timeoutId = setTimeout(() => {
        this.removeToast(toastElement);
      }, duration);
    }

    // Retornar objeto con método close
    return {
      close: () => this.removeToast(toastElement),
    };
  }

  /**
   * Remover un toast del DOM
   */
  removeToast(element) {
    if (!element || !element.parentNode) return;

    // Buscar en el registro
    const index = this.toasts.findIndex((t) => t.element === element);
    if (index !== -1) {
      const toastObj = this.toasts[index];

      // Cancelar timeout si existe
      if (toastObj.timeoutId) {
        clearTimeout(toastObj.timeoutId);
      }

      // Llamar callback onClose si existe
      if (toastObj.onClose && typeof toastObj.onClose === "function") {
        try {
          toastObj.onClose();
        } catch (err) {
          console.error("Error en onClose del toast:", err);
        }
      }

      // Remover del registro
      this.toasts.splice(index, 1);
    }

    // Animar salida
    element.classList.remove("unimind-toast-show");
    element.classList.add("unimind-toast-hide");

    setTimeout(() => {
      if (element.parentNode) {
        element.parentNode.removeChild(element);
      }
    }, 300);
  }

  /**
   * Cerrar todos los toasts
   */
  closeAll() {
    const toastsCopy = [...this.toasts];
    toastsCopy.forEach((toast) => {
      if (toast.element) {
        this.removeToast(toast.element);
      }
    });
  }

  /**
   * Escapar HTML para prevenir XSS
   */
  escapeHtml(text) {
    const map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return String(text).replace(/[&<>"']/g, (m) => map[m]);
  }

  // ============================================
  // Métodos de conveniencia para cada tipo
  // ============================================

  /**
   * Mostrar toast de éxito
   */
  success(message, options = {}) {
    return this.show({
      message,
      type: "success",
      ...options,
    });
  }

  /**
   * Mostrar toast de error
   */
  error(message, options = {}) {
    return this.show({
      message,
      type: "error",
      ...options,
    });
  }

  /**
   * Mostrar toast de advertencia
   */
  warning(message, options = {}) {
    return this.show({
      message,
      type: "warning",
      ...options,
    });
  }

  /**
   * Mostrar toast de información
   */
  info(message, options = {}) {
    return this.show({
      message,
      type: "info",
      ...options,
    });
  }
}

// ============================================
// Asignaciones estáticas
// ============================================
Toast.ICONS = {
  success: "fas fa-check-circle",
  error: "fas fa-exclamation-circle",
  warning: "fas fa-exclamation-triangle",
  info: "fas fa-info-circle",
};

// ============================================
// Instancia global del toast
// ============================================
if (typeof window !== "undefined") {
  window.Toast = new Toast();
}

// Exportar para uso como módulo (opcional)
if (typeof module !== "undefined" && module.exports) {
  module.exports = Toast;
}
