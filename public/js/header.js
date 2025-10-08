/**
 * Header Component JavaScript
 * Maneja la interactividad del header simplificado (notificaciones y perfil)
 */

class HeaderComponent {
  constructor() {
    this.header = document.getElementById("main-header");
    this.sidebar = document.getElementById("sidebar");
    this.actionButtons = document.querySelectorAll(".header-action-btn");
    this.userInfo = document.querySelector(".user-info");

    this.init();
  }

  init() {
    this.setupActionButtons();
    this.setupUserMenu();
    this.syncWithSidebar();
    this.setupResponsiveHandler();
  }

  // Configurar botones de acción
  setupActionButtons() {
    this.actionButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const title = button.getAttribute("title");
        this.handleActionClick(title, button);
      });
    });
  }

  // Manejar clics en botones de acción
  handleActionClick(action, button) {
    switch (action) {
      case "Notificaciones":
        this.showNotifications();
        break;
      default:
        // Acción no reconocida, no hacer nada
        break;
    }

    // Animación visual
    button.style.transform = "scale(0.95)";
    setTimeout(() => {
      button.style.transform = "";
    }, 150);
  }

  // Configurar menú de usuario
  setupUserMenu() {
    if (this.userInfo) {
      this.userInfo.addEventListener("click", () => {
        this.showUserMenu();
      });
    }
  }

  // Mostrar menú de usuario
  showUserMenu() {
    const existingMenu = document.querySelector(".user-dropdown");
    if (existingMenu) {
      existingMenu.remove();
      return;
    }

    const dropdown = document.createElement("div");
    dropdown.className = "user-dropdown";
    dropdown.innerHTML = `
            <div class="dropdown-item">Mi Perfil</div>
            <div class="dropdown-item">Configuración</div>
            <hr class="dropdown-divider">
            <div class="dropdown-item logout">Cerrar Sesión</div>
        `;

    this.userInfo.style.position = "relative";
    this.userInfo.appendChild(dropdown);

    // Cerrar al hacer clic fuera
    setTimeout(() => {
      document.addEventListener(
        "click",
        (e) => {
          if (!this.userInfo.contains(e.target)) {
            dropdown.remove();
          }
        },
        { once: true },
      );
    }, 100);
  }

  // Sincronizar con el estado del sidebar
  syncWithSidebar() {
    // Escuchar evento personalizado del sidebar
    document.addEventListener("sidebarToggle", (e) => {
      this.updateHeaderPosition(e.detail.collapsed);
    });
  }

  // Actualizar posición del header según el estado del sidebar
  updateHeaderPosition(collapsed = null) {
    if (collapsed === null) {
      collapsed = this.sidebar?.classList.contains("collapsed");
    }

    document.body.classList.toggle("sidebar-collapsed", collapsed);
  }

  // Configurar manejo responsive
  setupResponsiveHandler() {
    const breakpoints = [
      { max: 390, class: "mobile-small" },
      { max: 768, class: "mobile" },
      { max: 1024, class: "tablet" },
      { min: 1441, class: "desktop-large" },
    ];

    const updateResponsive = () => {
      const width = window.innerWidth;

      // Remover todas las clases responsive
      breakpoints.forEach((bp) => {
        this.header?.classList.remove(bp.class);
      });

      // Agregar clase apropiada
      for (const bp of breakpoints) {
        if ((bp.max && width <= bp.max) || (bp.min && width >= bp.min)) {
          this.header?.classList.add(bp.class);
          break;
        }
      }
    };

    window.addEventListener("resize", updateResponsive);
    updateResponsive(); // Ejecutar al inicializar
  }

  // Mostrar notificaciones
  showNotifications() {
    // Implementar panel de notificaciones
  }
}

// Función para inicializar el header con props personalizados
function initializeHeader(customProps = {}) {
  document.addEventListener("DOMContentLoaded", () => {
    const headerComponent = new HeaderComponent();

    if (Object.keys(customProps).length > 0) {
      headerComponent.updateHeaderProps(customProps);
    }

    // Exponer la instancia globalmente para uso externo
    window.headerComponent = headerComponent;
  });
}

// Función helper para uso desde PHP
function setHeaderProps(props) {
  if (window.headerComponent) {
    window.headerComponent.updateHeaderProps(props);
  } else {
    // Si aún no está inicializado, almacenar para cuando esté listo
    window.pendingHeaderProps = props;
    document.addEventListener("DOMContentLoaded", () => {
      if (window.pendingHeaderProps && window.headerComponent) {
        window.headerComponent.updateHeaderProps(window.pendingHeaderProps);
        delete window.pendingHeaderProps;
      }
    });
  }
}

// Auto-inicializar
initializeHeader();

// Exponer funciones globalmente
window.setHeaderProps = setHeaderProps;
window.initializeHeader = initializeHeader;
