# UniMind - Sistema de Monitoreo de Estrés

## Página de Prueba con Sidebar Responsive

Proyecto simplificado que muestra una página de prueba con sidebar completamente funcional y responsive.

---

## 🎯 Refactorización con Patrones de Diseño (Diciembre 2025)

El proyecto ha sido **refactorizado** aplicando 6 patrones de diseño fundamentales:

- ✅ **Singleton** - Database (una única conexión compartida)
- ✅ **Template Method** - BaseModel (estructura común para modelos)
- ✅ **Factory Method** - ModelFactory (creación centralizada)
- ✅ **Facade** - APIFacade (interfaz unificada para APIs)
- ✅ **Strategy** - AuthStrategy (redirección por roles)
- ✅ **Command** - SyncCommands (cola de sincronización offline)

### 📚 Documentación de Patrones:

- **[Resumen Ejecutivo](docs/REFACTORING-EXECUTIVE-SUMMARY.md)** - Overview de cambios y métricas
- **[Resumen Técnico](docs/REFACTORING-SUMMARY.md)** - Detalles de implementación
- **[Diagramas UML](docs/DESIGN-PATTERNS-DIAGRAMS.md)** - Arquitectura visual
- **[Guía de Migración](docs/MIGRATION-GUIDE.md)** - Para desarrolladores

### 🎓 Para Nuevos Desarrolladores:

1. Lee primero el **[Resumen Ejecutivo](docs/REFACTORING-EXECUTIVE-SUMMARY.md)**
2. Revisa la **[Guía de Migración](docs/MIGRATION-GUIDE.md)** antes de hacer cambios
3. Consulta ejemplos refactorizados:
   - Models: `models/administrador/CursosModel.php`
   - API: `api/notifications.php`
   - Controllers: `controllers/AuthController.php`

### 📊 Mejoras Cuantificables:

| Métrica          | Antes       | Después     | Mejora   |
| ---------------- | ----------- | ----------- | -------- |
| Conexiones DB    | ~20+        | 1           | **-95%** |
| Código duplicado | ~800 líneas | ~200 líneas | **-75%** |
| Código API       | ~600 líneas | ~200 líneas | **-67%** |

---

## Estructura Actual del Proyecto

Ver manual.md para detalles completos de la API.

```
unimind/
│
├── config/
│   ├── database.php
│   └── app.php
│
├── controllers/
│   ├── AuthController.php
│   ├── UserController.php
│   ├── TestController.php
│   ├── ReportController.php
│   └── AlertController.php
│
├── models/
│   ├── User.php
│   ├── Test.php
│   ├── Result.php
│   └── Report.php
│
├── views/
│   ├── administrador/
│   │   ├── dashboard.php
│   │   ├── usuarios.php
│   │   └── reportes.php
│   │
│   ├── profesor/
│   │   ├── alertas.php
│   │   └── estudiantes.php
│   │
│   ├── estudiante/
│   │   ├── test.php
│   │   ├── historial.php
│   │   └── progreso.php
│   │
│   ├── auth/
│   │   ├── login.php
│   │   └── registro.php
│   │
│   ├── header.php
│   ├── footer.php
│   ├── sidebar.php
├── public/
│   ├── css/
│   │   ├── style.css         # Estilos generales
│   │   └── theme.css         # Tema del sistema
│   ├── js/
│   │   └── main.js
│   └── img/
│       └── logo.png
│
├── middleware/
│   └── AuthMiddleware.php
│
├── utils/
│   ├── Validation.php
│   └── SessionHelper.php
│
├── database/
│   ├── schema.sql
│   └── seed.sql
│
├── docs/
│   ├── manual.md
│   └── api.md
│
├── index.php
└── .gitignore

```

## Configuración Inicial

### Para Usuarios de Windows

1. **Clona el Repositorio en htdocs**:

   ```cmd
   cd C:\xampp\htdocs
   git clone https://github.com/jorian101/unimind.git
   cd unimind
   npm install
   ```

