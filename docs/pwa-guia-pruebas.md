# 📱 UniMind PWA - Guía de Pruebas e Instalación

## ✅ PWA Implementada Exitosamente

UniMind ahora es una **Progressive Web App (PWA)** completamente funcional.

---

## 🚀 Cómo Probar la PWA en Localhost

### Opción 1: Usando Chrome en localhost

1. **Inicia XAMPP** (si no está corriendo):

   ```bash
   sudo /opt/lampp/lampp start
   ```

2. **Abre Chrome** y ve a:

   ```
   http://localhost/unimind
   ```

3. **Abre DevTools** (F12) y ve a:
   - **Application** → **Manifest**
   - Verifica que el manifest se carga correctamente
   - **Application** → **Service Workers**
   - Verifica que el SW está activo

4. **Instalar la PWA**:
   - En Chrome, verás un ícono de **"Instalar"** (➕) en la barra de direcciones
   - O ve a: **Menú (⋮) → Instalar UniMind**
   - La app se instalará como aplicación nativa

5. **Verificar instalación**:
   - En Chrome: `chrome://apps`
   - La app aparecerá en tu escritorio/menú de aplicaciones
   - También en el cajón de aplicaciones de Android si estás en móvil

---

## 🌐 Cómo Probar con ngrok (HTTPS - Recomendado)

### Por qué usar ngrok:

- ✅ Obtienes HTTPS automáticamente
- ✅ Puedes probar desde otros dispositivos (móvil)
- ✅ Funcionalidades PWA completas activadas

### Pasos:

1. **Ejecuta el script de deploy**:

   ```bash
   cd /opt/lampp/htdocs/unimind
   ./deploy.sh
   ```

2. **Copia la URL de ngrok** (aparece en la terminal):

   ```
   https://xxxx-xxx-xxx.ngrok-free.app
   ```

3. **Abre la URL en Chrome** (PC o móvil):
   - Chrome en PC: `https://xxxx.ngrok-free.app/unimind`
   - Chrome en Android: Escanea QR o abre directamente

4. **Instalar en móvil**:
   - En Chrome móvil aparecerá: **"Agregar UniMind a la pantalla de inicio"**
   - Toca "Instalar" o "Agregar"
   - La app se instalará como nativa en tu dispositivo

---

## 🔍 Verificar que la PWA Funciona Correctamente

### ✅ Checklist de Pruebas:

1. **Manifest cargado**:
   - DevTools → Application → Manifest
   - Debe mostrar nombre, iconos, colores de UniMind

2. **Service Worker activo**:
   - DevTools → Application → Service Workers
   - Estado: "activated and is running"
   - Scope: `/unimind/public/sw.js`

3. **Caché funcionando**:
   - DevTools → Application → Cache Storage
   - Debe mostrar: `unimind-v1.0.0` con archivos cacheados

4. **Modo offline**:
   - Con la app abierta, desactiva WiFi/datos
   - Recarga la página (F5)
   - Debe mostrar contenido cacheado o página offline personalizada

5. **Instalación**:
   - Verifica que aparece el ícono "Instalar" en Chrome
   - Instala y comprueba que abre en ventana standalone

6. **Iconos**:
   - Al instalar, debe mostrar el icono azul con "UM"
   - Verifica en el escritorio/home screen

---

## 📱 Cómo Instalar en Diferentes Dispositivos

### 🖥️ **Windows/Linux/Mac (Chrome/Edge)**

1. Abre la URL de UniMind
2. Busca el ícono **➕** (instalar) en la barra de direcciones
3. O: **Menú → Instalar UniMind**
4. La app aparecerá como aplicación nativa

### 📱 **Android (Chrome)**

1. Abre la URL en Chrome
2. Toca **Menú (⋮) → Agregar a pantalla de inicio**
3. O espera el banner automático: "Instalar UniMind"
4. La app se instalará en tu cajón de aplicaciones

### 🍎 **iOS/iPadOS (Safari)**

⚠️ iOS tiene soporte limitado de PWA, pero funciona:

1. Abre la URL en Safari
2. Toca el botón **Compartir** (⬆️)
3. Selecciona **"Agregar a pantalla de inicio"**
4. La app aparecerá en tu home screen

**Nota**: iOS no soporta todas las features de PWA (ej: notificaciones push)

---

## 🧪 Comandos Útiles para Debugging

### Ver logs del Service Worker:

```javascript
// En la consola del navegador (F12)
navigator.serviceWorker.getRegistration().then((reg) => console.log(reg));
```

### Desregistrar el Service Worker (si hay problemas):

