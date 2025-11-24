document.addEventListener("DOMContentLoaded", function () {
  const bell = document.getElementById("notif-bell");
  const countEl = document.getElementById("notif-count");
  const dropdown = document.getElementById("notif-dropdown");

  async function fetchNotifs() {
    try {
      const res = await fetch("/api/notifications.php");
      if (!res.ok) return;
      const data = await res.json();
      if (!data.success) return;

      renderNotifications(data.notifications || []);
    } catch {
      // silent fail
    }
  }

  function renderNotifications(items) {
    const unread = items.filter((n) => n.leido == 0).length;
    if (unread > 0) {
      countEl.textContent = String(unread);
      countEl.style.display = "inline-block";
    } else {
      countEl.style.display = "none";
    }

    dropdown.innerHTML = "";
    if (!items.length) {
      dropdown.innerHTML =
        '<div class="notif-empty">No hay notificaciones</div>';
      return;
    }

    const list = document.createElement("div");
    list.className = "notif-list";
    items.forEach((n) => {
      const item = document.createElement("div");
      item.className = "notif-item" + (n.leido == 0 ? " notif-unread" : "");
      const meta = n.metadata ? JSON.parse(n.metadata) : null;
      let body =
        '<div class="notif-message">' + escapeHtml(n.mensaje) + "</div>";
      if (meta && meta.id_test) {
        body +=
          '<div class="notif-action"><a href="/estudiante/tests.php?from_notif=1&id_test=' +
          encodeURIComponent(meta.id_test) +
          '">Ver test</a></div>';
      }
      body += '<div class="notif-time">' + escapeHtml(n.creado_en) + "</div>";
      item.innerHTML = body;
      list.appendChild(item);
    });
    dropdown.appendChild(list);
  }

  function escapeHtml(str) {
    if (!str) return "";
    return String(str)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  bell.addEventListener("click", function () {
    if (dropdown.style.display === "none" || dropdown.style.display === "") {
      dropdown.style.display = "block";
    } else {
      dropdown.style.display = "none";
    }
  });

  // initial fetch and poll
  fetchNotifs();
  setInterval(fetchNotifs, 20000);
});
