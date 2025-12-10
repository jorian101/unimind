# 🔄 Sistema de Auto-Actualización PWA

## 📋 Descripción General

Sistema implementado para que **cada cambio de código se refleje automáticamente** al recargar la página, tanto en **desarrollo local (localhost)** como en **producción**, **sin necesidad de usar "Clear Site Data"** y **manteniendo la sesión activa**.

---

## ✅ Características Implementadas

### 1️⃣ Service Worker (`sw.js`)

**✅ Versionado automático de caché:**

```javascript
const CACHE_NAME = "unimind-v1.0.5";
const RUNTIME_CACHE = "unimind-runtime-v1.0.5";
```

**✅ Activación inmediata con `skipWaiting()`:**

- Ya presente en el evento `install`
- Ahora también responde a mensajes `SKIP_WAITING` desde clientes

**✅ Control inmediato con `clients.claim()`:**

- El nuevo SW toma control de todas las páginas inmediatamente

**✅ Notificación a clientes:**

- Cuando el SW nuevo se activa, envía mensaje `NEW_VERSION` a todos los clientes
- Incluye la versión del caché para tracking

**✅ Handler de mensajes `SKIP_WAITING`:**

```javascript
if (event.data.type === "SKIP_WAITING") {
  self.skipWaiting();
  return;
}
```

---

### 2️⃣ Frontend (`main-simple.js`)

**✅ Registro automático del Service Worker:**

- Se ejecuta al cargar la página (`window.addEventListener('load')`)

**✅ Comprobación forzada de actualizaciones:**

```javascript
await registration.update();
```

**✅ Activación automática del SW nuevo:**

- Si hay un SW en estado `waiting`, se envía `SKIP_WAITING` automáticamente
- Se detectan nuevas versiones con el evento `updatefound`

**✅ Recarga automática cuando el nuevo SW toma control:**

```javascript
navigator.serviceWorker.addEventListener("controllerchange", () => {
  if (refreshing) return;
  refreshing = true;
  window.location.reload();
});
```

**✅ Logging en consola:**

- Todos los eventos importantes se registran para debugging

---

## 🧪 Instrucciones de Prueba

### **Prueba 1: Cambiar versión de caché**

1. **Editar `sw.js`:**

   ```javascript
   // Cambiar de:
   const CACHE_NAME = "unimind-v1.0.5";

   // A:
   const CACHE_NAME = "unimind-v1.0.6";
   ```

2. **Abrir/recargar la aplicación:**

   ```
   http://localhost/unimind
   ```

3. **Observar en consola del navegador:**

   ```
   [SW] Service Worker registrado exitosamente
   [SW] Nueva versión detectada, instalando...
   [SW] Nueva versión instalada, activando automáticamente...
   [SW] Nueva versión activa: unimind-v1.0.6
   [SW] Nuevo Service Worker en control, recargando página...
   ```

4. **Verificar:**
   - ✅ La página se recarga automáticamente
   - ✅ La sesión sigue activa (usuario logueado)
   - ✅ Los componentes cargan correctamente
   - ✅ En DevTools → Application → Cache Storage: solo existe `unimind-v1.0.6`

---

### **Prueba 2: Cambiar un archivo CSS**

1. **Modificar cualquier CSS (ejemplo: `views/estudiante/dashboard.css`):**

   ```css
   /* Agregar o cambiar algún estilo */
   .dashboard-container {
     background: #f0f0f0; /* Cambio visible */
   }
   ```

2. **Incrementar versión en `sw.js`:**

   ```javascript
   const CACHE_NAME = "unimind-v1.0.7";
   ```

3. **Recargar la aplicación**

4. **Verificar:**
   - ✅ El nuevo estilo se aplica inmediatamente
   - ✅ No fue necesario borrar datos
   - ✅ La sesión permanece activa

---

### **Prueba 3: Cambiar código JavaScript**

1. **Modificar `public/js/dashboard.js` o cualquier JS:**

   ```javascript
   // Agregar un console.log para verificar
   console.log("Dashboard v2.0 cargado");
   ```

2. **Incrementar versión en `sw.js`:**

   ```javascript
   const CACHE_NAME = "unimind-v1.0.8";
   ```

3. **Recargar la aplicación**

