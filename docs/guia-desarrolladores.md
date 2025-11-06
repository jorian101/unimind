# Guía para Desarrolladores: Integración del Sidebar y Gestión de Páginas

Para enseñar a los desarrolladores que trabajen en otros roles (como profesor, estudiante o administrador) cómo integrar y personalizar el sidebar en sus páginas, crear nuevas páginas/componentes y gestionar commits, sigue esta guía paso a paso. Esta se basa en la estructura actual del proyecto (un sistema web en PHP con MVC, ubicado en unimind). Asegúrate de compartir esta documentación con ellos y de que entiendan la estructura del proyecto antes de empezar.

1. Crea la nueva página en la carpeta correspondiente a su rol. Por ejemplo:
   - Para un estudiante: `views/estudiante/nueva-pagina.php`
   - Para un profesor: `views/profesor/nueva-pagina.php`
   - Para un administrador: `views/administrador/nueva-pagina.php`

2. Incluye el header de página al inicio del archivo de la nueva página. El layout general (sidebar, header global y estilos comunes) ya está incluido en `index.php`, por lo que las páginas solo necesitan su contenido específico. Usa la función `renderPageHeader` para el título y breadcrumb. Es obligatorio incluir `require_once '../pageHeader.php';` para cargar las funciones necesarias, y luego llamar a `renderPageHeader` con el título y el array de breadcrumb. Además, asegúrate de que el contenido esté dentro de un contenedor apropiado, como `<main>` o `<div class="main-content">`.

   ```php
   <?php
   require_once '../pageHeader.php';
   renderPageHeader('Título de la Nueva Página', ['Breadcrumb', 'Item']);
   ?>
   <!-- Aquí va el contenido específico de tu página -->
   <main class="main-content">
       <h1>Contenido de la nueva página</h1>
       <!-- Tu HTML/PHP aquí -->
   </main>
   ```

   **Nota sobre el breadcrumb:** El array pasado a `renderPageHeader` representa la ruta de navegación (breadcrumb). Por ejemplo, `['UniMind', 'Dashboard']` mostraría "UniMind > Dashboard". El primer elemento es el nivel superior (como el nombre del sistema o sección), y los siguientes son los subniveles hasta la página actual. Ajusta según la jerarquía de tu página.

3. Abre [sidebar-config.php](../utils/sidebar-config.php) y localiza el array del rol que quieres modificar (por ejemplo: `'profesor'`, `'estudiante'`, `'administrador'`).

