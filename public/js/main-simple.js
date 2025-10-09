document.addEventListener("DOMContentLoaded", function () {
  console.log("JavaScript cargado correctamente");

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
  }

  // Inicializar listeners
  updateMenuListeners();

  // Role selector
  const roleSelector = document.getElementById("roleSelector");
  if (roleSelector) {
    roleSelector.onchange = function () {
      window.location.href = "?role=" + this.value;
    };
  }

  // Sidebar toggle
  const toggle = document.querySelector(".sidebar__menu-toggle");
  if (toggle) {
    toggle.onclick = function () {
      const sidebar = document.getElementById("sidebar");
      const body = document.body;

      // Verificar si estamos en móvil
      const isMobile = window.innerWidth <= 768;

      if (isMobile) {
        // En móvil, alternar visibilidad
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
      }
    };
  }

  // Manejar redimensionado de ventana
  window.addEventListener("resize", function () {
    const sidebar = document.getElementById("sidebar");
    const body = document.body;
    const isMobile = window.innerWidth <= 768;

    if (isMobile) {
      // En móvil, remover clases de desktop
      sidebar.classList.remove("sidebar--collapsed");
      body.classList.remove("sidebar-collapsed");
    } else {
      // En desktop, remover clases de móvil
      sidebar.classList.remove("sidebar--show");
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
