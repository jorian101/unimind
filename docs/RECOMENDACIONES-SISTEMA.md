# Sistema de Recomendaciones - Unimind

## Implementación Completa ✅

### Estructura MVC

#### 1. Base de Datos

**Tabla: `Recomendaciones`**

```sql
- id_recomendacion (PK)
- titulo
- descripcion
- categoria (mental, profesional, fisica, academica, social)
- tipo_test (estres, ansiedad, ambos)
- nivel_minimo (1-5)
- nivel_maximo (1-5)
- prioridad (1-5)
- activa (boolean)
- fecha_creacion
- fecha_actualizacion
```

**Datos iniciales:** 18 recomendaciones pre-cargadas con el seed.sql

#### 2. Modelo

**Archivo:** `/models/administrador/RecomendacionesModel.php`

Métodos principales:

- `getAll()` - Obtener todas las recomendaciones
- `getAllActive()` - Solo recomendaciones activas
- `getById($id)` - Obtener por ID
- `create($data)` - Crear nueva recomendación
- `update($id, $data)` - Actualizar recomendación
- `delete($id)` - Eliminar recomendación
- `getByCategoria($categoria)` - Filtrar por categoría
- `getByMagnitud($magnitud)` - Filtrar por nivel
- `getRecomendacionesParaEstudiante($idEstudiante)` - **Auto-recomendación inteligente**
- `getEstadisticas()` - Estadísticas del sistema
- `search($searchTerm)` - Búsqueda por texto
- `toggleActiva($id)` - Activar/desactivar

**Patrón Template Method:** Hereda de `BaseModel` para consistencia.

#### 3. Controlador

**Archivo:** `/controllers/RecomendacionesController.php`

Métodos API Administrador:

- `handleApiGet()` - GET todas las recomendaciones con filtros
- `handleApiGetEstadisticas()` - GET estadísticas
- `handleApiCreate()` - POST crear nueva
- `handleApiUpdate($id)` - PUT actualizar
- `handleApiDelete($id)` - DELETE eliminar
- `handleApiToggle($id)` - PATCH activar/desactivar

Métodos API Estudiante:

- `handleApiEstudianteGet()` - GET recomendaciones personalizadas

#### 4. API Endpoint

**Archivo:** `/api/recomendaciones.php`

Rutas disponibles:

```
GET    /api/recomendaciones.php                    - Listar (admin)
GET    /api/recomendaciones.php?stats=true         - Estadísticas (admin)
GET    /api/recomendaciones.php?categoria=mental   - Filtrar por categoría
GET    /api/recomendaciones.php?magnitud=3         - Filtrar por nivel
GET    /api/recomendaciones.php?search=texto       - Buscar
GET    /api/recomendaciones.php?estudiante=true    - Personalizadas (estudiante)
POST   /api/recomendaciones.php                    - Crear (admin)
PUT    /api/recomendaciones.php?id=X               - Actualizar (admin)
DELETE /api/recomendaciones.php?id=X               - Eliminar (admin)
PATCH  /api/recomendaciones.php?id=X&action=toggle - Toggle activa (admin)
```

#### 5. Vista Administrador

**Archivo:** `/views/administrador/recomendaciones.php`

Características:

- ✅ CRUD completo con JavaScript
- ✅ Tabla dinámica con datos de base de datos
- ✅ Filtros por categoría y magnitud
- ✅ Búsqueda en tiempo real
- ✅ Modal para crear/editar
- ✅ Estadísticas en cards
- ✅ Validaciones de formulario
- ✅ Notificaciones de éxito/error
- ✅ Responsive design

**CSS:** `/views/administrador/recomendaciones.css`

#### 6. Vista Estudiante

**Archivo:** `/views/estudiante/recomendaciones.php`

Características:

- ✅ Recomendaciones personalizadas según últimas aplicaciones
- ✅ Basadas en niveles de estrés/ansiedad detectados
- ✅ Grid cards con diseño moderno
- ✅ Badges de categoría, nivel y tipo de test
- ✅ Iconos según categoría
- ✅ Colores según prioridad
- ✅ Estado vacío cuando no hay resultados
- ✅ Responsive design

---

## Sistema de Auto-Recomendación 🤖

### Lógica Inteligente

La función `getRecomendacionesParaEstudiante()` implementa:

1. **Obtención de últimas aplicaciones:**
   - Busca las 2 últimas aplicaciones completadas del estudiante
   - Extrae el tipo de test (estrés/ansiedad)
   - Obtiene el nivel calculado (normal, leve, moderado, alto, severo)

2. **Mapeo de niveles:**

   ```php
   'normal'   => 1
   'leve'     => 2
   'moderado' => 3
   'alto'     => 4
   'severo'   => 5
   ```

3. **Filtrado inteligente:**
   - Busca recomendaciones donde:
     - `tipo_test` coincide con el test aplicado O es 'ambos'
     - `nivel_minimo <= nivel_detectado`
     - `nivel_maximo >= nivel_detectado`
     - `activa = 1`
   - Ordena por prioridad descendente
   - Limita a 5 por cada aplicación
   - Evita duplicados

4. **Personalización:**
   - Cada recomendación incluye:
     - Tipo de test que la generó
     - Nivel detectado
     - Nivel numérico

### Ejemplo de Uso

**Estudiante con nivel "moderado" en test de estrés:**

