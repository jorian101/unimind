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
      sidebar.classList.toggle("sidebar--collapsed");
    };
  }
});
