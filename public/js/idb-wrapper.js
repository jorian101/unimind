/**
 * idb-wrapper.js
 * Wrapper promisificado para IndexedDB con soporte para definir stores y migraciones.
 * Permite escalar a múltiples páginas sin reescribir código de IndexedDB.
 */
(function (window) {
  "use strict";

  const DB = {
    name: "unimind-sync",
    version: 3, // incrementar cuando cambies stores/índices
    stores: {}, // { storeName: { options, indexes: [{name, keyPath, options}] } }
    db: null,
  };

  /**
   * Define un store (debe llamarse antes de open)
   * @param {string} name - Nombre del store
   * @param {object} config - { options: {keyPath, autoIncrement}, indexes: [{name, keyPath, options}] }
   */
  function defineStore(name, config = {}) {
    DB.stores[name] = config;
  }

  /**
   * Abre la base de datos (con upgrade si es necesario)
   * @returns {Promise<IDBDatabase>}
   */
  function open() {
    if (DB.db) return Promise.resolve(DB.db);
    return new Promise((resolve, reject) => {
      const req = indexedDB.open(DB.name, DB.version);
      req.onupgradeneeded = (e) => {
        const db = e.target.result;

        // Crear stores declarados
        Object.keys(DB.stores).forEach((storeName) => {
          if (!db.objectStoreNames.contains(storeName)) {
            const config = DB.stores[storeName];
            const opts = config.options || {
              keyPath: "id",
              autoIncrement: true,
            };
            const os = db.createObjectStore(storeName, opts);

            // Crear índices
            const idxs = config.indexes || [];
            idxs.forEach((ix) => {
              try {
                os.createIndex(ix.name, ix.keyPath, ix.options || {});
              } catch (err) {
                console.warn(
                  `No se pudo crear índice ${ix.name} en ${storeName}:`,
                  err,
                );
              }
            });
          } else {
            // Store ya existe, solo agregar índices faltantes (si necesario)
            const tx = e.target.transaction;
            if (tx && tx.objectStoreNames.contains(storeName)) {
              const store = tx.objectStore(storeName);
              const config = DB.stores[storeName];
              const idxs = config.indexes || [];
              idxs.forEach((ix) => {
                if (!store.indexNames.contains(ix.name)) {
                  try {
                    store.createIndex(ix.name, ix.keyPath, ix.options || {});
                  } catch (err) {
                    console.warn(`No se pudo añadir índice ${ix.name}:`, err);
                  }
                }
              });
            }
          }
        });
      };
      req.onsuccess = () => {
        DB.db = req.result;
        resolve(DB.db);
      };
      req.onerror = () => reject(req.error);
    });
  }

  /**
   * Obtiene un object store en una transacción
   * @param {string} storeName
   * @param {string} mode - 'readonly' | 'readwrite'
   * @returns {Promise<IDBObjectStore>}
   */
  function getStore(storeName, mode = "readonly") {
    return open().then((db) => {
      const tx = db.transaction(storeName, mode);
      return tx.objectStore(storeName);
    });
  }

  /**
   * Añade o actualiza un registro (put)
   * @param {string} storeName
   * @param {any} value
   * @returns {Promise<any>} - key del registro
   */
  function add(storeName, value) {
    return new Promise((resolve, reject) => {
      getStore(storeName, "readwrite")
        .then((store) => {
          const r = store.put(value);
          r.onsuccess = () => resolve(r.result);
          r.onerror = () => reject(r.error);
        })
        .catch(reject);
    });
  }

  /**
   * Obtiene un registro por key
   * @param {string} storeName
   * @param {any} key
   * @returns {Promise<any>}
   */
  function get(storeName, key) {
    return new Promise((resolve, reject) => {
      getStore(storeName, "readonly")
        .then((store) => {
          const r = store.get(key);
          r.onsuccess = () => resolve(r.result);
          r.onerror = () => reject(r.error);
        })
        .catch(reject);
    });
  }

  /**
   * Obtiene todos los registros de un store o índice
   * @param {string} storeName
   * @param {string|null} indexName - nombre del índice (opcional)
   * @param {any} query - IDBKeyRange o valor (opcional)
   * @param {number} limit - máximo de registros
   * @returns {Promise<Array>}
   */
  function getAll(storeName, indexName = null, query = null, limit = Infinity) {
    return new Promise((resolve, reject) => {
      open()
        .then((db) => {
          const tx = db.transaction(storeName, "readonly");
          const store = tx.objectStore(storeName);
          const source = indexName ? store.index(indexName) : store;
          const out = [];
          const req = source.openCursor(query);
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
        })
        .catch(reject);
    });
  }

  /**
   * Elimina un registro por key
   * @param {string} storeName
   * @param {any} key
   * @returns {Promise<boolean>}
   */
  function del(storeName, key) {
    return new Promise((resolve, reject) => {
      getStore(storeName, "readwrite")
        .then((store) => {
          const r = store.delete(key);
          r.onsuccess = () => resolve(true);
          r.onerror = () => reject(r.error);
        })
        .catch(reject);
    });
  }

  /**
   * Actualiza un registro (get + put)
   * @param {string} storeName
   * @param {any} key
   * @param {object} updates - objeto con campos a actualizar
   * @returns {Promise<any>}
   */
  function update(storeName, key, updates) {
    return new Promise((resolve, reject) => {
      open()
        .then((db) => {
          const tx = db.transaction(storeName, "readwrite");
          const store = tx.objectStore(storeName);
          const getReq = store.get(key);
          getReq.onsuccess = () => {
            const rec = getReq.result;
            if (!rec) return resolve(null);
            Object.assign(rec, updates);
            const putReq = store.put(rec);
            putReq.onsuccess = () => resolve(rec);
            putReq.onerror = () => reject(putReq.error);
          };
          getReq.onerror = () => reject(getReq.error);
        })
        .catch(reject);
    });
  }

  /**
   * Configura nombre de la base
   * @param {string} name
   */
  function setDBName(name) {
    DB.name = name;
  }

  /**
   * Configura versión de la base
   * @param {number} version
   */
  function setVersion(version) {
    DB.version = version;
  }

  /**
   * Cierra la conexión (útil para testing)
   */
  function close() {
    if (DB.db) {
      DB.db.close();
      DB.db = null;
    }
  }

  // Exponer API pública
  window.IDBWrapper = {
    defineStore,
    open,
    add,
    get,
    getAll,
    del,
    update,
    setDBName,
    setVersion,
    close,
  };
})(window);