```javascript
navigator.serviceWorker.getRegistrations().then((registrations) => {
  registrations.forEach((reg) => reg.unregister());
});
```

### Limpiar toda la caché:

```javascript
caches.keys().then((names) => {
  names.forEach((name) => caches.delete(name));
});
```

### Forzar actualización del SW:

```javascript
navigator.serviceWorker.getRegistration().then((reg) => reg.update());
```

---

## 🛠️ Problemas Comunes y Soluciones

### ❌ "Service Worker no se registra"

**Solución**: Verifica que estás usando HTTPS (ngrok) o localhost

### ❌ "No aparece el botón de instalar"

**Solución**:

- Verifica que el manifest.json se carga correctamente
- Revisa que los iconos existen en `public/icons/`
- Cierra y vuelve a abrir Chrome

### ❌ "La app no funciona offline"

**Solución**:

- Verifica que el Service Worker está activo
- Revisa DevTools → Console para errores
- Limpia la caché y recarga

### ❌ "Los cambios no se ven"

**Solución**:

- El SW cachea archivos para velocidad
- Cambia la versión en `sw.js`: `const CACHE_NAME = 'unimind-v1.0.1'`
- O fuerza actualización: Shift + F5

---

## 📊 Características Implementadas

### ✅ Lo que ya funciona:

| Feature                | Estado | Descripción                                 |
| ---------------------- | ------ | ------------------------------------------- |
| **Instalación**        | ✅     | App instalable en todos los dispositivos    |
| **Modo Offline**       | ✅     | Caché de assets estáticos                   |
| **Iconos PWA**         | ✅     | 8 tamaños diferentes                        |
| **Tema personalizado** | ✅     | Colores de UniMind                          |
| **Splash Screen**      | ✅     | Se genera automáticamente                   |
| **Standalone Mode**    | ✅     | Sin barra del navegador                     |
| **Shortcuts**          | ✅     | Accesos rápidos a Tests/Dashboard/Historial |

### ⏳ Features opcionales para futuro:

- 🔔 **Push Notifications** (requiere backend adicional)
- 📍 **Geolocalización** (si necesitas tracking de ubicación)
- 📊 **Background Sync** (sincronizar datos cuando vuelve conexión)
- 🔐 **Biometric Auth** (huella/Face ID para login)

---

## 🎯 Siguiente Paso: Generar APK

Una vez que hayas probado y verificado que la PWA funciona:

### Opción recomendada: **PWABuilder**

1. Ve a: https://www.pwabuilder.com
2. Pega tu URL de ngrok: `https://xxxx.ngrok-free.app/unimind`
3. Click en "Start"
4. Descarga el **APK** o **AAB** generado
5. Instálalo en Android o súbelo a Play Store

**Ventajas**:

- ✅ Gratis y rápido (5 minutos)
- ✅ APK firmado listo para instalar
- ✅ AAB listo para Play Store
- ✅ Sin configuración técnica

---

## 📝 Notas Importantes

### Sobre los iconos:

Los iconos actuales son **placeholders** (SVG con "UM"). Para producción:

- Diseña un logo profesional de 512x512px
- Guárdalo como PNG en `public/icons/icon-512x512.png`
- Genera los demás tamaños con herramientas online como:
  - https://realfavicongenerator.net
  - https://www.pwabuilder.com/imageGenerator

### Sobre el Service Worker:

- **Versión**: Cambia `CACHE_NAME` en `sw.js` cada vez que actualices assets
- **Estrategia**: Usa Cache First para assets, Network First para contenido
- **Debugging**: DevTools → Application → Service Workers

### Sobre ngrok:

- La URL cambia cada vez que reinicias ngrok
- Para URL fija, usa ngrok premium o un dominio real
- Para desarrollo está perfecto así

---

## ✅ Resumen: Tu PWA está Lista

Tu proyecto UniMind ahora:

✅ **Es una PWA funcional** → Se puede instalar como app  
✅ **Funciona offline** → Caché inteligente implementado  
✅ **Es responsive** → Ya lo tenías, ahora es app  
✅ **Tiene iconos** → Placeholders listos (mejóralos después)  
✅ **Listo para APK** → Usa PWABuilder cuando quieras

**🎉 ¡Pruébalo ahora en Chrome!**

---

## 🆘 Soporte

Si tienes problemas:

1. Abre DevTools (F12) → Console
2. Busca errores en rojo
3. Verifica Application → Manifest y Service Workers
4. Revisa que XAMPP esté corriendo

**¡Disfruta tu PWA!** 🚀