4. **Verificar en consola:**
   - ✅ Aparece el mensaje `Dashboard v2.0 cargado`
   - ✅ Los eventos del SW confirman la actualización
   - ✅ La sesión sigue activa

---

### **Prueba 4: Verificar que la sesión NO se pierde**

1. **Iniciar sesión en la aplicación**

2. **Verificar que hay datos en:**
   - DevTools → Application → Cookies: `PHPSESSID` o cookie de sesión
   - DevTools → Application → Local Storage: datos de usuario
   - DevTools → Application → IndexedDB: `unimind-sync` con datos

3. **Cambiar versión del SW:**

   ```javascript
   const CACHE_NAME = "unimind-v1.0.9";
   ```

4. **Recargar la página**

5. **Verificar:**
   - ✅ La página se recarga automáticamente
   - ✅ El usuario SIGUE LOGUEADO
   - ✅ Las cookies NO se borraron
   - ✅ localStorage NO se borró
   - ✅ IndexedDB NO se borró
   - ✅ Los componentes que dependen de sesión cargan correctamente

---

## 🚀 Uso en Desarrollo (Localhost)

### **Configuración recomendada en Chrome DevTools:**

1. **Abrir DevTools (F12)**

2. **Ir a: Application → Service Workers**

3. **Marcar estas opciones (SOLO EN DESARROLLO):**
   - ✅ `Update on reload` - Fuerza actualización del SW en cada recarga
   - ✅ `Bypass for network` - Opcional, para debugging

4. **Trabajar normalmente:**
   - Cada vez que cambies código y recargues, el SW se actualizará automáticamente
   - La página se recargará una vez cuando el nuevo SW tome control
   - **NO necesitas usar "Clear Site Data" nunca más**

---

## 🌐 Uso en Producción

### **Estrategia de versionado:**

**Opción 1: Versionado manual**

- Incrementar manualmente `CACHE_NAME` en cada deploy:
  ```javascript
  const CACHE_NAME = "unimind-v1.1.0";
  ```

**Opción 2: Versionado automático (recomendado)**

- Usar un script de deploy que inyecte automáticamente la versión:
  ```bash
  # En deploy.sh
  VERSION=$(date +%Y%m%d%H%M%S)
  sed -i "s/const CACHE_NAME = .*/const CACHE_NAME = \"unimind-v${VERSION}\";/" sw.js
  ```

**Opción 3: Hash de commit (Git)**

- Usar el hash del commit como versión:
  ```bash
  VERSION=$(git rev-parse --short HEAD)
  sed -i "s/const CACHE_NAME = .*/const CACHE_NAME = \"unimind-v${VERSION}\";/" sw.js
  ```

---

## 📊 Comportamiento Esperado

### **Primera visita:**

```
1. Se registra el Service Worker
2. Se cachean los assets críticos
3. La aplicación funciona normalmente
```

### **Visitas subsecuentes (sin cambios):**

```
1. SW comprueba si hay actualización
2. No hay cambios, usa caché
3. Aplicación carga rápidamente desde caché
```

### **Visitas subsecuentes (con cambios):**

```
1. SW detecta nuevo sw.js
2. Se instala el nuevo SW en background
3. Se envía mensaje SKIP_WAITING
4. Nuevo SW se activa inmediatamente
5. Nuevo SW toma control (clients.claim)
6. Nuevo SW envía mensaje NEW_VERSION
7. Página detecta controllerchange
8. Página se recarga automáticamente
9. Nueva versión en uso, sesión intacta ✅
```

---

## 🔍 Debugging

### **Consola del navegador:**

Los mensajes clave a buscar:

```
[SW] Service Worker registrado exitosamente
[SW] Hay una nueva versión esperando, activando...
[SW] Nueva versión detectada, instalando...
[SW] Nueva versión instalada, activando automáticamente...
[SW] Nueva versión activa: unimind-vX.X.X
[SW] Nuevo Service Worker en control, recargando página...
```

### **Chrome DevTools → Application:**

1. **Service Workers:**
   - Ver estado del SW actual
   - Ver si hay SW en estado "waiting"
   - Opción manual "skipWaiting" (no necesaria con nuestro sistema)

