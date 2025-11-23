// Service Worker colocado en /unimind/sw.js para que su scope cubra /unimind/
// Versión 1.0.1 - Optimizado para rendimiento y caché offline
const CACHE_NAME = "unimind-v1.0.1";
const RUNTIME_CACHE = "unimind-runtime-v1";

// Assets críticos con rutas relativas (resuelven bajo /unimind/)
const STATIC_ASSETS = [
  "public/css/style.css",
  "public/css/theme.css",
  "public/js/main-simple.js",
  "public/js/dashboard.js",
  "public/js/header.js",
  "public/manifest.webmanifest",
  // CSS críticos de views
  "views/layout.css",
  "views/sidebar.css",
  "views/header.css",
  "views/pageHeader.css",
  "views/estudiante/inicio.css",
  "views/estudiante/dashboard.css",
  "views/estudiante/tests.css",
  "views/administrador/tests.css",
  // Iconos (sin query string, se cachean por ruta base)
  "public/icons/icon-192.png",
  "public/icons/icon-512.png",
  // Página offline mínima
  "offline.html",
];

const CACHEABLE_ROUTES = ["views/", "public/", "controllers/"];

const NO_CACHE_ROUTES = [
  "controllers/AuthController.php",
  "controllers/AplicacionesController.php",
  "controllers/TestsController.php",
  "controllers/UserController.php",
  "controllers/Logout.php",
];

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
        /* algunos recursos no cacheados */
      }
      await self.skipWaiting();
    })(),
  );
});

self.addEventListener("activate", (event) => {
  event.waitUntil(
    caches
      .keys()
      .then((names) =>
        Promise.all(
          names
            .filter((n) => n !== CACHE_NAME && n !== RUNTIME_CACHE)
            .map((n) => caches.delete(n)),
        ),
      )
      .then(() => self.clients.claim()),
  );
});

self.addEventListener("fetch", (event) => {
  const { request } = event;
  const url = new URL(request.url);
  if (!url.protocol.startsWith("http") || request.method !== "GET") return;
  if (NO_CACHE_ROUTES.some((route) => url.pathname.includes(route))) {
    event.respondWith(fetch(request));
    return;
  }
  if (isStaticAsset(url.pathname)) {
    event.respondWith(cacheFirst(request));
    return;
  }
  if (isDynamicRoute(url.pathname)) {
    event.respondWith(networkFirst(request));
    return;
  }
  event.respondWith(networkFirst(request));
});

async function cacheFirst(request) {
  try {
    // Buscar en caché (con y sin query string para flexibilidad)
    let cached = await caches.match(request);
    if (!cached) {
      // Intentar sin query string para assets versionados
      const url = new URL(request.url);
      url.search = "";
      cached = await caches.match(url.toString());
    }
    if (cached) return cached;

    const resp = await fetch(request);
    if (resp && resp.status === 200) {
      const c = await caches.open(CACHE_NAME);
      // Cachear sin query string para reutilizar mejor
      const cacheUrl = new URL(request.url);
      if (cacheUrl.search.includes("v=")) {
        cacheUrl.search = "";
        c.put(cacheUrl.toString(), resp.clone());
      } else {
        c.put(request, resp.clone());
      }
    }
    return resp;
  } catch {
    // Fallback genérico: intentar página offline si está disponible
    try {
      const offlineURL = new URL(
        "offline.html",
        self.registration.scope,
      ).toString();
      const offline = await caches.match(offlineURL);
      if (offline) return offline;
    } catch {}
    return null;
  }
}

async function networkWithTimeout(request, timeout = 3000) {
  const controller = new AbortController();
  const signal = controller.signal;
  const timer = setTimeout(() => controller.abort(), timeout);
  try {
    const response = await fetch(request, { signal });
    clearTimeout(timer);
    return response;
  } catch (err) {
    clearTimeout(timer);
    throw err;
  }
}

async function networkFirst(request) {
  try {
    const res = await networkWithTimeout(request, 3000);
    if (res && res.status === 200) {
      const c = await caches.open(RUNTIME_CACHE);
      c.put(request, res.clone());
    }
    return res;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    // Si es una petición de documento, intentar devolver offline.html cacheada
    try {
      if (request.destination === "document") {
        const offlineURL = new URL(
          "offline.html",
          self.registration.scope,
        ).toString();
        const offlineCached = await caches.match(offlineURL);
        if (offlineCached) return offlineCached;
      }
    } catch {}
    return new Response("<h1>Sin conexión</h1>", {
      headers: { "Content-Type": "text/html" },
    });
  }
}

function isStaticAsset(pathname) {
  // Remover query string para detectar extensión correctamente
  const pathWithoutQuery = pathname.split("?")[0];
  const exts = [
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
    ".eot",
    ".ico",
    ".webp",
  ];
  return exts.some((ext) => pathWithoutQuery.endsWith(ext));
}

function isDynamicRoute(pathname) {
  return CACHEABLE_ROUTES.some((route) => pathname.includes(route));
}
