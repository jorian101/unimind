# Guía de Instalación y Uso

## Instalación (aun no oficial)

1. Clona el repositorio:

   ```
   git clone https://github.com/jorian101/unimind.git
   cd unimind
   ```

2. Configura la base de datos:
   - Crea una base de datos MySQL llamada `stress_monitoring`
   - Ejecuta el script `database/schema.sql`
   - Ejecuta el script `database/seed.sql` para datos de prueba

3. Configura el backend:
   - Edita `backend/config/database.php` con tus credenciales de BD
   - Asegúrate de que el servidor web tenga acceso a la carpeta `backend`

4. Para el frontend:
   - Abre `frontend/index.html` en un navegador web
   - O sirve la carpeta `frontend` con un servidor web

## API Endpoints

Ver `docs/api.md` para detalles completos de la API.

# Estructura del Proyecto

Ver `docs/manuak.md` para detalles completos de la estructura.

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
