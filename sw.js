// Service Worker colocado en /sw.js para que su scope cubra toda la raíz /
// Versión 1.0.5 - Fixed base path detection (changed from /unimind to /)
const CACHE_NAME = "unimind-v1.0.5";
const RUNTIME_CACHE = "unimind-runtime-v1.0.5";

// Assets críticos con rutas relativas (resuelven bajo la raíz /)
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
    (async () => {
      // Limpiar cachés antiguas
      const names = await caches.keys();
      await Promise.all(
        names
          .filter((n) => n !== CACHE_NAME && n !== RUNTIME_CACHE)
          .map((n) => caches.delete(n)),
      );

      // Tomar control inmediato de todos los clientes
      await self.clients.claim();

      // Notificar a todos los clientes que hay nueva versión activa
      // Los clientes pueden decidir recargar sin borrar site data
      try {
        const allClients = await self.clients.matchAll({
          type: "window",
          includeUncontrolled: true,
        });
        allClients.forEach((client) => {
          client.postMessage({
            type: "NEW_VERSION",
            version: CACHE_NAME,
          });
        });
      } catch {
        // ignore notification errors
      }
    })(),
  );
});

self.addEventListener("fetch", (event) => {
  let { request } = event;
  // Detect development host (localhost) to relax caching for fast iteration.
  // Using URL(self.location.href).hostname works inside SW.
  const IS_DEV = (() => {
    try {
      return new URL(self.location.href).hostname === "localhost";
    } catch {
      return false;
    }
  })();
  try {
    const url = new URL(request.url);
    // Only handle http(s) GET requests
    if (!url.protocol.startsWith("http") || request.method !== "GET") return;

    // If a request appears to be missing the application base (e.g. "/controllers/...")
    // attempt to rewrite it by prepending the service worker scope path (e.g. "/unimind").
    // This helps when the HTML was served from cache/offline and didn't run PHP to inject the base.
    const scopePath = new URL(self.registration.scope).pathname.replace(
      /\/$/,
      "",
    );
    if (scopePath && url.pathname.startsWith("/controllers/")) {
      const corrected = `${scopePath}${url.pathname}${url.search}`;
      const newUrl = new URL(corrected, url.origin).toString();
      request = new Request(newUrl, {
        method: "GET",
        headers: request.headers,
        mode: request.mode,
        credentials: request.credentials,
        redirect: request.redirect,
        cache: request.cache,
        referrer: request.referrer,
      });
    }

    const requestUrl = new URL(request.url);

    // Development override: prefer network for JS/CSS so changes are visible
    // immediately without needing to bump CACHE_NAME. This only applies on
    // localhost and only for .js/.css files.
    if (IS_DEV) {
      const pathname = requestUrl.pathname.split("?")[0];
      if (pathname.match(/\.(js|css)$/i)) {
        event.respondWith(networkFirst(request));
        return;
      }
    }

    // No-cache routes (API endpoints) should use networkFirst with fallback instead of direct fetch
    // This prevents "Failed to fetch" errors when offline
    if (NO_CACHE_ROUTES.some((route) => requestUrl.pathname.includes(route))) {
      event.respondWith(networkFirst(request));
      return;
    }

    // Static assets (cache-first)
    if (isStaticAsset(requestUrl.pathname)) {
      event.respondWith(cacheFirst(request));
      return;
    }

    // Dynamic routes - prefer network then cache
    if (isDynamicRoute(requestUrl.pathname)) {
      event.respondWith(networkFirst(request));
      return;
    }

    event.respondWith(networkFirst(request));
  } catch {
    // In case of any error parsing URL or building request, fallback to network-first
    event.respondWith(networkFirst(request));
  }
});

// Listen messages from clients to trigger sync via SW (optional)
self.addEventListener("message", (event) => {
  if (!event.data) return;

  // Permitir que las páginas fuercen activación inmediata del nuevo SW
  if (event.data.type === "SKIP_WAITING") {
    self.skipWaiting();
    return;
  }

  if (event.data.type === "SYNC_BATCH") {
    const payload = event.data.payload;
    (async () => {
      try {
        const resp = await fetch("./controllers/SyncController.php", {
          method: "POST",
          credentials: "include",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ items: payload }),
        });
        const json = await resp.json();
        const allClients = await self.clients.matchAll({
          includeUncontrolled: true,
        });
        allClients.forEach((c) =>
          c.postMessage({ type: "SYNC_RESULT", result: json }),
        );
      } catch (err) {
        const allClients = await self.clients.matchAll({
          includeUncontrolled: true,
        });
        allClients.forEach((c) =>
          c.postMessage({ type: "SYNC_ERROR", error: String(err) }),
        );
      }
    })();
  }
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

    // Intentar devolver un fallback adecuado según el tipo de recurso
    try {
      const url = new URL(request.url);
      const accept =
        request.headers && request.headers.get
          ? request.headers.get("accept") || ""
          : "";

      // API / controllers => devolver JSON de fallback
      if (
        url.pathname.includes("/controllers/") ||
        accept.includes("application/json")
      ) {
        const payload = JSON.stringify({
          success: false,
          offline: true,
          message: "Sin conexión",
        });
        return new Response(payload, {
          status: 503,
          headers: { "Content-Type": "application/json" },
        });
      }

      // Imágenes => devolver SVG placeholder (válido para <img>)
      if (
        request.destination === "image" ||
        url.pathname.match(/\.(png|jpg|jpeg|gif|svg)$/i)
      ) {
        const svg =
          `<?xml version="1.0" encoding="utf-8"?>\n` +
          `<svg xmlns="http://www.w3.org/2000/svg" width="192" height="192">` +
          `<rect width="100%" height="100%" fill="#f2f2f2"/>` +
          `<text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="20" fill="#999">Offline</text>` +
          `</svg>`;
        return new Response(svg, {
          headers: { "Content-Type": "image/svg+xml" },
          status: 503,
        });
      }

      // Documentos => devolver página offline cacheada si existe
      if (request.destination === "document") {
        const offlineURL = new URL(
          "offline.html",
          self.registration.scope,
        ).toString();
        const offlineCached = await caches.match(offlineURL);
        if (offlineCached) return offlineCached;
      }
    } catch {
      // ignore and fallthrough
    }

    // Fallback genérico de texto
    return new Response("Sin conexión", {
      status: 503,
      headers: { "Content-Type": "text/plain" },
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
