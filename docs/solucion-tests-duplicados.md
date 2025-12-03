# Solución de Duplicación de Tests Sugeridos

## Problema Identificado

Los tests sugeridos se mostraban duplicados en la vista de estudiantes cuando:

1. El mismo test era sugerido en múltiples cursos donde el estudiante estaba inscrito
2. Múltiples profesores sugerían el mismo test
3. Un estudiante recibía el mismo test de diferentes combinaciones curso-profesor

## Causa Raíz

La estructura original de la tabla `Sugerencias` usaba una clave única compuesta por:

```sql
UNIQUE KEY `uk_curso_test_profesor` (`id_curso`, `id_test`, `id_profesor`)
```

Esto permitía múltiples registros del mismo test para un estudiante si venían de diferentes cursos o profesores.

## Solución Implementada

### 1. Rediseño de la Tabla Sugerencias (db.sql)

**Cambio de enfoque**: De modelo curso-test-profesor a estudiante-test

**Antes:**

```sql
CREATE TABLE `Sugerencias` (
    `id_sugerencia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_curso` INT NOT NULL,
    `id_test` INT NOT NULL,
    `id_profesor` INT NOT NULL,
    ...
    UNIQUE KEY `uk_curso_test_profesor` (`id_curso`, `id_test`, `id_profesor`)
);
```

**Después:**

```sql
CREATE TABLE `Sugerencias` (
    `id_sugerencia` INT AUTO_INCREMENT PRIMARY KEY,
    `id_estudiante` INT NOT NULL,
    `id_test` INT NOT NULL,
    `profesores_ids` TEXT NULL COMMENT 'JSON array de IDs de profesores',
    `cursos_ids` TEXT NULL COMMENT 'JSON array de IDs de cursos',
    `fecha_sugerencia` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `fecha_ultima_sugerencia` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    ...
    UNIQUE KEY `uk_estudiante_test` (`id_estudiante`, `id_test`)
);
```

**Beneficios:**

- Un estudiante solo tendrá **UN registro** por test, sin importar cuántos profesores o cursos lo sugieran
- Se mantiene trazabilidad de todos los profesores/cursos mediante arrays JSON
- Se registra tanto la fecha inicial como la última sugerencia

### 2. Actualización de Stored Procedures (procedures.sql)

#### sp_sugerir_test

**Nueva lógica con intervalo de 2 meses:**

- Itera sobre todos los estudiantes del curso
- Para cada estudiante, verifica si ya existe una sugerencia del mismo test
- **Si existe Y está dentro de 2 meses**: REUTILIZA la sugerencia existente
  - Agrega el nuevo profesor/curso a los arrays JSON usando `JSON_MERGE_PRESERVE`
  - Actualiza `fecha_ultima_sugerencia` a NOW()
  - Contador: `estudiantes_reusados`
- **Si existe pero pasaron 2+ meses**: RENUEVA la sugerencia
  - Elimina la sugerencia antigua
  - Crea una nueva sugerencia con el profesor/curso actual
  - Reinicia el intervalo de 2 meses
  - Contador: `estudiantes_nuevos`
- **Si NO existe**: CREA NUEVA sugerencia
  - Contador: `estudiantes_nuevos`
- Retorna el total de estudiantes afectados y desglose

```sql
CREATE PROCEDURE sp_sugerir_test(
    IN p_id_curso INT,
    IN p_id_test INT,
    IN p_id_profesor INT
)
BEGIN
    -- Cursor para iterar estudiantes del curso
    -- Para cada estudiante:
    --   1. Verificar si existe sugerencia previa
    --   2. Calcular meses transcurridos desde fecha_sugerencia
    --   3. Si < 2 meses: REUTILIZAR (agregar a JSON arrays)
    --   4. Si >= 2 meses: RENOVAR (eliminar y crear nueva)
    --   5. Si no existe: CREAR NUEVA
END
```

#### sp_obtener_tests_sugeridos_estudiante

**Simplificación:**

- Busca directamente por `id_estudiante`
- Extrae información del primer profesor/curso de los arrays JSON
- Elimina joins complejos e innecesarios

#### sp_obtener_todos_tests_estudiante

**Optimización:**

- Primera parte del UNION: tests sugeridos (busca por `id_estudiante`)
- Segunda parte: tests generales NO sugeridos para ese estudiante
- Ordena poniendo sugeridos primero

### 3. Actualización de Seeds (seed.sql)

Los datos de prueba ahora crean sugerencias por estudiante:

```sql
-- Ejemplo: Test 1 sugerido por Profesor 2 en Curso 1
-- Se crea una fila por cada estudiante (IDs: 4,5,6,7,8,18,19,20,21,22)
INSERT INTO `Sugerencias` (`id_estudiante`, `id_test`, `profesores_ids`, `cursos_ids`, ...)
VALUES
(4, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
(5, 1, '[2]', '[1]', 'pendiente', '2025-11-20 10:00:00'),
...
```

### 4. Actualización de API (suggest_test.php)

El endpoint ahora:

- Usa el stored procedure actualizado
- Lee `estudiantes_afectados` del resultado
- Retorna mensaje confirmando cuántos estudiantes recibieron la sugerencia

### 5. Frontend (Sin cambios necesarios)

El modelo `TestsEstudianteModel.php` usa los stored procedures, así que **no requiere modificaciones**.

## Archivos Modificados

1. `/database/db.sql` - Nueva estructura de tabla Sugerencias
2. `/database/procedures.sql` - 3 procedimientos actualizados
3. `/database/seed.sql` - Datos de prueba con nueva estructura
4. `/api/suggest_test.php` - Manejo de respuesta del stored procedure
5. `/database/migration_sugerencias.sql` - Script de migración (NUEVO)

