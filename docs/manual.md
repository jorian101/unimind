# **Estructura del Proyecto**

**Proyecto:** Sistema de Monitoreo de Estrés UniMind
**Directorio principal:** `appweb-control-ansiedad/`

```
appweb-control-ansiedad/
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

---

## **Descripción de la estructura**

### **backend/**

Contiene toda la lógica del servidor desarrollada en **PHP puro**, siguiendo el patrón **MVC (Modelo - Vista - Controlador)**.

- **config/**
  Archivos esenciales de configuración del sistema.
  - `database.php`: Define la conexión a la base de datos mediante PDO (host, usuario, contraseña, nombre BD).
  - `cors.php`: Permite peticiones desde el frontend (CORS).

- **controllers/**
  Controladores que procesan las solicitudes HTTP, comunican los modelos y retornan respuestas JSON o vistas renderizadas.
  Ejemplo: `UserController.php` para gestionar usuarios o `TestController.php` para registrar tests.

- **models/**
  Contiene las clases que representan entidades de la base de datos (usuarios, tests, resultados, reportes).
  Ejemplo: `Result.php` podría tener un método `getByUser()` para recuperar los resultados de un estudiante.

- **views/**
  Carpeta destinada a **plantillas y vistas del backend**.
  Aunque el sistema usa un **frontend independiente**, esta carpeta puede servir para:
  - Prototipos o vistas generadas directamente por PHP.
  - Plantillas comunes (`header.php`, `footer.php`) o mensajes de error.
  - Renderizados simples cuando el backend se usa de forma autónoma (por ejemplo, pruebas, logs o plantillas de correo).

  Estructura interna sugerida:
  - `templates/`: contiene las vistas principales o layout base.
  - `partials/`: fragmentos reutilizables (mensajes, alertas, etc.).

- **api/**
  Punto de entrada principal de la API.
  - `index.php`: Router principal que recibe todas las peticiones y carga las rutas definidas.
  - `routes/`: Define las rutas REST (`auth.php`, `users.php`, etc.), cada una enlaza con su controlador.

- **middleware/**
  Capas intermedias como la verificación de tokens o validaciones de acceso.
  Ejemplo: `AuthMiddleware.php` asegura que solo usuarios autenticados accedan a determinadas rutas.

- **utils/**
  Funciones auxiliares y helpers.
  Ejemplo:
  - `JwtHelper.php`: para generar y verificar tokens JWT.
  - `Validation.php`: validaciones básicas de formularios y datos.

---

### **frontend/**

Contiene la interfaz del sistema web, organizada para adaptarse a los **tres roles principales**: administrador, profesor y estudiante.

- **assets/**
  Archivos estáticos reutilizables.
  - `css/`: estilos globales del sistema (`style.css`) y variables de tema (`theme.css`).
  - `js/`: scripts generales (`main.js`).
  - `img/`: recursos gráficos (logos, íconos, banners).

- **components/**
  Contiene componentes visuales **comunes a todas las páginas**, como:
  - `header.html`: barra superior del sistema.
  - `footer.html`: pie de página con información institucional.
    _(Nota: estos son archivos fijos y obligatorios)._

- **pages/**
  Carpeta principal de vistas del frontend, separadas por **rol de usuario**:
  - `administrador/`: gestión de usuarios, reportes.
  - `estudiante/`: test, historial, monitoreo personal.
  - `profesor/`: visualización de alertas o niveles de estrés.

  Ejemplos de páginas posibles:
  - `dashboard.html`
  - `perfil.html`
  - `test.html`

- **index.html**
  Página inicial (punto de entrada).
  - Es **obligatoria**.
  - Puede redirigir al login o dashboard según sesión.

---

### **database/**

Incluye los scripts SQL de creación e inicialización de la base de datos.

- `schema.sql`: estructura de tablas (`usuarios`, `tests`, `resultados`, `reportes`).
- `seed.sql`: carga de datos iniciales (roles, usuarios de prueba, tipos de test).

---

### **docs/**

Documentación técnica y funcional.

- `api.md`: endpoints de la API, parámetros y ejemplos.
- `manual.md`: descripción de la arquitectura, estructura y despliegue.

---

### **.gitignore**

Define los archivos y carpetas excluidos del control de versiones (por ejemplo: `/vendor/`, `.env`, `/node_modules/`, logs, builds).

---

## **Notas generales**

- La separación **backend / frontend** facilita el mantenimiento y escalabilidad.
- Las carpetas `views/`, `controllers/` y `models/` constituyen el núcleo del patrón **MVC**.
- Los únicos archivos **obligatorios** del frontend son:
  - `index.html`
  - `components/header.html`
  - `components/footer.html`

- Las carpetas de roles (`administrador/`, `profesor/`, `estudiante/`) son fijas.
- Las rutas del backend deben corresponder a los módulos definidos (`usuarios`, `tests`, `reportes`, `alertas`).

---

## **Archivos que deben crearse por el momento**

Como el proyecto está en etapa inicial y aún **no se definen completamente los controladores, modelos ni rutas**, se recomienda crear solo los archivos base para comenzar con la configuración y pruebas.

**🔧 En `backend/`:**

- `config/database.php`
- `config/cors.php`
- `api/index.php`
- `middleware/AuthMiddleware.php`
- `utils/JwtHelper.php`
- `utils/Validation.php`

_(Las carpetas `controllers/`, `models/`, `views/` y `api/routes/` pueden dejarse vacías inicialmente —usa `.gitkeep` para conservarlas en GitHub)._

**🎨 En `frontend/`:**

- `index.html`
- `components/header.html`
- `components/footer.html`
- `assets/css/style.css`
- `assets/css/theme.css`
- `assets/js/main.js`
- Carpetas vacías para `pages/administrador/`, `pages/estudiante/` y `pages/profesor/`.

**🗃️ En `database/`:**

- `schema.sql` (tablas básicas)
- `seed.sql` (datos iniciales mínimos)

**📘 En `docs/`:**

- `manual.md` (documentación del proyecto y estructura)
- `api.md` (plantilla inicial de endpoints)

**📄 En raíz:**

- `.gitignore` (para excluir `/vendor/`, `.env`, etc.)