4. Edita el array `'menu'` para agregar, quitar o cambiar elementos. Cada elemento es un array con:
   - `'icon'`: Clase de FontAwesome (por ejemplo: `'fas fa-home'`). Los iconos se obtienen de FontAwesome (https://fontawesome.com/icons). Busca el icono deseado en su sitio web, copia la clase (como 'fas fa-home' para un ícono sólido de casa), y úsala aquí.
   - `'label'`: Texto del menú.
   - `'page'`: Nombre de la página (debe coincidir con el archivo en `views/{rol}/`).
   - Opcional: `'submenu'` para submenús anidados.

   Ejemplo para agregar un nuevo ítem al rol de profesor:

   ```php
   'profesor' => [
       'title' => 'UniMind Profesor',
       'menu' => [
           // ... ítems existentes ...
           ['icon' => 'fas fa-plus', 'label' => 'Nueva Pagina', 'page' => 'nueva-pagina'],
       ],
   ],
   ```

5. Para el rol de estudiante, considera la lógica especial: si la página está en la lista `$unimindPages` (por ejemplo: 'dashboard', 'tests'), usa el menú "UniMind Estudiante"; de lo contrario, usa "AULA VIRTUAL UNJBG". Si necesitas cambiar esto, edita la variable `$unimindPages` o la lógica condicional en [sidebar-config.php](../utils/sidebar-config.php). Además, agrega un nuevo ítem al array 'menu' del bloque UniMind Estudiante.

   Ejemplo: Para incluir 'nueva-pagina' en UniMind, cambia:

   ```php
   $unimindPages = ['dashboard', 'tests', 'historial', 'formulario', 'recomendaciones', 'calendario-citas', 'nueva-pagina'];
   ```

   Y agrega al 'menu' de UniMind:

   ```php
   ['icon' => 'fas fa-new', 'label' => 'Nueva Página', 'page' => 'nueva-pagina'],
   ```

   **Explicación del sidebar diferente para estudiantes:** El rol de estudiante tiene dos contextos distintos para reflejar las funcionalidades del sistema. "UniMind Estudiante" se usa para páginas relacionadas con la evaluación y gestión de salud mental (estrés, ansiedad, etc.), mientras que "AULA VIRTUAL UNJBG" se usa para el entorno educativo general simulado (cursos, calendario académico, etc.).

6. Prueba los cambios accediendo a la página con el rol modificado. El sidebar se actualiza automáticamente al recargar.

7. Para estilos CSS, ponlos en `views/{rol}/` (por ejemplo: `views/estudiante/reportes.css`) y enlázalos en la página con `<link rel="stylesheet" href="views/estudiante/reportes.css?v=<?php echo time(); ?>">`.

8. Después de crear el CSS, aplica la metodología BEM (Block Element Modifier) para nombrar las clases y evitar conflictos. Usa las variables de colores y fuente definidas en el archivo global [theme.css](../public/css/theme.css). Puedes pedirle a Copilot que modifique los nombres de clases a BEM.

   Ejemplo de BEM:
   - Bloque: `.card`
   - Elemento: `.card__title`
   - Modificador: `.card--large`

   Variables en theme.css: `--primary-color`, `--font`, etc.

9. Para JavaScript, usa `public/js` para scripts compartidos, o crea archivos específicos en `views/{rol}/` si son únicos.

10. Usa convención de nombres: minúsculas, guiones bajos y nombres descriptivos (por ejemplo: `historial-evaluaciones.php`).

11. Usa mensajes convencionales en commits: `tipo: descripción breve`. Tipos permitidos (de [commitlint.config.js](../commitlint.config.js)): `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`, `perf`, `ci`, `build`, `revert`.

Ejemplos:

- `feat: agregar sidebar a página de reportes`
- `fix: corregir error en validación de formulario`
- `docs: actualizar guía de desarrolladores`
- `style: formatear código en sidebar-config.php`
- `refactor: simplificar lógica en SimpleRouter.php`
- `test: agregar pruebas para controlador de usuario`
- `chore: actualizar dependencias en package.json`
- `perf: optimizar consulta de base de datos`
- `ci: configurar pipeline de GitHub Actions`
- `build: actualizar script de build`
- `revert: deshacer cambios en dashboard`

12. Mantén commits pequeños y enfocados en una tarea. Evita commits masivos.

13. Ejecuta `npm run lint` y `npm run format` antes de commitear para corregir errores automáticamente.

14. Trabaja en la rama `develop`. Es importante que primero hagas pull de `develop` para evitar conflictos: `git pull origin develop`.

15. Si hay conflictos, resuélvelos manualmente y prueba el proyecto. Normalmente ahay conflictos cuando tocas el mismo archivo que otros están modificando, quizás solo ocurra si tocas el componente de alguien.

16. Cuando realices un merge (por ejemplo, al hacer `git pull`), si hay cambios para fusionar, se abrirá el editor en la terminal para aceptar el commit de merge. Escribe opcionalmente un mensaje como "merge: sincronizar con repositorio remoto", luego presiona Ctrl+C y escribe `:qa!` para salir y guardar el commit de merge.

17. No hagas push directo a `main` ni a `develop` sin revisión. Considera crear una rama feature (por ejemplo: `git checkout -b feature/nueva-pagina`) para trabajar y luego mergea a `develop` vía PR.

Para crear una PR (pull request) si no sabes qué es:

1.  Después de hacer tus commits en la rama feature, haz push: `git push origin feature/nueva-pagina` para subir tu rama al repositorio.
2.  Ve al repositorio en GitHub.
3.  Haz clic en "Compare & pull request" o "New pull request".
4.  Selecciona la rama base `develop` y la rama de comparación `feature/nueva-pagina`.
5.  Agrega un título y descripción clara (por ejemplo: "Agrega nueva página de reportes para estudiantes").
6.  Asigna reviewers si es necesario (a jorian101) y crea la PR.
7.  Espera aprobación y mergea.
8.  De ahora en adelante cada que hagas push a esta rama, se actualiza tu PR automáticamente. El administrador revisará si hay errores y comentará en tu PR para que soluciones.

9.  Ejecuta el proyecto en XAMPP y verifica que las páginas carguen sin errores.

10. No expongas datos sensibles en commits. Usa `.gitignore` para archivos locales. Depende de lo que hagas, probablemente no agreguen nada ahí aparte de lo que ya existe.

11. Coordina con el equipo para evitar sobrescribir cambios. Cualquier duda consultado con el equipo para evitar errores o conflictos y solucionar rápido

## Consideraciones adicionales

- El sidebar se renderiza dinámicamente basado en el rol y la página actual, usando `sidebar-config.php`.
- No modifiques directamente `sidebar.php` (que renderiza el HTML), ya que usa la configuración de `sidebar-config.php`. Todas las opciones del sidebar están en ese config, solo agrega ahí.
- Si agregas un submenú, asegúrate de que los subítems tengan `'page'` para navegación.
- Después de editar, verifica que no haya errores de sintaxis ejecutando el proyecto en XAMPP.
- Si la página no existe, el router muestra un mensaje de "página en desarrollo" con el archivo esperado.
- Para páginas públicas (sin rol), usa `views` directamente, pero coordina con el equipo para evitar conflictos. No creo que lo creen, ya que todo es en roles. solo login no.

Si necesitan más detalles o ejemplos específicos, comparte capturas o errores. Es importante que sigan esto y si no comprendieron algo avisen y normal se soluciona.
