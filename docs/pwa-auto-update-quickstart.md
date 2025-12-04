# 🔄 Sistema de Auto-Actualización PWA - Guía Rápida

## 🎯 Objetivo

**Nunca más uses "Clear Site Data"**. Este sistema actualiza automáticamente tu PWA sin perder la sesión.

---

## ⚡ Inicio Rápido

### En Desarrollo (Localhost)

**Cada vez que hagas cambios en el código:**

```bash
# 1. Incrementar versión automáticamente
./scripts/bump-version.sh

# 2. Recargar navegador (F5)
# ✓ Los cambios se aplicarán automáticamente
# ✓ La sesión permanecerá activa
```

### Configuración Chrome DevTools (Solo Desarrollo)

```
F12 → Application → Service Workers
☑️ Marcar "Update on reload"
```

---

## 📝 Comandos Útiles

### Incrementar versión:

```bash
# Incremento automático (patch): 1.0.5 → 1.0.6
./scripts/bump-version.sh

# Incremento minor: 1.0.5 → 1.1.0
./scripts/bump-version.sh minor

# Incremento major: 1.0.5 → 2.0.0
./scripts/bump-version.sh major

# Versión específica
./scripts/bump-version.sh 2.1.3
```

### Ver estado del Service Worker:

```javascript
// En consola del navegador
navigator.serviceWorker.getRegistration().then((reg) => {
  console.log("Estado:", reg.active?.state);
  console.log("Waiting:", reg.waiting);
  console.log("Installing:", reg.installing);
});
```

### Forzar actualización manual:

```javascript
// En consola del navegador
navigator.serviceWorker.getRegistration().then((reg) => {
  reg.update();
});
```

---

## 🔍 Verificar que Funciona

### Consola del navegador debe mostrar:

```
[SW] Service Worker registrado exitosamente
[SW] Nueva versión detectada, instalando...
[SW] Nueva versión instalada, activando automáticamente...
[SW] Nueva versión activa: unimind-v1.0.X
[SW] Nuevo Service Worker en control, recargando página...
```

### DevTools → Application → Cache Storage:

- Solo debe existir `unimind-v1.0.X` (versión actual)
- Las versiones antiguas se eliminan automáticamente

---

## ✅ Checklist de Actualización

Antes de cada deploy:

- [ ] Incrementar versión: `./scripts/bump-version.sh`
- [ ] Probar en localhost que funciona
- [ ] Verificar que la sesión no se pierde
- [ ] Commit: `git add sw.js && git commit -m "chore: bump SW version"`
- [ ] Push y deploy

---

## 🐛 Solución de Problemas

### Problema: No se ve el cambio

**Solución:**

```bash
# 1. Verificar que incrementaste la versión
grep "CACHE_NAME" sw.js

# 2. Hard refresh
# Ctrl + Shift + R (Windows/Linux)
# Cmd + Shift + R (Mac)

# 3. Verificar en consola que aparecen mensajes [SW]
```

### Problema: La sesión se pierde

**¡NO HAGAS ESTO!**

- ❌ Clear Site Data
- ❌ Borrar cookies
- ❌ Borrar localStorage

**HAZ ESTO:**

- ✅ Solo recargar (F5)
- ✅ Usar el sistema automático

---

## 📚 Documentación Completa

Ver: [`docs/pwa-auto-update.md`](./pwa-auto-update.md)

---

## 🚀 Flujo Completo

```
┌─────────────────────────────────────────────────────────┐
│ 1. Desarrollador hace cambios en código                │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 2. Ejecuta: ./scripts/bump-version.sh                  │
│    (Incrementa CACHE_NAME automáticamente)             │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 3. Usuario recarga página (F5)                         │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 4. Service Worker detecta nueva versión                │
│    - Descarga nuevo sw.js                              │
│    - Instala en background                             │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 5. main-simple.js detecta instalación                  │
│    - Envía mensaje SKIP_WAITING                        │
│    - Activa nuevo SW inmediatamente                    │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 6. Nuevo SW se activa                                  │
│    - Borra cachés antiguas                             │
│    - Toma control con clients.claim()                  │
│    - Envía mensaje NEW_VERSION                         │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 7. main-simple.js recibe controllerchange              │
│    - Recarga página automáticamente                    │
└─────────────────┬───────────────────────────────────────┘
                  │
                  ▼
┌─────────────────────────────────────────────────────────┐
│ 8. ✓ Cambios aplicados                                 │
│    ✓ Sesión intacta                                    │
│    ✓ Datos preservados                                 │
└─────────────────────────────────────────────────────────┘
```

---

## 💡 Tips

### Desarrollo:

- Usa `Update on reload` en DevTools
- Incrementa versión con cada cambio importante
- Revisa la consola para debugging

### Producción:

- Automatiza el bump de versión en CI/CD
- Considera mostrar notificación al usuario
- Monitorea errores con Sentry/similar

---

## 📞 Soporte

Si algo no funciona:

1. Lee [`docs/pwa-auto-update.md`](./pwa-auto-update.md)
2. Revisa la sección "Troubleshooting"
3. Verifica la consola del navegador
4. Revisa DevTools → Application → Service Workers

---

**Última actualización:** 4 de diciembre de 2025  
**Versión del sistema:** 1.0
