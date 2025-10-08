# Desarrollo del Frontend

## Requisitos Previos

- **XAMPP**: Necesitas tener XAMPP instalado en tu sistema. XAMPP incluye Apache (servidor web), MySQL (base de datos) y PHP. Aunque el frontend es estático (HTML, CSS, JS), usar XAMPP te permite servir los archivos de manera local y facilita la integración futura con el backend PHP.
  - Descarga XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/).
  - Instala XAMPP en tu sistema operativo.

- **Editor de Código**: Recomendamos Visual Studio Code (VS Code) para editar los archivos.

## Estructura del Frontend

El frontend está organizado de la siguiente manera:

- `index.html`: Página principal.
- `assets/css/`: Hojas de estilo (style.css, theme.css).
- `assets/js/`: Scripts JavaScript (main.js).
- `assets/img/`: Imágenes.
- `components/`: Componentes reutilizables (header.html, footer.html). Se puede usar para incluir elementos comunes en las páginas.
- `pages/`: Páginas específicas para diferentes roles (administrador, estudiante, profesor). Aquí puedes crear nuevas páginas HTML.

## Cómo Crear y Editar Páginas

1. **Crear una Nueva Página**:
   - Crea un nuevo archivo HTML en la carpeta `pages/` o subcarpetas (ej. `pages/estudiante/nueva-pagina.html`).
   - Usa la estructura básica de HTML, incluyendo enlaces a CSS y JS.

2. **Editar Estilos**:
   - Modifica `assets/css/style.css` para estilos generales.
   - Modifica `assets/css/theme.css` para temas y colores.
   - **Recomendaciones para Clases CSS**: Usa nombres de clases descriptivos y específicos para evitar conflictos y mejorar la mantenibilidad. Evita selectores globales como `button` o directos a etiquetas HTML (ej. `div`); en su lugar, usa clases como `.btn-primary-action` o `.header-navigation-link`. Se recomienda adoptar la metodología BEM (Block Element Modifier) para estructurar clases, por ejemplo: `.block__element--modifier`. Si no aplicas BEM manualmente, indica a GitHub Copilot que genere nombres de clases siguiendo esta convención para asegurar consistencia y escalabilidad. Esto facilita la reutilización y reduce efectos secundarios inesperados.
   - **Diseño Responsivo**: Asegúrate de que las páginas sean responsivas para anchos de pantalla de 390px (móviles pequeños), 768px (tablets), 1024px (laptops) y 1440px (escritorios). Implementa media queries en CSS para estos breakpoints y diseña fluidamente entre ellos usando unidades relativas como `em`, `rem` o porcentajes para un escalado suave.

3. **Agregar JavaScript**:
   - Edita `assets/js/main.js` para lógica del lado cliente.
   - Si necesitas scripts específicos para páginas, puedes incluirlos directamente en el HTML o crear nuevos archivos JS.
   - **Recomendación de Herramientas**: Usa GitHub Copilot para pulir nombres de clases CSS, optimizar código JavaScript y sugerir mejoras en la estructura HTML, lo que acelera el desarrollo y asegura consistencia.

4. **Usar Componentes**:
   - Los componentes en `components/` están pensados para ser incluidos dinámicamente con JavaScript (por ejemplo, usando fetch para cargar header.html y footer.html).
   - Ejemplo simple en JS:
     ```javascript
     fetch("components/header.html")
       .then((response) => response.text())
       .then((data) => (document.getElementById("header").innerHTML = data));
     ```

## Notas Importantes

- **Acceso a Archivos**: Al clonar en `htdocs`, accede al frontend en `http://localhost/unimind/frontend/index.html`. Si hay un `index.php` en la raíz del proyecto o backend (como `backend/api/index.php`), Apache podría priorizarlo si intentas acceder a `http://localhost/unimind/`. Para evitar confusiones, accede directamente a las subcarpetas.
- **Integración con Backend**: Cuando el backend esté listo, podrás hacer llamadas AJAX desde el frontend a los endpoints PHP (ubicados en `backend/api/`). Asegúrate de que el backend también esté en `htdocs` para evitar problemas de CORS.
- **XAMPP y Backend**: El backend requiere PHP y MySQL, así que cuando lo actives, inicia también esos módulos en XAMPP.
- **Version Control**: Usa Git para controlar versiones. Haz commits frecuentes de tus cambios.

Si tienes problemas, consulta el `README.md` principal o los otros documentos en `docs/`.
