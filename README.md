# Guía de Instalación y Uso

## Instalación

1. Clona el repositorio:

   ```
   git clone https://github.com/jorian101/appweb-control-ansiedad.git
   cd appweb-control-ansiedad
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

## Uso

- Accede al frontend en `http://localhost/frontend/`
- Usa las credenciales de prueba:
  - Estudiante: estudiante@example.com / 123456
  - Admin: admin@example.com / admin123

## API Endpoints

Ver `docs/api.md` para detalles completos de la API.

# Estructura del Proyecto

Ver `docs/muak.md` para detalles completos de la estructura.

stress-monitoring-system/
│
├── backend/ # Backend PHP puro
│ ├── config/ # Configuración de BD, CORS, etc.
│ │ ├── database.php
│ │ └── cors.php
│ │
│ ├── controllers/ # Controladores MVC
│ │ ├── AuthController.php
│ │ ├── UserController.php
│ │ ├── TestController.php
│ │ ├── ReportController.php
│ │ └── AlertController.php
│ │
│ ├── models/ # Modelos (ORM manual)
│ │ ├── User.php
│ │ ├── Test.php
│ │ ├── Result.php
│ │ └── Report.php
│ │
│ ├── views/ # Vistas (si usas PHP como template)
│ │ ├── dashboard.php
│ │ ├── login.php
│ │ └── admin/
│ │
│ ├── api/ # API REST para app móvil y frontend
│ │ ├── index.php # Router API
│ │ ├── routes/
│ │ │ ├── auth.php
│ │ │ ├── tests.php
│ │ │ ├── users.php
│ │ │ └── reports.php
│ │
│ ├── middleware/ # Autenticación, validaciones
│ │ └── AuthMiddleware.php
│ │
│ └── utils/ # Helpers, validaciones, JWT, etc.
│ ├── JwtHelper.php
│ └── Validation.php
│
├── frontend/ # Frontend vanilla (HTML, CSS, JS)
│ ├── assets/
│ │ ├── css/
│ │ ├── js/
│ │ └── img/
│ ├── pages/
│ │ ├── login.html
│ │ ├── student-dashboard.html
│ │ ├── admin-dashboard.html
│ │ └── test.html
│ └── index.html
│
├── mobile/ # App con Ionic
│ ├── src/
│ │ ├── app/
│ │ │ ├── services/
│ │ │ ├── pages/
│ │ │ └── guards/
│ │ └── environments/
│ ├── android/ # Generado por Capacitor
│ ├── ios/ # Generado por Capacitor
│ └── www/ # Build web de Ionic
│
├── database/ # Scripts SQL
│ ├── schema.sql
│ └── seed.sql
│
├── docs/ # Documentación del proyecto
│ ├── api.md
│ └── manual.md
│
└── .gitignore
