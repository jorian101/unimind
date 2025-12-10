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
    // optional metadata provided on the button (when creating new test)
    const testName = btn.getAttribute("data-test-name") || null;
    const testDesc = btn.getAttribute("data-test-desc") || null;
    const testItems = btn.getAttribute("data-test-items")
      ? Number(btn.getAttribute("data-test-items"))
      : null;
    try {
      btn.disabled = true;
      btn.dataset.orig = btn.textContent;
      btn.textContent = "Enviando...";
      const res = await fetch("/unimind/api/suggest_test.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(
          Object.assign(
            {
              id_test: Number(id_test),
              id_curso: Number(id_curso),
            },
            testName
              ? {
                  test_name: testName,
                  test_description: testDesc || "",
                  num_items: testItems || 0,
                }
              : {},
          ),
        ),
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
        // If id_test is falsy (0/undefined) open a prompt to create a new test
        if (!id_test) {
          const meta = await showCreateTestPrompt();
          if (!meta) return;
          // attach metadata to button temporarily so sendSuggest includes it
          btn.setAttribute("data-test-name", meta.name);
          btn.setAttribute("data-test-desc", meta.description);
          btn.setAttribute("data-test-items", meta.num_items);
          // send with id_test = 0 to indicate create
          await sendSuggest(0, id_curso, btn);
          // cleanup attrs
          btn.removeAttribute("data-test-name");
          btn.removeAttribute("data-test-desc");
          btn.removeAttribute("data-test-items");
        } else {
          await sendSuggest(id_test, id_curso, btn);
        }
      });
    });
  }

  // small modal to create a test (name, description, num_items)
  function showCreateTestPrompt() {
    return new Promise((resolve) => {
      const wrapper = document.createElement("div");
      wrapper.style.position = "fixed";
      wrapper.style.inset = 0;
      wrapper.style.background = "rgba(0,0,0,0.35)";
      wrapper.style.display = "flex";
      wrapper.style.alignItems = "center";
      wrapper.style.justifyContent = "center";
      wrapper.style.zIndex = 11000;

      const box = document.createElement("div");
      box.style.background = "#fff";
      box.style.padding = "1rem";
      box.style.borderRadius = "10px";
      box.style.minWidth = "360px";
      box.style.boxShadow = "0 10px 30px rgba(2,6,23,0.12)";

      box.innerHTML = `
        <h3 style="margin:0 0 .6rem 0">Crear Test</h3>
        <label style="display:block;margin-bottom:.4rem">Nombre</label>
        <input type="text" id="__create_test_name" style="width:100%;padding:.5rem;border:1px solid #e6e6e6;border-radius:6px;margin-bottom:.6rem">
        <label style="display:block;margin-bottom:.4rem">Descripción (opcional)</label>
        <textarea id="__create_test_desc" style="width:100%;padding:.5rem;border:1px solid #e6e6e6;border-radius:6px;height:70px;margin-bottom:.6rem"></textarea>
        <label style="display:block;margin-bottom:.4rem">Número de ítems</label>
        <input type="number" id="__create_test_items" min="1" value="10" style="width:120px;padding:.4rem;border:1px solid #e6e6e6;border-radius:6px;margin-bottom:.6rem">
        <div style="display:flex;gap:.5rem;justify-content:flex-end;margin-top:.4rem">
          <button id="__create_cancel" class="btn-outline">Cancelar</button>
          <button id="__create_ok" class="btn-primary">Crear y Enviar</button>
        </div>
      `;

      wrapper.appendChild(box);
      document.body.appendChild(wrapper);

      wrapper
        .querySelector("#__create_cancel")
        .addEventListener("click", () => {
          document.body.removeChild(wrapper);
          resolve(null);
        });
      wrapper.querySelector("#__create_ok").addEventListener("click", () => {
        const name = wrapper.querySelector("#__create_test_name").value.trim();
        const desc = wrapper.querySelector("#__create_test_desc").value.trim();
        const num =
          parseInt(wrapper.querySelector("#__create_test_items").value, 10) ||
          0;
        if (!name || num <= 0) {
          alert("Por favor indica un nombre y un número de ítems mayor que 0.");
          return;
        }
        document.body.removeChild(wrapper);
        resolve({ name, description: desc, num_items: num });
      });
    });
  }

  if (document.readyState === "loading")
    document.addEventListener("DOMContentLoaded", init);
  else init();

  document.addEventListener("DOMContentLoaded", function () {
    // Botones sugerir
    document.querySelectorAll(".btn-suggest").forEach((btn) => {
      btn.addEventListener("click", function () {
        openSuggestModal(btn.dataset.test);
      });
    });

    // Modal y formulario
    const modal = document.getElementById("suggestModal");
    const closeBtn = document.getElementById("closeSuggestModal");
    const selectCourse = document.getElementById("selectCourse");
    const selectTest = document.getElementById("selectTest");
    const suggestForm = document.getElementById("suggestForm");
    const suggestMsg = document.getElementById("suggestMsg");

    closeBtn.onclick = () => {
      modal.style.display = "none";
      suggestMsg.textContent = "";
    };
    window.onclick = (e) => {
      if (e.target === modal) {
        modal.style.display = "none";
        suggestMsg.textContent = "";
      }
    };

    function openSuggestModal(testType) {
      // Cargar cursos asignados al profesor
      selectCourse.innerHTML = "";
      const cursos = window.__PROF_SUGGEST.courses || [];
      if (!cursos.length) {
        document.getElementById("suggestMsg").textContent =
          "No se encontraron cursos asignados.";
        modal.style.display = "block";
        return;
      }
      cursos.forEach((c) => {
        const opt = document.createElement("option");
        opt.value = c.id_curso;
        opt.textContent = c.nombre_curso;
        selectCourse.appendChild(opt);
      });
      selectTest.value = testType || "estres";
      modal.style.display = "block";
      selectCourse.focus();
    }

    suggestForm.onsubmit = function (e) {
      e.preventDefault();
      suggestMsg.textContent = "";
      const idCurso = selectCourse.value;
      const testKey = selectTest.value;
      const testId = window.__PROF_SUGGEST.tests[testKey];
      if (!idCurso || !testId) {
        suggestMsg.textContent = "Selecciona curso y test.";
        return;
      }
      fetch("/unimind/api/suggest_test.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_curso: idCurso, id_test: testId }),
      })
        .then((r) => r.json())
        .then((res) => {
          if (res.success) {
            suggestMsg.textContent =
              "Test sugerido correctamente. Los alumnos recibirán la notificación.";
            setTimeout(() => {
              modal.style.display = "none";
              suggestMsg.textContent = "";
            }, 1200);
          } else {
            suggestMsg.textContent = res.message || "Error al sugerir test.";
          }
        })
        .catch(() => {
          suggestMsg.textContent = "Error de red o servidor.";
        });
    };
  });
})();
