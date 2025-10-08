# Desarrollo del Frontend

## Requisitos Previos

- **XAMPP**: Necesitas tener XAMPP instalado en tu sistema. XAMPP incluye Apache (servidor web), MySQL (base de datos) y PHP. Aunque el frontend es estático (HTML, CSS, JS), usar XAMPP te permite servir los archivos de manera local y facilita la integración futura con el backend PHP.
  - Descarga XAMPP desde [https://www.apachefriends.org/](https://www.apachefriends.org/).
  - Instala XAMPP en tu sistema operativo.

- **Editor de Código**: Recomendamos Visual Studio Code (VS Code) para editar los archivos.

## Configuración Inicial

### Para Usuarios de Windows

1. **Clona el Repositorio en htdocs**:
   - Abre la carpeta `htdocs` de XAMPP (generalmente `C:\xampp\htdocs\`).
   - Abre la línea de comandos (CMD o PowerShell) y navega a esa carpeta:
     ```
     cd C:\xampp\htdocs
     ```
   - Clona el repositorio:
     ```
     git clone https://github.com/jorian101/unimind.git
     ```
     Esto creará la carpeta `unimind` dentro de `htdocs`.

2. **Inicia XAMPP**:
   - Abre la aplicación XAMPP desde el menú Inicio.
   - Inicia el módulo Apache (no necesitas MySQL ni PHP por ahora, solo para el frontend).

### Para Usuarios de Linux

1. **Clona el Repositorio en htdocs**:
   - Abre la carpeta `htdocs` de XAMPP (generalmente `/opt/lampp/htdocs/`).
   - Abre la terminal y navega a esa carpeta:
     ```
     cd /opt/lampp/htdocs
     ```
   - Clona el repositorio:
     ```
     git clone https://github.com/jorian101/unimind.git
     ```
     Esto creará la carpeta `unimind` dentro de `htdocs`.

2. **Inicia XAMPP**:
   - Abre la aplicación XAMPP (puedes usar `sudo /opt/lampp/lampp start`).
   - Inicia el módulo Apache (no necesitas MySQL ni PHP por ahora, solo para el frontend).

## Ejecutar el Frontend

1. **Accede al Frontend**:
   - Abre tu navegador web.
     - Ve a `http://localhost/unimind/frontend/index.html`.
   - Deberías ver la página inicial del sistema de monitoreo de estrés.

2. **Desarrollo y Pruebas**:
   - Edita los archivos en tu editor de código (desde la carpeta clonada en `htdocs/unimind`).
   - Para ver los cambios, recarga la página en el navegador.
   - Si realizas cambios en archivos estáticos (HTML, CSS, JS), no necesitas reiniciar Apache; solo recarga la página.

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

3. **Agregar JavaScript**:
   - Edita `assets/js/main.js` para lógica del lado cliente.
   - Si necesitas scripts específicos para páginas, puedes incluirlos directamente en el HTML o crear nuevos archivos JS.

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
