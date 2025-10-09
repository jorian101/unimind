document.addEventListener("DOMContentLoaded", function () {
  console.log("JavaScript cargado correctamente");

  // Get elements and initial state
  const sidebar = document.getElementById("sidebar");
  const body = document.body;
  const isMobile = window.innerWidth <= 768; // Changed to match CSS
  const savedCollapsed = localStorage.getItem("sidebarCollapsed") === "true";

  // Synchronize JavaScript state with CSS-applied initial state
  if (sidebar && !isMobile) {
    if (savedCollapsed) {
      sidebar.classList.add("sidebar--collapsed");
      body.classList.add("sidebar-collapsed");
    } else {
      sidebar.classList.remove("sidebar--collapsed");
      body.classList.remove("sidebar-collapsed");
    }
  }

  // Función para obtener el estado actual del sidebar
  function getCurrentSidebarState() {
    const sidebar = document.getElementById("sidebar");
    return sidebar ? sidebar.classList.contains("sidebar--collapsed") : false;
  }

  // Función para aplicar el estado del sidebar (solo para cambios dinámicos)
  function applySidebarState(collapsed) {
    const sidebar = document.getElementById("sidebar");
    const body = document.body;
    const isMobile = window.innerWidth <= 768; // Changed to match CSS

    if (!sidebar || isMobile) return;

    if (collapsed) {
      sidebar.classList.add("sidebar--collapsed");
      body.classList.add("sidebar-collapsed");
    } else {
      sidebar.classList.remove("sidebar--collapsed");
      body.classList.remove("sidebar-collapsed");
    }
  }

  // Función para actualizar los event listeners
  function updateMenuListeners() {
    const menuItems = document.querySelectorAll(".sidebar__item[data-page]");
    console.log("Items del menú encontrados:", menuItems.length);

    menuItems.forEach(function (item, index) {
      console.log("Procesando item", index, item.dataset);

      // Remover listeners previos
      item.replaceWith(item.cloneNode(true));
      const newItem = document.querySelectorAll(".sidebar__item[data-page]")[
        index
      ];

      newItem.style.cursor = "pointer";

      newItem.addEventListener("click", function (e) {
        // Evitar que el click en submenu-toggle active la navegación
        if (e.target.classList.contains("submenu-toggle")) {
          return;
        }

        const page = this.dataset.page;
        const role = this.dataset.role;

        console.log("CLICK! Navegando a:", page, role);

        if (page && role) {
          // Guardar el estado actual del sidebar antes de navegar
          const currentCollapsed = getCurrentSidebarState();
          localStorage.setItem("sidebarCollapsed", currentCollapsed.toString());

          // Force immediate visual feedback
          if (currentCollapsed) {
            document.documentElement.classList.add(
              "sidebar-initially-collapsed",
            );
          } else {
            document.documentElement.classList.remove(
              "sidebar-initially-collapsed",
            );
          }

          window.location.href = "?role=" + role + "&page=" + page;
        }
      });
    });

    // Configurar toggles de submenu
    const submenuToggles = document.querySelectorAll(".submenu-toggle");
    submenuToggles.forEach((toggle) => {
      toggle.addEventListener("click", function (e) {
        e.stopPropagation();
        e.preventDefault();

        const parentItem = this.closest(".sidebar__item");
        const submenu = parentItem.querySelector(".submenu");

        if (submenu) {
          submenu.classList.toggle("open");
          this.classList.toggle("rotated");
        }
      });
    });

    // Configurar clicks en items con submenu
    const submenuParents = Array.from(
      document.querySelectorAll(".sidebar__item"),
    ).filter((item) => item.querySelector(".submenu"));
    submenuParents.forEach((parent) => {
      parent.addEventListener("click", function (e) {
        // No toggle si se hace clic dentro del submenu
        if (this.querySelector(".submenu").contains(e.target)) return;

        const submenu = this.querySelector(".submenu");
        const toggle = this.querySelector(".submenu-toggle");
        if (submenu) {
          submenu.classList.toggle("open");
          toggle.classList.toggle("rotated");
        }
      });
    });
  }

  // Inicializar listeners
  updateMenuListeners();

  // Role selector
  const roleSelector = document.getElementById("roleSelector");
  if (roleSelector) {
    roleSelector.onchange = function () {
      // Guardar estado actual antes de cambiar de rol
      const currentCollapsed = getCurrentSidebarState();
      localStorage.setItem("sidebarCollapsed", currentCollapsed.toString());

      // Apply visual state immediately
      if (currentCollapsed) {
        document.documentElement.classList.add("sidebar-initially-collapsed");
      } else {
        document.documentElement.classList.remove(
          "sidebar-initially-collapsed",
        );
      }

      window.location.href = "?role=" + this.value;
    };
  }

  // Sidebar toggle
  const toggle = document.querySelector(".sidebar__menu-toggle");
  if (toggle) {
    toggle.onclick = function () {
      const sidebar = document.getElementById("sidebar");
      const body = document.body;
      const isMobile = window.innerWidth <= 768; // Changed to match CSS

      if (isMobile) {
        // En móvil (incluye 768px), alternar visibilidad
        sidebar.classList.toggle("sidebar--show");
        const overlay = document.getElementById("sidebar-overlay");
        if (sidebar.classList.contains("sidebar--show")) {
          overlay.style.display = "block";
          setTimeout(() => overlay.classList.add("show"), 10);
        } else {
          overlay.classList.remove("show");
          setTimeout(() => (overlay.style.display = "none"), 300);
        }
      } else {
        // En desktop, alternar colapso
        sidebar.classList.toggle("sidebar--collapsed");
        body.classList.toggle("sidebar-collapsed");

        // Update CSS state immediately
        const collapsed = sidebar.classList.contains("sidebar--collapsed");
        if (collapsed) {
          document.documentElement.classList.add("sidebar-initially-collapsed");
        } else {
          document.documentElement.classList.remove(
            "sidebar-initially-collapsed",
          );
        }

        // Guardar estado inmediatamente
        localStorage.setItem("sidebarCollapsed", collapsed.toString());
      }
    };
  }

  // Manejar redimensionado de ventana
  window.addEventListener("resize", function () {
    const sidebar = document.getElementById("sidebar");
    const body = document.body;
    const isMobile = window.innerWidth <= 768; // Changed to match CSS

    if (isMobile) {
      // En móvil, remover clases de desktop pero NO cambiar el estado guardado
      sidebar.classList.remove("sidebar--collapsed");
      body.classList.remove("sidebar-collapsed");
      document.documentElement.classList.remove("sidebar-initially-collapsed");
    } else {
      // En desktop, aplicar el estado guardado actual
      const savedCollapsed =
        localStorage.getItem("sidebarCollapsed") === "true";
      applySidebarState(savedCollapsed);

      // Update CSS state
      if (savedCollapsed) {
        document.documentElement.classList.add("sidebar-initially-collapsed");
      } else {
        document.documentElement.classList.remove(
          "sidebar-initially-collapsed",
        );
      }
    }
  });

  // Cerrar sidebar en móvil al hacer clic fuera de él
  document.addEventListener("click", function (event) {
    const sidebar = document.getElementById("sidebar");
    const toggle = document.querySelector(".sidebar__menu-toggle");
    const overlay = document.getElementById("sidebar-overlay");
    const isMobile = window.innerWidth <= 768;

    if (isMobile && sidebar.classList.contains("sidebar--show")) {
      // Si se hace clic fuera del sidebar y no es el botón toggle
      if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
        sidebar.classList.remove("sidebar--show");
        overlay.classList.remove("show");
        setTimeout(() => (overlay.style.display = "none"), 300);
      }
    }
  });

  // Cerrar sidebar al hacer clic en el overlay
  const overlay = document.getElementById("sidebar-overlay");
  if (overlay) {
    overlay.addEventListener("click", function () {
      const sidebar = document.getElementById("sidebar");
      sidebar.classList.remove("sidebar--show");
      overlay.classList.remove("show");
      setTimeout(() => (overlay.style.display = "none"), 300);
    });
  }
});
