// Sidebar toggle functionality
document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const main = document.querySelector("main");
  const toggle = document.querySelector(".menu-toggle");
  const roleSelector = document.getElementById("roleSelector");
  const menuItems = document.querySelectorAll(".sidebar-item");

  // Toggle sidebar
  if (toggle) {
    toggle.addEventListener("click", function () {
      const isCollapsed = sidebar.classList.toggle("collapsed");

      // Actualizar margen del main si existe
      if (main) {
        main.style.marginLeft = isCollapsed ? "60px" : "280px";
      }

      // Emitir evento personalizado para que el header se sincronice
      document.dispatchEvent(
        new CustomEvent("sidebarToggle", {
          detail: { collapsed: isCollapsed },
        }),
      );
    });
  }

  // Navigation SPA (Single Page Application)
  menuItems.forEach((item) => {
    item.addEventListener("click", function () {
      const page = this.dataset.page;
      const role = this.dataset.role;
      const menuLabel = this.querySelector("span").textContent;

      // Actualizar URL sin recargar página
      const newUrl = `?role=${role}&page=${page}`;
      history.pushState({ role, page, title: menuLabel }, "", newUrl);

      // Actualizar header con el título del menú seleccionado
      if (window.headerComponent) {
        // Generar breadcrumb apropiado
        const breadcrumb =
          page === "dashboard" ? [menuLabel] : ["Inicio", menuLabel];

        window.headerComponent.updateHeaderProps({
          title: menuLabel,
          breadcrumb: breadcrumb,
        });
      }

      // Cargar contenido
      loadPage(role, page);

      // Actualizar estado activo
      menuItems.forEach((i) => i.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // Cambio de rol
  if (roleSelector) {
    roleSelector.addEventListener("change", function () {
      const newRole = this.value;
      const newUrl = `?role=${newRole}&page=dashboard`;
      window.location.href = newUrl; // Recarga para cambiar sidebar
    });
  }

  // Manejar botón atrás/adelante del navegador
  window.addEventListener("popstate", function (event) {
    if (event.state) {
      loadPage(event.state.role, event.state.page);

      // Actualizar header si hay título en el estado
      if (event.state.title && window.headerComponent) {
        const breadcrumb =
          event.state.page === "dashboard"
            ? [event.state.title]
            : ["Inicio", event.state.title];

        window.headerComponent.updateHeaderProps({
          title: event.state.title,
          breadcrumb: breadcrumb,
        });
      }
    }
  });

  // Función para cargar contenido sin recargar página
  async function loadPage(role, page) {
    try {
      const response = await fetch(
        `utils/page-loader.php?role=${role}&page=${page}`,
      );
      const content = await response.text();
      document.getElementById("main-content").innerHTML = content;
    } catch {
      // Error al cargar página, mostrar mensaje por defecto
      document.getElementById("main-content").innerHTML =
        "<h1>Error</h1><p>No se pudo cargar la página</p>";
    }
  }
});
