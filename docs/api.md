# API Documentation

Documentación de los endpoints disponibles en UniMind.

## Tabla de Contenidos

- [Endpoints de Estudiante](#endpoints-de-estudiante)
  - [Obtener Tests Disponibles](#obtener-tests-disponibles)
- [Endpoints de Profesor](#endpoints-de-profesor)
- [Endpoints de Administrador](#endpoints-de-administrador)

---

## Endpoints de Estudiante

### Obtener Tests Disponibles

Obtiene la lista de todos los tests (evaluaciones) disponibles para que el estudiante pueda realizar.

**Endpoint:** `GET /controllers/AplicacionesController.php?action=getTestsDisponibles`

**Autenticación:** Requerida (Sesión de estudiante)

**Headers:**

```
Content-Type: application/json
```

**Respuesta Exitosa (200 OK):**

```json
{
  "success": true,
  "message": "Tests obtenidos correctamente",
  "data": [
    {
      "id_test": "1",
      "nombre": "Test de Estrés Percibido",
      "descripcion": "Evaluación del nivel de estrés percibido en el último mes",
      "num_items": "14",
      "created_at": "2025-11-23 10:30:00",
      "updated_at": "2025-11-23 10:30:00"
    },
    {
      "id_test": "2",
      "nombre": "Inventario de Ansiedad de Beck",
      "descripcion": null,
      "num_items": "21",
      "created_at": "2025-11-23 11:00:00",
      "updated_at": null
    }
  ]
}
```

**Respuesta de Error (401 Unauthorized):**

```json
{
  "success": false,
  "message": "Usuario no autenticado",
  "data": null
}
```

**Respuesta de Error (500 Internal Server Error):**

```json
{
  "success": false,
  "message": "Error del servidor: [mensaje de error]",
  "data": null
}
```

**Ejemplo de uso con JavaScript:**

```javascript
async function cargarTests() {
  try {
    const base = window.UNIMIND_BASE || "";
    const baseUrl =
      window.location.origin && window.location.origin !== "null"
        ? window.location.origin + base
        : base;

    const response = await fetch(
      `${baseUrl}/controllers/AplicacionesController.php?action=getTestsDisponibles`,
      {
        method: "GET",
        headers: {
          "Content-Type": "application/json",
        },
        credentials: "include",
      },
    );

    const result = await response.json();

    if (result.success) {
      result.data.forEach((test) => {
        console.log(`- ${test.nombre} (${test.num_items} ítems)`);
      });
    } else {
      console.error("Error:", result.message);
    }
  } catch (error) {
    console.error("Error al cargar tests:", error);
  }
}
```

**Notas:**

- Los datos se devuelven directamente en el array `data`, sin anidación adicional
- Los valores numéricos pueden venir como strings desde la base de datos
- El frontend debe calcular el tiempo estimado y el icono según el tipo de test
- Los campos `descripcion`, `created_at` y `updated_at` pueden ser `null`

---

## Endpoints de Profesor

_(Por documentar)_

---

## Endpoints de Administrador

_(Por documentar)_
