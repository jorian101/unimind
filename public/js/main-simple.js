document.addEventListener("DOMContentLoaded", function () {
  console.log("JavaScript cargado correctamente");

  const menuItems = document.querySelectorAll(".sidebar__item");
  console.log("Items del menú encontrados:", menuItems.length);

  menuItems.forEach(function (item, index) {
    console.log("Procesando item", index, item.dataset);

    item.style.cursor = "pointer";

    item.onclick = function () {
      const page = this.dataset.page;
      const role = this.dataset.role;

      console.log("CLICK! Navegando a:", page, role);

      if (page && role) {
        window.location.href = "?role=" + role + "&page=" + page;
      }
    };
  });

  const roleSelector = document.getElementById("roleSelector");
  if (roleSelector) {
    roleSelector.onchange = function () {
      window.location.href = "?role=" + this.value + "&page=dashboard";
    };
  }

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
