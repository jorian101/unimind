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
    this.setupProfileMenu();
    this.syncWithSidebar();
    this.setupResponsiveHandler();
  }

  setupActionButtons() {
    this.actionButtons.forEach((button) => {
      button.addEventListener("click", () => {
        const title = button.getAttribute("title");
        this.handleActionClick(title, button);
      });
    });
  }

  handleActionClick(action, button) {
    switch (action) {
      case "Notificaciones":
        this.showNotifications();
        break;
      default:
        break;
    }

    button.style.transform = "scale(0.95)";
    setTimeout(() => {
      button.style.transform = "";
    }, 150);
  }

  setupUserMenu() {
    if (this.userInfo) {
      this.userInfo.addEventListener("click", () => {
        this.showUserMenu();
      });
    }
  }

  setupProfileMenu() {
    const profileToggle = document.getElementById("profileToggle");
    const profileMenu = document.getElementById("profileMenu");

    if (profileToggle && profileMenu) {
      profileToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        const isVisible = profileMenu.classList.contains("visible");
        if (isVisible) {
          this.closeProfileMenu();
        } else {
          this.openProfileMenu();
        }
      });

      // Close menu when clicking outside
      document.addEventListener("click", (e) => {
        if (!profileToggle.contains(e.target)) {
          this.closeProfileMenu();
        }
      });
    }
  }

  openProfileMenu() {
    const profileToggle = document.getElementById("profileToggle");
    const profileMenu = document.getElementById("profileMenu");
    if (profileMenu) {
      profileMenu.classList.add("visible");
      profileToggle.classList.add("active");
    }
  }

  closeProfileMenu() {
    const profileToggle = document.getElementById("profileToggle");
    const profileMenu = document.getElementById("profileMenu");
    if (profileMenu) {
      profileMenu.classList.remove("visible");
      profileToggle.classList.remove("active");
    }
  }

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

  syncWithSidebar() {
    document.addEventListener("sidebarToggle", (e) => {
      this.updateHeaderPosition(e.detail.collapsed);
    });
  }

  updateHeaderPosition(collapsed = null) {
    if (collapsed === null) {
      collapsed = this.sidebar?.classList.contains("collapsed");
    }

    document.body.classList.toggle("sidebar-collapsed", collapsed);
  }

  setupResponsiveHandler() {
    const breakpoints = [
      { max: 390, class: "mobile-small" },
      { max: 768, class: "mobile" },
      { max: 1024, class: "tablet" },
      { min: 1441, class: "desktop-large" },
    ];

    const updateResponsive = () => {
      const width = window.innerWidth;

      breakpoints.forEach((bp) => {
        this.header?.classList.remove(bp.class);
      });

      for (const bp of breakpoints) {
        if ((bp.max && width <= bp.max) || (bp.min && width >= bp.min)) {
          this.header?.classList.add(bp.class);
          break;
        }
      }
    };

    window.addEventListener("resize", updateResponsive);
    updateResponsive();
  }

  showNotifications() {
    // Implementar panel de notificaciones
  }
}

function initializeHeader(customProps = {}) {
  document.addEventListener("DOMContentLoaded", () => {
    const headerComponent = new HeaderComponent();

    if (Object.keys(customProps).length > 0) {
      headerComponent.updateHeaderProps(customProps);
    }

    window.headerComponent = headerComponent;
  });
}

function setHeaderProps(props) {
  if (window.headerComponent) {
    window.headerComponent.updateHeaderProps(props);
  } else {
    window.pendingHeaderProps = props;
    document.addEventListener("DOMContentLoaded", () => {
      if (window.pendingHeaderProps && window.headerComponent) {
        window.headerComponent.updateHeaderProps(window.pendingHeaderProps);
        delete window.pendingHeaderProps;
      }
    });
  }
}

initializeHeader();

window.setHeaderProps = setHeaderProps;
window.initializeHeader = initializeHeader;
