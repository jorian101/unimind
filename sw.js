// Service Worker colocado en /unimind/sw.js para que su scope cubra /unimind/
// Versión 1.0.0
const CACHE_NAME = "unimind-v1.0.0";
const RUNTIME_CACHE = "unimind-runtime-v1";

const STATIC_ASSETS = [
  "/public/css/style.css",
  "/public/css/theme.css",
  "/public/js/main-simple.js",
  "/public/js/dashboard.js",
  "/public/js/header.js",
  "/public/manifest.webmanifest",
  "/public/icons/icon.php?size=192",
  "/public/icons/icon.php?size=512",
  "https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css",
];

const CACHEABLE_ROUTES = [
  "/unimind/views/",
  "/public/",
  "/unimind/controllers/",
];

const NO_CACHE_ROUTES = [
  "/unimind/controllers/AuthController.php",
  "/unimind/controllers/AplicacionesController.php",
  "/unimind/controllers/TestsController.php",
  "/unimind/controllers/UserController.php",
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
    const cached = await caches.match(request);
    if (cached) return cached;
    const resp = await fetch(request);
    if (resp && resp.status === 200) {
      const c = await caches.open(CACHE_NAME);
      c.put(request, resp.clone());
    }
    return resp;
  } catch {
    return caches.match("/unimind/views/autenticacion/login.php");
  }
}

async function networkFirst(request) {
  try {
    const res = await fetch(request);
    if (res && res.status === 200) {
      const c = await caches.open(RUNTIME_CACHE);
      c.put(request, res.clone());
    }
    return res;
  } catch {
    const cached = await caches.match(request);
    if (cached) return cached;
    return new Response("<h1>Sin conexión</h1>", {
      headers: { "Content-Type": "text/html" },
    });
  }
}

function isStaticAsset(pathname) {
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
  ];
  return exts.some((ext) => pathname.endsWith(ext));
}

function isDynamicRoute(pathname) {
  return CACHEABLE_ROUTES.some((route) => pathname.includes(route));
}