## Migración de Base de Datos Existente

### Opción 1: Seed completo (DESTRUCTIVO)

```bash
cd /opt/lampp/htdocs/unimind
/opt/lampp/bin/php database/run_seed.php
```

⚠️ **ADVERTENCIA**: Elimina todos los datos existentes

### Opción 2: Migración segura (RECOMENDADO)

```bash
cd /opt/lampp/htdocs/unimind
/opt/lampp/bin/mysql -u root -p db_tests_estres_ansiedad < database/migration_sugerencias.sql
```

✅ Preserva datos mediante conversión automática

El script de migración:

1. Crea backup de la tabla original
2. Convierte sugerencias de curso a sugerencias por estudiante
3. Agrupa múltiples sugerencias del mismo test en un solo registro con arrays JSON
4. Mantiene el backup para rollback si es necesario

## Resultado Final

### Antes (con duplicados):

```
Estudiante ID=4 inscrito en Curso 1 y Curso 3
Profesor A sugiere Test 1 en Curso 1 → Registro 1
Profesor B sugiere Test 1 en Curso 3 → Registro 2
Resultado: Estudiante ve Test 1 DUPLICADO ❌
```

### Después (sin duplicados):

```
Estudiante ID=4 inscrito en Curso 1 y Curso 3
Profesor A sugiere Test 1 en Curso 1 → Crea registro único
Profesor B sugiere Test 1 en Curso 3 → Actualiza el mismo registro
Resultado: Estudiante ve Test 1 UNA VEZ ✅
Metadata: profesores_ids=[A, B], cursos_ids=[1, 3]
```

## Lógica del Intervalo de 2 Meses

El sistema ahora implementa una **ventana de reuso de 2 meses** para las sugerencias:

### Escenario 1: Sugerencias dentro de 2 meses (REUSO)

```
DÍA 1:  Profesor A sugiere Test 1 en Curso 1
        → Se crea: { estudiante: 4, test: 1, profesores: [A], cursos: [1], fecha: 2025-01-01 }

DÍA 30: Profesor B sugiere Test 1 en Curso 3 (mismo estudiante 4)
        → Se REUTILIZA el registro existente
        → Se actualiza: { profesores: [A, B], cursos: [1, 3], fecha_ultima: 2025-01-30 }
        ✅ Estudiante ve el test UNA sola vez
        ✅ Se mantiene información de ambos profesores/cursos
```

### Escenario 2: Sugerencias después de 2+ meses (RENOVACIÓN)

```
DÍA 1:   Profesor A sugiere Test 1 en Curso 1
         → Se crea: { estudiante: 4, test: 1, profesores: [A], cursos: [1], fecha: 2025-01-01 }

DÍA 70:  Profesor B sugiere Test 1 en Curso 3 (mismo estudiante 4)
         → Han pasado 2+ meses (70 días ≈ 2.3 meses)
         → Se ELIMINA el registro anterior
         → Se CREA NUEVO: { estudiante: 4, test: 1, profesores: [B], cursos: [3], fecha: 2025-03-12 }
         ✅ Estudiante ve el test UNA sola vez
         ✅ Se reinicia el ciclo con el nuevo profesor/curso
         ⚠️ Se pierde el historial del Profesor A (por diseño)
```

### Escenario 3: Múltiples profesores en el mismo día

```
DÍA 1: Profesor A sugiere Test 1 en Curso 1
       → Crea registro para estudiante 4

DÍA 1: Profesor B sugiere Test 1 en Curso 3 (mismo estudiante 4)
       → REUTILIZA registro
       → profesores: [A, B], cursos: [1, 3]

DÍA 1: Profesor C sugiere Test 1 en Curso 5 (mismo estudiante 4)
       → REUTILIZA registro
       → profesores: [A, B, C], cursos: [1, 3, 5]

✅ Resultado: Estudiante 4 ve Test 1 UNA sola vez con información de 3 profesores
```

### Justificación del Intervalo de 2 Meses

**¿Por qué 2 meses?**

- Tiempo razonable para que un estudiante complete un test sugerido
- Evita "spam" de múltiples sugerencias del mismo test
- Permite renovar sugerencias si el test no fue completado en un plazo prudente

**Comportamiento deseado:**

- ✅ Dentro de 2 meses: Agrupa todas las sugerencias (sin duplicar)
- ✅ Después de 2 meses: Permite una nueva sugerencia "fresca"
- ✅ Evita duplicados en TODO momento

## Próximos Pasos (Opcional)

Si deseas mostrar **todos** los profesores/cursos que sugirieron un test:

1. Actualizar `views/estudiante/tests.php` para parsear los arrays JSON
2. Mostrar lista completa: "Sugerido por: Prof. A (Curso 1), Prof. B (Curso 3)"
3. Actualizar CSS para el nuevo diseño de información

Por ahora, el sistema muestra solo el primer profesor/curso, lo cual es suficiente para evitar duplicados.

## Verificación

Después de aplicar los cambios, verifica con:

```sql
-- Ver sugerencias de un estudiante (sin duplicados)
SELECT id_estudiante, id_test, profesores_ids, cursos_ids, fecha_sugerencia
FROM Sugerencias
WHERE id_estudiante = 4;

-- Resultado esperado: UN registro por test
```

## Rollback (Si es necesario)

Si algo sale mal con la migración:

```sql
-- Restaurar desde backup
DROP TABLE IF EXISTS `Sugerencias`;
RENAME TABLE `Sugerencias_backup` TO `Sugerencias`;
```