2. **Cache Storage:**
   - Ver qué versiones de caché existen
   - Verificar que solo existe la versión actual
   - Ver qué archivos están cacheados

3. **Storage:**
   - **Cookies:** Deben permanecer después de actualizar
   - **Local Storage:** Debe permanecer después de actualizar
   - **IndexedDB:** Debe permanecer después de actualizar

---

## ⚠️ Consideraciones Importantes

### **✅ LO QUE SÍ FUNCIONA:**

- ✅ Actualización automática del Service Worker
- ✅ Recarga automática cuando nueva versión está lista
- ✅ Preservación de sesión (cookies)
- ✅ Preservación de datos locales (localStorage)
- ✅ Preservación de datos IndexedDB
- ✅ Funciona en localhost
- ✅ Funciona en producción
- ✅ No requiere intervención manual

### **❌ LO QUE NO SE DEBE HACER:**

- ❌ Usar "Clear Site Data" (borra sesión y datos)
- ❌ Desregistrar el Service Worker manualmente
- ❌ Borrar manualmente el cache storage
- ❌ Borrar localStorage/cookies manualmente

### **⚡ OPTIMIZACIONES OPCIONALES:**

**Para desarrollo:**

- Marcar `Update on reload` en DevTools
- Considerar usar `cache: 'reload'` en fetch durante desarrollo

**Para producción:**

- Mostrar un toast/notificación al usuario en lugar de recargar automáticamente
- Dar opción al usuario de "Actualizar ahora" o "Más tarde"
- Implementar cache-busting con hash en nombres de archivos (webpack/vite)

---

## 🎯 Resultado Final

### **Desarrollo (Localhost):**

```
✔️ Cambias código
✔️ Incrementas CACHE_NAME
✔️ Guardas archivos
✔️ Recargas navegador (F5)
✔️ SW detecta cambios automáticamente
✔️ Página se recarga una vez
✔️ Cambios aplicados
✔️ Sesión intacta
✔️ LISTO! 🎉
```

### **Producción:**

```
✔️ Haces deploy con nueva versión
✔️ Usuario visita la aplicación
✔️ SW detecta nueva versión automáticamente
✔️ Nueva versión se instala en background
✔️ Página se recarga automáticamente
✔️ Usuario ve cambios nuevos
✔️ Sesión permanece activa
✔️ Sin interrupciones! 🚀
```

---

## 📚 Referencias

- [Service Worker API - MDN](https://developer.mozilla.org/en-US/docs/Web/API/Service_Worker_API)
- [Service Worker Lifecycle](https://web.dev/service-worker-lifecycle/)
- [Workbox (Google)](https://developers.google.com/web/tools/workbox)
- [PWA Best Practices](https://web.dev/progressive-web-apps/)

---

## 🐛 Troubleshooting

### **Problema: El SW no se actualiza**

**Solución:**

1. Verificar que incrementaste `CACHE_NAME`
2. En DevTools: Application → Service Workers → Click "Update"
3. Verificar que no hay errores en consola
4. Verificar que `Update on reload` está marcado (solo desarrollo)

### **Problema: La página no se recarga automáticamente**

**Solución:**

1. Verificar que el código de `main-simple.js` se cargó correctamente
2. Verificar en consola que aparecen los mensajes `[SW]`
3. Verificar que no hay errores JavaScript bloqueantes
4. Hard refresh: Ctrl+Shift+R (Windows/Linux) o Cmd+Shift+R (Mac)

### **Problema: La sesión se pierde**

**Solución:**

1. **NO usar** "Clear Site Data"
2. Verificar que las cookies tienen `SameSite` y `Secure` configurados correctamente
3. Verificar que el dominio/path de las cookies es correcto
4. Solo recargar con F5 o mediante el sistema automático

### **Problema: Los cambios no se ven**

**Solución:**

1. Verificar que incrementaste `CACHE_NAME`
2. Hard refresh: Ctrl+Shift+R
3. Verificar en DevTools → Network que los archivos se están descargando
4. Verificar que no hay errores 404 en Network tab
5. Borrar manualmente la caché antigua en DevTools → Cache Storage (solo para debug)

---

**Documento creado:** 4 de diciembre de 2025  
**Versión:** 1.0  
**Autor:** Sistema de Auto-Actualización PWA - Unimind
