/* pwa-sync.js
   IndexedDB queue + sync to server with retries (refactorizado con IDBWrapper)
*/
(function (window) {
  "use strict";

  const STORE_NAME = "applications";
  const STORE_TESTS = "tests";
  const META_STORE = "meta";

  // Inicializar stores con IDBWrapper
  if (window.IDBWrapper) {
    window.IDBWrapper.setDBName("unimind-sync");
    window.IDBWrapper.setVersion(3);

    // Definir stores antes de abrir
    window.IDBWrapper.defineStore(META_STORE, {
      options: { keyPath: "key" },
      indexes: [],
    });

    window.IDBWrapper.defineStore(STORE_NAME, {
      options: { keyPath: "client_uuid" },
      indexes: [
        { name: "status", keyPath: "status" },
        { name: "created_at", keyPath: "created_at" },
      ],
    });

    window.IDBWrapper.defineStore(STORE_TESTS, {
      options: { keyPath: "client_uuid" },
      indexes: [
        { name: "status", keyPath: "status" },
        { name: "created_at", keyPath: "created_at" },
      ],
    });
  }

  // Meta helpers
  async function getMeta(key) {
    try {
      const record = await window.IDBWrapper.get(META_STORE, key);
      return record ? record.value : null;
    } catch (e) {
      console.error("Error getting meta:", e);
      return null;
    }
  }

  async function setMeta(key, value) {
    try {
      await window.IDBWrapper.add(META_STORE, { key, value });
      return true;
    } catch (e) {
      console.error("Error setting meta:", e);
      return false;
    }
  }

  // Applications (estudiante tests)
  async function addApplication(item) {
    const now = new Date().toISOString();
    const record = Object.assign(
      { status: "queued", attempts: 0, created_at: now },
      item,
    );
    return window.IDBWrapper.add(STORE_NAME, record);
  }

  async function getQueuedItems(limit = 100) {
    return window.IDBWrapper.getAll(STORE_NAME, "status", "queued", limit);
  }

  async function markItem(client_uuid, status, extra = {}) {
    return window.IDBWrapper.update(STORE_NAME, client_uuid, {
      status,
      ...extra,
    });
  }

  // Tests (admin-created tests)
  async function addTest(item) {
    const now = new Date().toISOString();
    // Ensure client_uuid exists
    if (!item.client_uuid) {
      item.client_uuid = crypto.randomUUID
        ? crypto.randomUUID()
        : String(Date.now()) + Math.random();
    }
    const record = Object.assign(
      { status: "queued", attempts: 0, created_at: now },
      item,
    );
    return window.IDBWrapper.add(STORE_TESTS, record);
  }

  async function getQueuedTests(limit = 100) {
    return window.IDBWrapper.getAll(STORE_TESTS, "status", "queued", limit);
  }

  async function markTest(client_uuid, status, extra = {}) {
    return window.IDBWrapper.update(STORE_TESTS, client_uuid, {
      status,
      ...extra,
    });
  }

  async function retryWithBackoff(fn, attempts = 5) {
    let attempt = 0;
    while (attempt < attempts) {
      try {
        return await fn();
      } catch (e) {
        attempt++;
        if (attempt >= attempts) throw e;
        const wait = Math.pow(2, attempt) * 300; // 600ms, 1200ms, ...
        await new Promise((r) => setTimeout(r, wait));
      }
    }
  }

  async function flushQueue() {
    const queued = await getQueuedItems(100);
    if (!queued.length) return { sent: 0 };

    const payload = {
      items: queued.map((it) => ({
        client_uuid: it.client_uuid,
        id_test: it.id_test,
        respuestas: it.respuestas,
      })),
    };

    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;
      const sendFn = () =>
        fetch(`${baseUrl}/controllers/SyncController.php`, {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(payload),
        }).then((r) => {
          if (!r.ok) {
            const err = new Error("HTTP " + r.status);
            err.status = r.status;
            throw err;
          }
          return r.json();
        });

      const res = await retryWithBackoff(sendFn, 5);
      if (res && res.mappings) {
        for (const m of res.mappings) {
          if (m.status === "created" || m.status === "exists") {
            await markItem(m.client_uuid, "synced", {
              server_id: m.server_id,
              synced_at: new Date().toISOString(),
            });
          } else {
            await markItem(m.client_uuid, "error", { error: m.error });
          }
        }
      }
      return { sent: queued.length, result: res };
    } catch (e) {
      // mark attempts
      for (const it of queued) {
        try {
          await markItem(it.client_uuid, "queued", {
            attempts: (it.attempts || 0) + 1,
          });
        } catch {}
      }
      throw e;
    }
  }

  async function flushTests() {
    const queued = await getQueuedTests(50);
    if (!queued.length) return { sent: 0 };

    try {
      const base = window.UNIMIND_BASE || "";
      const baseUrl =
        window.location.origin && window.location.origin !== "null"
          ? window.location.origin + base
          : base;

      // Send each test to TestsController. Support create (JSON) and update (FormData) depending on record
      for (const t of queued) {
        try {
          // If the queued record indicates an update (has id_test or explicit action), send as FormData with action=update
          if (t.id_test || t.action === "update") {
            const form = new FormData();
            form.append("action", "update");
            form.append("id_test", t.id_test || "");
            form.append("nombre", t.nombre || "");
            form.append("descripcion", t.descripcion || "");
            form.append("num_items", t.num_items || 0);
            form.append("items", JSON.stringify(t.items || []));

            const sendFn = () =>
              fetch(`${baseUrl}/controllers/TestsController.php`, {
                method: "POST",
                credentials: "include",
                body: form,
              }).then((r) => {
                if (!r.ok) {
                  const err = new Error("HTTP " + r.status);
                  err.status = r.status;
                  throw err;
                }
                return r.json();
              });

            const res = await retryWithBackoff(sendFn, 4);
            if (res && res.success) {
              await markTest(t.client_uuid, "synced", {
                server_id: res.data?.id_test || null,
                synced_at: new Date().toISOString(),
              });
            } else {
              await markTest(t.client_uuid, "error", {
                error: res.message || "unknown",
              });
            }
          } else {
            // Create new test via JSON raw (createTest supports JSON input)
            const payload = {
              nombre: t.nombre,
              descripcion: t.descripcion,
              num_items: t.num_items,
              items: t.items,
            };

            const sendFn = () =>
              fetch(`${baseUrl}/controllers/TestsController.php`, {
                method: "POST",
                credentials: "include",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(payload),
              }).then((r) => {
                if (!r.ok) {
                  const err = new Error("HTTP " + r.status);
                  err.status = r.status;
                  throw err;
                }
                return r.json();
              });

            const res = await retryWithBackoff(sendFn, 4);
            if (res && res.success) {
              await markTest(t.client_uuid, "synced", {
                server_id: res.data?.id_test || null,
                synced_at: new Date().toISOString(),
              });
            } else {
              await markTest(t.client_uuid, "error", {
                error: res.message || "unknown",
              });
            }
          }
        } catch {
          await markTest(t.client_uuid, "queued", {
            attempts: (t.attempts || 0) + 1,
          });
        }
      }

      return { sent: queued.length };
    } catch (e) {
      throw e;
    }
  }

  // Expose API
  window.UnimindSync = {
    addApplication,
    flushQueue,
    getQueuedItems,
    addTest,
    flushTests,
    getQueuedTests,
    markTest,
    getMeta,
    setMeta,
  };

  // Auto-register message to trigger flush from SW or UI
  navigator.serviceWorker?.addEventListener("message", (ev) => {
    if (ev.data && ev.data.type === "SYNC_NOW") {
      // Flush both queues (applications and tests) when requested by SW/UI
      flushQueue().catch(() => {});
      flushTests().catch(() => {});
    }
  });

  // Auto-sync cuando se reconecta a internet
  window.addEventListener("online", () => {
    // Producción: eliminar logs
    Promise.all([
      flushQueue().catch((e) =>
        console.warn("[PWA-Sync] Error al sincronizar aplicaciones:", e),
      ),
      flushTests().catch((e) =>
        console.warn("[PWA-Sync] Error al sincronizar tests:", e),
      ),
    ]).then(() => {
      // Producción: eliminar logs
    });
  });

  // Inicializar IDBWrapper al cargar
  if (window.IDBWrapper) {
    window.IDBWrapper.open()
      .then(() => {
        // Si ya estamos online al cargar la página (por ejemplo recargaste
        // después de recuperar la conexión), intentar flush inmediato.
        if (navigator.onLine) {
          flushQueue().catch((e) =>
            console.warn("[PWA-Sync] Error al sincronizar aplicaciones:", e),
          );
          flushTests().catch((e) =>
            console.warn("[PWA-Sync] Error al sincronizar tests:", e),
          );
        }
      })
      .catch((e) => {
        console.error("[PWA-Sync] Error al abrir IndexedDB:", e);
      });
  }
})(window);
