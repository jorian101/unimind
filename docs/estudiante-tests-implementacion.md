# Sistema de Tests para Estudiantes - Cambios Implementados

## Resumen

Se ha implementado la funcionalidad completa para que los estudiantes puedan responder tests psicológicos y ver su historial usando las páginas existentes del sistema.

## Archivos Creados

### 1. `/models/estudiante/TestsEstudianteModel.php`

Modelo que maneja toda la lógica de datos para los tests del estudiante:

- `getTestsDisponibles()` - Lista todos los tests disponibles
- `getTestById()` - Obtiene información de un test específico
- `getItemsByTest()` - Carga las preguntas de un test
- `getOpcionesRespuesta()` - Obtiene las opciones de respuesta
- `iniciarAplicacion()` - Inicia una nueva aplicación de test
- `registrarRespuesta()` - Guarda cada respuesta del estudiante
- `finalizarAplicacion()` - Calcula la puntuación final
- `getHistorialUsuario()` - Obtiene el historial de tests completados
- `getResultadoAplicacion()` - Obtiene resultado de un test específico

### 2. `/controllers/AplicacionesController.php`

Controlador API REST para manejar las operaciones de tests:

- Endpoint para obtener tests disponibles
- Endpoint para obtener datos de un test específico
- Endpoint para iniciar un test
- Endpoint para enviar respuestas
- Endpoint para obtener historial
- Endpoint para obtener resultado

## Archivos Modificados

### 1. `/views/estudiante/tests.php`

**Cambios:**

- Ahora carga los tests dinámicamente desde la base de datos usando `TestsEstudianteModel`
- Muestra la información real: nombre, descripción, número de ítems
- Asigna iconos automáticamente según el tipo de test (estrés, ansiedad, depresión, burnout)
- Calcula tiempo estimado basado en número de preguntas
- Muestra mensaje cuando no hay tests disponibles

### 2. `/views/estudiante/formulario.php`

**Cambios:**

- Carga las preguntas reales desde la base de datos según el `test_id` recibido
- Usa los `id_item` reales de la base de datos en los campos del formulario
- Carga las opciones de respuesta dinámicamente desde la tabla `Opciones_Respuesta`
- Mantiene toda la funcionalidad de navegación móvil y validaciones existentes

### 3. `/controllers/submit-test.php`

**Cambios:**

- Reescrito completamente para usar los procedimientos almacenados
- Flujo: Iniciar aplicación → Registrar respuestas → Finalizar y calcular
- Usa `sp_iniciar_aplicacion`, `sp_registrar_respuesta`, `sp_finalizar_aplicacion_y_calcular_puntuacion`
- Guarda el resultado en sesión para mostrarlo
- Redirige al historial con mensaje de éxito

### 4. `/views/estudiante/historial.php`

**Cambios:**

- Carga el historial real desde la base de datos usando `sp_obtener_historial_usuario`
- Muestra tabla con: fecha, nombre del test, puntuación y nivel de resultado
- Clasifica automáticamente los niveles (bajo/moderado/alto) con colores
- Muestra mensaje cuando no hay evaluaciones completadas
- Incluye sección de estadísticas (total evaluaciones, promedio, última fecha)
- Muestra mensaje de éxito al completar un test (se oculta automáticamente)

### 5. `/views/estudiante/tests.css`

**Cambios:**

- Añadida clase `.no-tests` para mensaje cuando no hay tests disponibles
- Estilizado del mensaje vacío con icono y texto centrado

## Flujo de Uso

1. **Ver Tests Disponibles:**
   - El estudiante va a la página de Tests
   - Se cargan todos los tests desde la base de datos
   - Muestra información: nombre, descripción, número de ítems, tiempo estimado

2. **Iniciar un Test:**
   - Click en "Iniciar Test"
   - Redirige a `formulario.php` con parámetros del test
   - Carga automáticamente las preguntas y opciones desde la BD

3. **Responder el Test:**
   - Navega por las preguntas (móvil: una a la vez, desktop: todas visibles)
   - Validación: debe responder todas las preguntas
   - Click en "Enviar Evaluación"

4. **Procesar Respuestas:**
   - `submit-test.php` inicia la aplicación en la BD
   - Registra cada respuesta individualmente
   - Llama al procedimiento de cálculo de puntuación
   - Aplica baremos según el tipo de test

5. **Ver Resultados:**
   - Redirige al historial con mensaje de éxito
   - Muestra la puntuación y nivel obtenido
   - El historial se actualiza automáticamente

## Procedimientos Almacenados Utilizados

- `sp_obtener_tests_disponibles()` - Lista tests
- `sp_obtener_items_por_test()` - Carga preguntas
- `sp_obtener_opciones_respuesta_generales()` - Carga opciones
- `sp_iniciar_aplicacion()` - Crea nueva aplicación
- `sp_registrar_respuesta()` - Guarda respuesta individual
- `sp_finalizar_aplicacion_y_calcular_puntuacion()` - Calcula resultado
- `sp_obtener_historial_usuario()` - Obtiene historial

## Características Destacadas

✅ **Integración Total con BD:** Todos los datos se cargan y guardan en la base de datos
✅ **Uso de Procedimientos Almacenados:** Lógica de negocio en la BD
✅ **Cálculo Automático:** Los baremos se aplican automáticamente según el test
✅ **Responsive:** Funciona en móvil, tablet y desktop
✅ **Validaciones:** No se puede enviar sin responder todas las preguntas
✅ **Historial Completo:** El estudiante puede ver todas sus evaluaciones
✅ **Mensajes de Feedback:** Éxito, errores, estados vacíos

## Notas Técnicas

- Se mantiene compatibilidad con el diseño existente (CSS y estructura HTML)
- Las sesiones se usan para autenticación y mensajes temporales
- Los errores se registran en el log de PHP para debugging
- El modelo usa PDO con prepared statements (seguridad contra SQL injection)
- Los stored procedures manejan toda la lógica de cálculo

## Testing Recomendado

1. Verificar que los tests se cargan correctamente desde la BD
2. Probar el flujo completo: ver tests → iniciar → responder → enviar
3. Verificar que las puntuaciones se calculan correctamente
4. Comprobar que el historial muestra las evaluaciones completadas
5. Probar en diferentes dispositivos (móvil, tablet, desktop)
