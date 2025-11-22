// Service Worker para UniMind PWA
// Versión 1.0.0
const CACHE_NAME = "unimind-v1.0.0";
const RUNTIME_CACHE = "unimind-runtime-v1";

// Archivos críticos que se cachean en la instalación
const STATIC_ASSETS = [
  "/public/css/style.css",
  "/public/css/theme.css",
  "/public/js/main-simple.js",
  "/public/js/dashboard.js",
  "/public/js/header.js",
  "/public/manifest.webmanifest",
  "/public/icons/icon.php?size=192",
  "/public/icons/icon.php?size=512",
  // Fuentes externas
  "https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css",
];

// Rutas dinámicas que se cachean bajo demanda
const CACHEABLE_ROUTES = [
  "/unimind/views/",
  "/unimind/public/",
  "/public/",
  "/unimind/controllers/",
];

// Rutas de API que NO se cachean (siempre online)
const NO_CACHE_ROUTES = [
  "/unimind/controllers/AuthController.php",
  "/unimind/controllers/AplicacionesController.php",
  "/unimind/controllers/TestsController.php",
  "/unimind/controllers/UserController.php",
];

// ========================================
// INSTALACIÓN DEL SERVICE WORKER
// ========================================
self.addEventListener("install", (event) => {
  event.waitUntil(
    (async () => {
      const cache = await caches.open(CACHE_NAME);
      const results = await Promise.allSettled(
        STATIC_ASSETS.map(async (url) => {
          try {
            const resp = await fetch(url, { cache: "no-store" });
            if (!resp || !resp.ok)
              throw new Error(`HTTP ${resp ? resp.status : "no-response"}`);
            await cache.put(url, resp.clone());
            return { url, ok: true };
          } catch (e) {
            return { url, ok: false, error: e.message };
          }
        }),
      );
      const failed = results
        .filter(
          (r) => r.status === "fulfilled" && r.value && r.value.ok === false,
        )
        .map((r) => r.value);
      if (failed.length) {
        /* algunos recursos no se pudieron cachear */
      }
      await self.skipWaiting();
    })(),
  );
});

// ========================================
// ACTIVACIÓN DEL SERVICE WORKER
// ========================================
self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((cacheNames) => {
        // Eliminar cachés antiguos
        return Promise.all(
          cacheNames
            .filter((name) => name !== CACHE_NAME && name !== RUNTIME_CACHE)
            .map((name) => caches.delete(name)),
        );
      })
      .then(() => self.clients.claim()),
  );
});

// ========================================
// INTERCEPTAR PETICIONES (FETCH)
// ========================================
self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignorar peticiones no HTTP/HTTPS
  if (!url.protocol.startsWith("http")) {
    return;
  }

  // No cachear peticiones POST/PUT/DELETE
  if (request.method !== "GET") {
    return;
  }

  // No cachear API endpoints críticos
  if (NO_CACHE_ROUTES.some((route) => url.pathname.includes(route))) {
    event.respondWith(fetch(request));
    return;
  }

  // Estrategia: Cache First para assets estáticos
  if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(request));
    return;
  }

  // Estrategia: Network First para contenido dinámico
  if (isDynamicRoute(url.pathname)) {
    event.respondWith(networkFirst(request));
    return;
  }

  // Estrategia por defecto: Network First
  event.respondWith(networkFirst(request));
});

// ========================================
// ESTRATEGIAS DE CACHE
// ========================================

/**
 * Cache First: Intenta servir desde caché primero
 * Ideal para: CSS, JS, imágenes, fuentes
 */
async function cacheFirst(request) {
  try {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;

    const networkResponse = await fetch(request);

    // Cachear para futuras peticiones
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(CACHE_NAME);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch {
    return caches.match("/unimind/views/autenticacion/login.php"); // Fallback
  }
}

/**
 * Network First: Intenta red primero, fallback a caché
 * Ideal para: HTML, API no críticas, contenido dinámico
 */
async function networkFirst(request) {
  try {
    const networkResponse = await fetch(request);

    // Cachear respuesta exitosa
    if (networkResponse && networkResponse.status === 200) {
      const cache = await caches.open(RUNTIME_CACHE);
      cache.put(request, networkResponse.clone());
    }

    return networkResponse;
  } catch {
    const cachedResponse = await caches.match(request);
    if (cachedResponse) return cachedResponse;

    // Página offline personalizada
    return new Response(
      `<!DOCTYPE html>
      <html lang="es">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sin conexión - UniMind</title>
        <style>
          body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
            padding: 20px;
          }
          .offline-container {
            max-width: 400px;
          }
          h1 { font-size: 2.5rem; margin-bottom: 1rem; }
          p { font-size: 1.1rem; opacity: 0.9; }
          .icon { font-size: 5rem; margin-bottom: 1rem; }
        </style>
      </head>
      <body>
        <div class="offline-container">
          <div class="icon">📡</div>
          <h1>Sin conexión</h1>
          <p>No hay conexión a Internet. Por favor, verifica tu conexión e intenta nuevamente.</p>
          <button onclick="location.reload()" style="margin-top: 2rem; padding: 12px 24px; background: white; color: #667eea; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; font-weight: 600;">
            Reintentar
          </button>
        </div>
      </body>
      </html>`,
      {
        headers: { "Content-Type": "text/html" },
      },
    );
  }
}

// ========================================
// FUNCIONES DE UTILIDAD
// ========================================

/**
 * Verifica si es un archivo estático
 */
function isStaticAsset(pathname) {
  const staticExtensions = [
    ".css",
    ".js",
    ".png",
    ".jpg",
    ".jpeg",
    ".svg",
    ".gif",
    ".woff",
    ".woff2",
    ".ttf",
  ];
  return staticExtensions.some((ext) => pathname.endsWith(ext));
}

/**
 * Verifica si es una ruta dinámica cacheable
 */
function isDynamicRoute(pathname) {
  return CACHEABLE_ROUTES.some((route) => pathname.includes(route));
}

// ========================================
// MENSAJES DEL SERVICE WORKER
// ========================================
self.addEventListener("message", (event) => {
  if (event.data && event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
  }

  if (event.data && event.data.type === "CLEAR_CACHE") {
    caches
      .keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames.map((cacheName) => caches.delete(cacheName)),
        );
      })
      .then(() => {
        event.ports[0].postMessage({ success: true });
      });
  }
});
