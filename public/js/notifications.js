document.addEventListener("DOMContentLoaded", function () {
  // Avoid initializing twice if script is included multiple times
  if (window.__unimind_notif_initialized) return;
  window.__unimind_notif_initialized = true;

  // Find possible bell triggers (button with id, or header icon)
  const triggers = [];
  const byId = document.getElementById("notif-bell");
  if (byId) triggers.push(byId);
  // header icon (may be <i> element)
  const headerIcon = document.querySelector(".header-icons .icon.fas.fa-bell");
  if (headerIcon) triggers.push(headerIcon);
  // page header variant
  const pageBell = document.querySelector(".notif-root #notif-bell");
  if (pageBell && !triggers.includes(pageBell)) triggers.push(pageBell);

  if (!triggers.length) return; // no bell in DOM
  console.debug("[notif] triggers found:", triggers);

  // Ensure there is a #notif-count element; if not, create and append to first trigger
  let countEl = document.getElementById("notif-count");
  if (!countEl) {
    countEl = document.createElement("span");
    countEl.id = "notif-count";
    countEl.className = "notif-count";
    countEl.style.display = "none";
    // append to first trigger (if it's an <i>, append to its parent)
    const targetForCount = triggers[0];
    if (targetForCount.tagName === "I") {
      if (targetForCount.parentElement)
        targetForCount.parentElement.appendChild(countEl);
      else document.body.appendChild(countEl);
    } else {
      targetForCount.appendChild(countEl);
    }
  }

  // Ensure there is a dropdown container; if not, create one attached to body
  let dropdown = document.getElementById("notif-dropdown");
  if (!dropdown) {
    dropdown = document.createElement("div");
    dropdown.id = "notif-dropdown";
    dropdown.className = "notif-dropdown";
    dropdown.style.display = "none";
    dropdown.style.position = "absolute";
    dropdown.style.zIndex = "10000";
    // sensible default styles so it's visible even without CSS
    dropdown.style.background = "#fff";
    dropdown.style.border = "1px solid rgba(0,0,0,0.12)";
    dropdown.style.boxShadow = "0 6px 20px rgba(0,0,0,0.12)";
    dropdown.style.padding = "8px";
    dropdown.style.maxHeight = "60vh";
    dropdown.style.overflowY = "auto";
    document.body.appendChild(dropdown);
  }

  let lastAnchor = null;

  async function fetchNotifs() {
    try {
      console.debug("[notif] fetchNotifs start");
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const res = await fetch(`${baseUrl}/api/notifications.php`, {
        credentials: "include",
      });
      if (!res.ok) {
        console.debug("[notif] fetch returned not ok", res.status);
        return;
      }
      const data = await res.json();
      if (!data.success) {
        console.debug("[notif] api returned success=false", data);
        return;
      }

      console.debug(
        "[notif] fetch success, notifications:",
        data.notifications,
      );
      renderNotifications(data.notifications || []);
    } catch {
      console.debug("[notif] fetch error");
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

  // Helper to position and toggle dropdown under the clicked anchor
  function showDropdownFor(anchor) {
    if (!anchor) return;
    console.debug("[notif] showDropdownFor anchor=", anchor);
    const rect = anchor.getBoundingClientRect();
    // position dropdown to the right edge if needed
    dropdown.style.minWidth = "240px";
    dropdown.style.left = rect.left + window.scrollX + "px";
    dropdown.style.top = rect.bottom + window.scrollY + 6 + "px";
    dropdown.style.display = "block";
    lastAnchor = anchor;
    console.debug("[notif] dropdown shown");
  }

  function hideDropdown() {
    dropdown.style.display = "none";
    lastAnchor = null;
    console.debug("[notif] dropdown hidden");
  }

  // Attach click handlers to all triggers
  triggers.forEach((el) => {
    el.addEventListener("click", function (ev) {
      console.debug("[notif] trigger click", ev.currentTarget);
      ev.stopPropagation();
      // If dropdown hidden -> fetch then show; if shown and anchor same -> toggle
      if (dropdown.style.display === "none" || dropdown.style.display === "") {
        fetchNotifs().then(() => showDropdownFor(ev.currentTarget));
      } else {
        // if clicking a different anchor, reposition
        if (lastAnchor !== ev.currentTarget) {
          showDropdownFor(ev.currentTarget);
        } else {
          hideDropdown();
        }
      }
    });
    // make sure bell icon shows pointer
    el.style.cursor = "pointer";
  });

  // Close when clicking outside
  document.addEventListener("click", function (e) {
    if (dropdown.style.display === "none" || dropdown.style.display === "")
      return;
    const isInside =
      dropdown.contains(e.target) || triggers.some((t) => t.contains(e.target));
    if (!isInside) hideDropdown();
  });

  // initial fetch and poll
  fetchNotifs();
  setInterval(fetchNotifs, 20000);
});