2. **Inicia XAMPP**:
   - Solo necesitas Apache (PHP)
   - No necesitas MySQL

### Para Usuarios de Linux

1. **Clona el Repositorio en htdocs**:

   ```bash
   cd /opt/lampp/htdocs
   git clone https://github.com/jorian101/unimind.git
   cd unimind
   npm install
   ```

2. **Inicia XAMPP**:
   ```bash
   sudo /opt/lampp/lampp start
   ```

## Ejecutar la Aplicación

1. **Acceso Principal**:
   - Ve a: `http://localhost/unimind`
   - Verás una página replicada de AulaVirtual

2. **Funcionalidades de la Página de Prueba**:
   - Sidebar responsive completamente funcional
   - Toggle manual del sidebar con botón
   - Notificaciones de prueba
   - Diseño adaptable a móvil

## Características Actuales

### ✅ **Implementado**

- Página de prueba simple con texto "Prueba"
- Sidebar responsive con menú de ejemplo
- Header con botón toggle para sidebar
- Footer informativo
- JavaScript funcional para toggle del sidebar
- Diseño completamente responsive

### 📱 **Sidebar Features**

- Se oculta/muestra automáticamente según el tamaño de pantalla
- Overlay en móvil para mejor UX
- Menú de ejemplo con iconos FontAwesome
- Animaciones suaves
- Usuario demo en footer del sidebar

### 🎨 **Componentes Reutilizables**

- **header.php**: Header con navegación y toggle
- **footer.php**: Footer simple
- **sidebar.php**: Sidebar completamente funcional
- **test-page.php**: Página principal de prueba

Ver `docs/manual.md` para detalles técnicos de implementación.

- Si realizas cambios en archivos estáticos (HTML, CSS, JS), no necesitas reiniciar Apache; solo recarga la página.

## Flujo de Trabajo con Git

Después de ejecutar el frontend y verificar que funciona correctamente, procede a programar pero dentro de la rama `develop`:

1. **Crea la rama 'develop'**:
   - Desde la rama principal (main), crea y cambia a la rama 'develop':
     ```
     git checkout -b develop
     ```
   - Actualiza tu rama 'develop' con el repositorio remoto:
     ```
     git pull origin develop
     ```

2. **Para cada módulo o rol**:
   - Crea una rama de feature en el formato `feature/rama-modulo` (reemplaza 'rama-modulo' con el nombre del módulo o rol, ej. `feature/login-estudiante`):
     ```
     git checkout -b feature/rama-modulo
     ```
   - Realiza los cambios necesarios en la rama.
   - Una vez completados, haz commit de los cambios:
     ```
     git add .
     git commit -m "feat: descripción de los cambios"
     ```
     **Nota**: Los commits siguen el formato de Conventional Commits y dependen de Husky para su validación mediante commitlint. Lee la sección "¿Cómo deberías proceder para evitar errores?" en `docs/husky-explicacion.md`.
   - Para mantener la rama feature al día con 'develop', haz merge de 'develop' en tu rama feature:
     ```
     git checkout develop
     git pull origin develop
     git checkout feature/rama-modulo
     git merge develop
     ```
     Resuelve cualquier conflicto si surge.
   - Sube la rama al remoto:
     ```
     git push origin feature/rama-modulo
     ```
   - Crea una Pull Request (PR) desde `feature/rama-modulo` hacia `develop` en GitHub/GitLab.
   - Espera revisión y aprueba la PR para mergear a `develop`.

**Recomendación**: Repasa conceptos básicos de Git (ramas, commits, push/pull) y Pull Requests para un flujo de trabajo colaborativo eficiente. Recursos útiles: documentación oficial de Git y guías de GitHub sobre PRs.

**Observaciones**: En algunas ocasiones se trabajará en la misma rama `develop`. Las ramas `feature` son útiles cuando cada miembro del equipo hace varias interfaces por su cuenta y evitamos conflictos.

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
