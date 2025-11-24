**Sugerencias y Notificaciones — Instrucciones de despliegue y prueba**

Resumen rápido

- Se añadió soporte para registrar quién sugiere una `Aplicacion` (columna `sugerido_por` en `Aplicaciones`) y el origen (`origen`).
- Se añadió una tabla `Notificaciones` para notificaciones in-app.
- El endpoint `api/suggest_test.php` ahora crea notificaciones para cada alumno cuando un profesor sugiere un test.
- Se añadió la UI en el encabezado (`views/pageHeader.php`) y un script cliente `public/js/notifications.js` que consulta `api/notifications.php`.

Pasos para aplicar la migración (local)

1. Ejecutar el migrador incluido (usa la configuración en `database/Database.php`):

```powershell
php .\scripts\apply_migration.php .\database\migrations\20251124_add_sugerido_por_and_notifications.sql
```

2. (Opcional) Simular una sugerencia para verificar el flujo (recomendado para pruebas locales):

```powershell
php .\scripts\simulate_suggest.php <id_curso> "Nombre demo" 5
# ejemplo: php .\scripts\simulate_suggest.php 1 "Demo sugerido" 5
```

3. Verificar en base de datos: revisar que la tabla `Aplicaciones` tenga `sugerido_por` y `origen` en las filas nuevas y que `Notificaciones` contenga filas para los alumnos.

Prueba desde la UI

- Iniciar sesión como alumno y acceder a la aplicación; en el encabezado verá un ícono (campana) que mostrará notificaciones recientes.
- Hacer click en una notificación que incluya `id_test` abrirá el listado de tests filtrado.

Notas técnicas

- El endpoint `api/suggest_test.php` requiere que el profesor sea responsable del curso. El código crea un `Test` si se pasan `test_name` y `num_items`, y luego crea `Aplicaciones` y `Notificaciones` para cada alumno.
- La tabla `Notificaciones` usa un campo `metadata` JSON para incluir datos como `id_test`.

Reversión

- Si necesitas revertir los cambios en la DB manualmente, eliminar las columnas y la tabla `Notificaciones`. Haz backup antes.

Contacto

- Si quieres que aplique estilos o animaciones adicionales a la campana o mejore la experiencia (marcar notificaciones como leídas, acciones rápida), indícamelo y lo implemento.
