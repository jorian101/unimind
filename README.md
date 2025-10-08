# Guía de Instalación y Uso

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
   - Dentro de la carpeta `unimind` realiza:
     ```
     npm i
     ```

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

## Ejecutar el Frontend

1. **Accede al Frontend**:
   - Abre tu navegador web.
     - Ve a `http://localhost/unimind/frontend/index.html`.
   - Deberías ver la página inicial del sistema de monitoreo de estrés.

2. **Desarrollo y Pruebas**:
   - Edita los archivos en tu editor de código (desde la carpeta clonada en `htdocs/unimind`).
   - Para ver los cambios, recarga la página en el navegador.
   - Si realizas cambios en archivos estáticos (HTML, CSS, JS), no necesitas reiniciar Apache; solo recarga la página.

### Siguientes pasos

1. Configura la base de datos:
   - Crea una base de datos MySQL llamada `stress_monitoring`
   - Ejecuta el script `database/schema.sql`
   - Ejecuta el script `database/seed.sql` para datos de prueba

2. Para el frontend:
   - Abre `frontend/index.html` en un navegador web
   - O sirve la carpeta `frontend` con un servidor web
   - Leer `docs/desarrollo-frontend.md` y `docs/manual.md` para comprender la estructura del frontend

3. Configura el backend:
   - Edita `backend/config/database.php` con tus credenciales de BD
   - Asegúrate de que el servidor web tenga acceso a la carpeta `backend`

## API Endpoints

Ver `docs/api.md` para detalles completos de la API.

# Estructura del Proyecto

Ver `docs/manual.md` para detalles completos de la estructura.

```
unimind/
│
├── backend/
│   ├── config/
│   │   ├── database.php
│   │   └── cors.php
│   │
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── UserController.php
│   │   ├── TestController.php
│   │   ├── ReportController.php
│   │   └── AlertController.php
│   │
│   ├── models/
│   │   ├── User.php
│   │   ├── Test.php
│   │   ├── Result.php
│   │   └── Report.php
│   │
│   ├── views/
│   │   ├── templates/
│   │   │   ├── header.php
│   │   │   ├── footer.php
│   │   │   └── layout.php
│   │   └── partials/
│   │       └── messages.php
│   │
│   ├── api/
│   │   ├── index.php
│   │   └── routes/
│   │       ├── auth.php
│   │       ├── users.php
│   │       ├── tests.php
│   │       └── reports.php
│   │
│   ├── middleware/
│   │   └── AuthMiddleware.php
│   │
│   └── utils/
│       ├── JwtHelper.php
│       └── Validation.php
│
├── frontend/
│   ├── assets/
│   │   ├── css/
│   │   │   ├── style.css
│   │   │   └── theme.css
│   │   ├── js/
│   │   │   └── main.js
│   │   └── img/
│   │
│   ├── components/
│   │   ├── header.html
│   │   └── footer.html
│   │
│   ├── pages/
│   │   ├── administrador/
│   │   ├── estudiante/
│   │   └── profesor/
│   │
│   └── index.html
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── docs/
│   ├── api.md
│   └── manual.md
│
└── .gitignore
```
