/* pwa-sync.js
   Minimal IndexedDB queue + sync to server with retries and credentials: 'include'
*/
(function (window) {
  "use strict";

  const DB_NAME = "unimind-sync";
  const DB_VERSION = 2; // bump when changing schema
  const STORE_NAME = "applications";

  const META_STORE = "meta";

  function openDB() {
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DB_NAME, DB_VERSION);
      req.onupgradeneeded = (e) => {
        const db = e.target.result;
        const oldVersion = e.oldVersion;
        // Meta store to keep schema version and other metadata
        if (!db.objectStoreNames.contains(META_STORE)) {
          db.createObjectStore(META_STORE, { keyPath: "key" });
        }

        // Create main store and indexes if missing
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          const os = db.createObjectStore(STORE_NAME, {
            keyPath: "client_uuid",
          });
          os.createIndex("status", "status");
        }

        // Migrate to v2: ensure created_at index exists
        if (oldVersion < 2) {
          try {
            const tx = e.target.transaction;
            if (
              tx &&
              tx.objectStoreNames &&
              tx.objectStoreNames.contains(STORE_NAME)
            ) {
              const store = tx.objectStore(STORE_NAME);
              if (!store.indexNames.contains("created_at")) {
                store.createIndex("created_at", "created_at");
              }
            }
          } catch {}
        }
      };
      req.onsuccess = () => resolve(req.result);
      req.onerror = () => reject(req.error);
    });
  }

  // Meta helpers
  async function getMeta(key) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(META_STORE, "readonly");
      const store = tx.objectStore(META_STORE);
      const r = store.get(key);
      r.onsuccess = () => resolve(r.result ? r.result.value : null);
      r.onerror = () => reject(r.error);
    });
  }

  async function setMeta(key, value) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(META_STORE, "readwrite");
      const store = tx.objectStore(META_STORE);
      store.put({ key: key, value: value });
      tx.oncomplete = () => resolve(true);
      tx.onerror = () => reject(tx.error);
    });
  }

  async function addApplication(item) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, "readwrite");
      const store = tx.objectStore(STORE_NAME);
      const now = new Date().toISOString();
      const record = Object.assign(
        { status: "queued", attempts: 0, created_at: now },
        item,
      );
      store.put(record);
      tx.oncomplete = () => resolve(record);
      tx.onerror = () => reject(tx.error);
    });
  }

  async function getQueuedItems(limit = 100) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, "readonly");
      const store = tx.objectStore(STORE_NAME);
      const idx = store.index("status");
      const req = idx.openCursor("queued");
      const out = [];
      req.onsuccess = (e) => {
        const cur = e.target.result;
        if (cur && out.length < limit) {
          out.push(cur.value);
          cur.continue();
        } else {
          resolve(out);
        }
      };
      req.onerror = () => reject(req.error);
    });
  }

  async function markItem(client_uuid, status, extra = {}) {
    const db = await openDB();
    return new Promise((resolve, reject) => {
      const tx = db.transaction(STORE_NAME, "readwrite");
      const store = tx.objectStore(STORE_NAME);
      const getReq = store.get(client_uuid);
      getReq.onsuccess = () => {
        const rec = getReq.result;
        if (!rec) return resolve(null);
        Object.assign(rec, { status: status }, extra);
        store.put(rec);
      };
      tx.oncomplete = () => resolve(true);
      tx.onerror = () => reject(tx.error);
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

  // Expose API
  window.UnimindSync = {
    addApplication,
    flushQueue,
    getQueuedItems,
    getMeta,
    setMeta,
  };

  // Auto-register message to trigger flush from SW or UI
  navigator.serviceWorker?.addEventListener("message", (ev) => {
    if (ev.data && ev.data.type === "SYNC_NOW") {
      flushQueue().catch(() => {});
    }
  });
})(window);
