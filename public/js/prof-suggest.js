// Handler for Suggest buttons on professor dashboard
(function () {
  function $all(sel, ctx) {
    return Array.from((ctx || document).querySelectorAll(sel));
  }

  function createSelect(courses) {
    const sel = document.createElement("select");
    sel.style.padding = ".4rem";
    sel.style.borderRadius = "6px";
    sel.style.border = "1px solid rgba(0,0,0,0.08)";
    sel.style.minWidth = "220px";
    courses.forEach((c) => {
      const opt = document.createElement("option");
      opt.value = c.id_curso;
      opt.textContent = c.nombre_curso;
      sel.appendChild(opt);
    });
    return sel;
  }

  function showPrompt(testKey, courses) {
    return new Promise((resolve) => {
      // simple modal using confirm / prompt fallback
      if (!courses || courses.length === 0) {
        alert("No se encontraron cursos asignados.");
        return resolve(null);
      }

      if (courses.length === 1) return resolve(courses[0].id_curso);

      // build lightweight modal
      const wrapper = document.createElement("div");
      wrapper.style.position = "fixed";
      wrapper.style.left = 0;
      wrapper.style.top = 0;
      wrapper.style.right = 0;
      wrapper.style.bottom = 0;
      wrapper.style.background = "rgba(0,0,0,0.35)";
      wrapper.style.display = "flex";
      wrapper.style.alignItems = "center";
      wrapper.style.justifyContent = "center";
      wrapper.style.zIndex = 10000;

      const box = document.createElement("div");
      box.style.background = "#fff";
      box.style.padding = "1rem";
      box.style.borderRadius = "10px";
      box.style.minWidth = "320px";
      box.style.boxShadow = "0 8px 24px rgba(2,6,23,0.12)";

      const title = document.createElement("div");
      title.textContent = "Selecciona el curso para la sugerencia";
      title.style.fontWeight = "700";
      title.style.marginBottom = ".6rem";

      const sel = createSelect(courses);

      const actions = document.createElement("div");
      actions.style.display = "flex";
      actions.style.justifyContent = "flex-end";
      actions.style.gap = ".5rem";
      actions.style.marginTop = ".8rem";

      const btnCancel = document.createElement("button");
      btnCancel.textContent = "Cancelar";
      btnCancel.className = "btn-outline";
      const btnOk = document.createElement("button");
      btnOk.textContent = "Enviar";
      btnOk.className = "btn-primary";

      actions.appendChild(btnCancel);
      actions.appendChild(btnOk);
      box.appendChild(title);
      box.appendChild(sel);
      box.appendChild(actions);
      wrapper.appendChild(box);
      document.body.appendChild(wrapper);

      btnCancel.addEventListener("click", () => {
        document.body.removeChild(wrapper);
        resolve(null);
      });
      btnOk.addEventListener("click", () => {
        const val = sel.value;
        document.body.removeChild(wrapper);
        resolve(val);
      });
    });
  }

  async function sendSuggest(id_test, id_curso, btn) {
    if (!id_test || !id_curso) {
      alert("No se puede enviar sugerencia: falta test o curso.");
      return;
    }
    try {
      btn.disabled = true;
      btn.dataset.orig = btn.textContent;
      btn.textContent = "Enviando...";
      const res = await fetch("/unimind/api/suggest_test.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          id_test: Number(id_test),
          id_curso: Number(id_curso),
        }),
      });
      const data = await res.json();
      if (res.ok && data.success) {
        btn.textContent = "Enviado";
        setTimeout(() => {
          btn.textContent = btn.dataset.orig || "Sugerir";
          btn.disabled = false;
        }, 2500);
        // small success indicator
        const msg = document.createElement("div");
        msg.textContent = data.message || "Sugerencia enviada";
        msg.style.position = "absolute";
        msg.style.background = "#081c15";
        msg.style.color = "#fff";
        msg.style.padding = ".45rem .6rem";
        msg.style.borderRadius = "6px";
        btn.parentElement.appendChild(msg);
        setTimeout(() => msg.remove(), 2500);
      } else {
        throw new Error(data.message || "Error servidor");
      }
    } catch (e) {
      alert("Error enviando sugerencia: " + e.message);
      btn.disabled = false;
      btn.textContent = btn.dataset.orig || "Sugerir";
    }
  }

  function init() {
    const cfg = window.__PROF_SUGGEST || { courses: [], tests: {} };
    const courses = cfg.courses || [];
    const tests = cfg.tests || {};

    $all(".btn-suggest").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const testKey = btn.getAttribute("data-test");
        const id_test = tests[testKey];
        const courseIdAttr = btn.getAttribute("data-course");
        let id_curso = courseIdAttr ? Number(courseIdAttr) : null;
        if (!id_curso) {
          // ask user to choose
          const chosen = await showPrompt(testKey, courses);
          if (!chosen) return;
          id_curso = chosen;
        }
        await sendSuggest(id_test, id_curso, btn);
      });
    });
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init);
  else init();
})();