- Recibirá recomendaciones con:
  - `tipo_test = 'estres'` o `'ambos'`
  - `nivel_minimo <= 3`
  - `nivel_maximo >= 3`
  - Ordenadas por prioridad

**Resultado:** Recomendaciones como:

- Taller de Gestión del Tiempo (académica, nivel 2-3)
- Asesoría Académica Personalizada (académica, nivel 2-4)
- Yoga y Meditación (física, nivel 2-3)

---

## Categorías de Recomendaciones

### 1. Mental

- Iconos: `fa-spa`, `fa-brain`
- Color: Turquesa (acc)
- Ejemplos: Mindfulness, Meditación, Relajación

### 2. Profesional

- Iconos: `fa-user-doctor`
- Color: Gris (var)
- Ejemplos: Consultas psicológicas, Terapias

### 3. Física

- Iconos: `fa-dumbbell`, `fa-running`
- Color: Naranja (sec)
- Ejemplos: Ejercicio, Yoga, Actividad física

### 4. Académica

- Iconos: `fa-book-open`, `fa-graduation-cap`
- Color: Vino (pri)
- Ejemplos: Gestión del tiempo, Asesoría académica

### 5. Social

- Iconos: `fa-users`
- Color: Turquesa (acc)
- Ejemplos: Grupos de apoyo, Actividades sociales

---

## Niveles y Prioridades

### Niveles (1-5)

1. **Normal/Muy Bajo** - Verde
2. **Leve/Bajo** - Amarillo claro
3. **Moderado/Medio** - Naranja
4. **Alto** - Rojo
5. **Severo/Crítico** - Rojo oscuro

### Prioridades (1-5)

1. **Baja** - Recomendaciones opcionales
2. **Media-Baja** - Útiles pero no urgentes
3. **Media** - Recomendables
4. **Alta** - Importante considerar
5. **Crítica** - Atención inmediata requerida

---

## Flujo de Uso

### Para Administradores:

1. Acceder a `/views/administrador/recomendaciones.php`
2. Ver estadísticas en cards superiores
3. Crear nuevas recomendaciones con botón "Nueva Recomendación"
4. Filtrar por categoría o magnitud
5. Buscar por texto
6. Editar o eliminar existentes
7. Activar/desactivar según necesidad

### Para Estudiantes:

1. Completar tests de estrés/ansiedad
2. Acceder a `/views/estudiante/recomendaciones.php`
3. Ver recomendaciones personalizadas automáticamente
4. Leer descripciones detalladas
5. Identificar prioridad y categoría
6. Aplicar recomendaciones según nivel detectado

---

## Comandos de Base de Datos

### Recrear base de datos:

```bash
cd /opt/lampp/htdocs/unimind
sudo /opt/lampp/bin/php database/clean_database.php
```

### Poblar con datos iniciales:

```bash
sudo /opt/lampp/bin/php database/run_seed.php
```

---

## Tecnologías y Patrones

✅ **MVC** - Separación completa de responsabilidades  
✅ **Template Method** - BaseModel para operaciones comunes  
✅ **Singleton** - Database connection única  
✅ **RESTful API** - Endpoints estándar  
✅ **JavaScript Vanilla** - Sin dependencias externas  
✅ **CSS Variables** - Tema consistente  
✅ **Responsive Design** - Mobile-first  
✅ **SQL Prepared Statements** - Seguridad contra SQL injection  
✅ **PDO** - Abstracción de base de datos

---

## Testing

### Verificar funcionamiento:

1. **Base de datos:**

   ```sql
   SELECT COUNT(*) FROM Recomendaciones WHERE activa = 1;
   -- Debe retornar 18
   ```

2. **API Administrador:**

   ```
   GET /unimind/api/recomendaciones.php
   ```

3. **API Estudiante:**

   ```
   GET /unimind/api/recomendaciones.php?estudiante=true
   ```

4. **Vista Administrador:**
   - Navegar a: `/unimind/views/administrador/recomendaciones.php`
   - Verificar CRUD completo

5. **Vista Estudiante:**
   - Completar un test primero
   - Navegar a: `/unimind/views/estudiante/recomendaciones.php`
   - Ver recomendaciones personalizadas

---

## Mantenimiento

### Agregar nueva recomendación:

1. Usar interfaz web del administrador
2. O ejecutar SQL:
   ```sql
   INSERT INTO Recomendaciones
   (titulo, descripcion, categoria, tipo_test, nivel_minimo, nivel_maximo, prioridad, activa)
   VALUES
   ('Nueva Recomendación', 'Descripción...', 'mental', 'ambos', 1, 3, 3, 1);
   ```

### Desactivar recomendación:

```sql
UPDATE Recomendaciones SET activa = 0 WHERE id_recomendacion = X;
```

### Actualizar prioridad:

```sql
UPDATE Recomendaciones SET prioridad = 5 WHERE nivel_minimo >= 4;
```

---

## Mejoras Futuras (Opcional)

- [ ] Sistema de seguimiento de recomendaciones aplicadas
- [ ] Notificaciones push cuando hay nuevas recomendaciones
- [ ] Feedback de estudiantes sobre efectividad
- [ ] Recomendaciones por IA basadas en patrones históricos
- [ ] Exportar recomendaciones a PDF
- [ ] Compartir recomendaciones con profesores/tutores
- [ ] Recordatorios programados
- [ ] Integración con calendario para seguimiento

---

**Fecha de implementación:** Diciembre 10, 2025  
**Estado:** ✅ Completamente funcional  
**Versión:** 1.0.0
