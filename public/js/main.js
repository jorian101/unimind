document.addEventListener("DOMContentLoaded", function () {
  const sidebar = document.getElementById("sidebar");
  const main = document.querySelector("main");
  const toggle = document.querySelector(".menu-toggle");
  const roleSelector = document.getElementById("roleSelector");
  const menuItems = document.querySelectorAll(".sidebar-item");

  if (toggle) {
    toggle.addEventListener("click", function () {
      const isCollapsed = sidebar.classList.toggle("collapsed");

      if (main) {
        main.style.marginLeft = isCollapsed ? "60px" : "280px";
      }

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

      const newUrl = `?role=${role}&page=${page}`;
      history.pushState({ role, page, title: menuLabel }, "", newUrl);

      if (window.headerComponent) {
        const breadcrumb =
          page === "dashboard" ? [menuLabel] : ["Inicio", menuLabel];

        window.headerComponent.updateHeaderProps({
          title: menuLabel,
          breadcrumb: breadcrumb,
        });
      }

      loadPage(role, page);

      menuItems.forEach((i) => i.classList.remove("active"));
      this.classList.add("active");
    });
  });

  if (roleSelector) {
    roleSelector.addEventListener("change", function () {
      const newRole = this.value;
      const newUrl = `?role=${newRole}&page=dashboard`;
      window.location.href = newUrl;
    });
  }

  window.addEventListener("popstate", function (event) {
    if (event.state) {
      loadPage(event.state.role, event.state.page);

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

  async function loadPage(role, page) {
    try {
      const response = await fetch(
        `utils/page-loader.php?role=${role}&page=${page}`,
      );
      const content = await response.text();
      document.getElementById("main-content").innerHTML = content;
    } catch {
      document.getElementById("main-content").innerHTML =
        "<h1>Error</h1><p>No se pudo cargar la página</p>";
    }
  }
});
