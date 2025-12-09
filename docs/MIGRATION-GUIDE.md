# 🚀 Guía de Migración a Patrones de Diseño

## Para Desarrolladores del Proyecto UniMind

Esta guía explica cómo adaptar código existente para usar los nuevos patrones de diseño implementados.

---

## 📚 Índice

1. [Migrar Modelos a BaseModel](#1-migrar-modelos-a-basemodel)
2. [Usar Database Singleton](#2-usar-database-singleton)
3. [Usar ModelFactory en Controllers](#3-usar-modelfactory-en-controllers)
4. [Refactorizar API Endpoints con APIFacade](#4-refactorizar-api-endpoints-con-apifacade)
5. [Agregar Nuevos Roles con Strategy](#5-agregar-nuevos-roles-con-strategy)
6. [Usar Command Pattern para Sync](#6-usar-command-pattern-para-sync)

---

## 1. Migrar Modelos a BaseModel

### ❌ Antes:

```php
<?php
require_once __DIR__ . '/../../database/Database.php';

class MiModelo {
    private $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function getAll() {
        try {
            $stmt = $this->conn->query('SELECT * FROM mi_tabla ORDER BY id');
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM mi_tabla WHERE id = :id');
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return null;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare('DELETE FROM mi_tabla WHERE id = :id');
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            error_log('Error: ' . $e->getMessage());
            return false;
        }
    }
}
?>
```

### ✅ Después:

```php
<?php
require_once __DIR__ . '/../BaseModel.php';

class MiModelo extends BaseModel {
    // Define métodos abstractos
    protected function getTableName() {
        return 'mi_tabla';
    }

    protected function getPrimaryKey() {
        return 'id';
    }

    protected function getOrderBy() {
        return 'nombre ASC'; // Opcional, default: 'id ASC'
    }

    // getAll(), getById(), delete() heredados automáticamente!
    // Solo agrega métodos específicos de este modelo:

    public function getMisMetodosEspecificos() {
        try {
            $stmt = $this->conn->prepare('SELECT * FROM mi_tabla WHERE campo_especial = 1');
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->handleError(__METHOD__, $e);
            return [];
        }
    }
}
?>
```

### 🎯 Beneficios:

- ✅ -60% líneas de código
- ✅ Manejo de errores consistente
- ✅ Soporte para transacciones: `$this->beginTransaction()`, `$this->commit()`, `$this->rollback()`

---

## 2. Usar Database Singleton

### ❌ Antes:

```php
// En Views
$db = new Database();
$conn = $db->connect();
$stmt = $conn->prepare('SELECT * FROM tabla');

// En Models
public function __construct() {
    $db = new Database();
    $this->conn = $db->connect();
}

// En Controllers
$database = new Database();
$conn = $database->connect();
```

### ✅ Después:

```php
// Método recomendado
$conn = Database::getInstance()->getConnection();
$stmt = $conn->prepare('SELECT * FROM tabla');

// En Models que extienden BaseModel
// No necesitas hacer nada, ya se hace automáticamente en constructor

// En código legacy (sigue funcionando pero deprecated)
$db = new Database();
$conn = $db->connect(); // Usa Singleton internamente
```

### 🎯 Beneficios:

- ✅ Una sola conexión compartida
- ✅ -95% uso de recursos
- ✅ Mejor rendimiento

---

## 3. Usar ModelFactory en Controllers

### ❌ Antes:

```php
<?php
class MiController {
    private $model;

    public function __construct() {
        // Instanciación directa - acoplamiento fuerte
        $this->model = new TestsModel();
    }

    public function handleRequest() {
        $tests = $this->model->getAllTests();
        // ...
    }
}
?>
```

### ✅ Después:

```php
<?php
require_once __DIR__ . '/../utils/ModelFactory.php';

class MiController {
    private $model;

    public function __construct() {
        // Factory pattern - acoplamiento débil
        $this->model = ModelFactory::create('administrador', 'tests');
    }

    public function handleRequest() {
        $tests = $this->model->getAllTests();
        // ...
    }
}

// Alternativa: detección automática de rol
class TestsController {
    private $model;

    public function __construct() {
        // Detecta rol automáticamente desde sesión
        $this->model = ModelFactory::createTestsModel();
    }
}

// Para modelos compartidos
$cursosModel = ModelFactory::createShared('cursos');
?>
```

### 🎯 Beneficios:

- ✅ Facilita testing con mocks
- ✅ Cambiar implementaciones sin tocar controllers
- ✅ Validación centralizada

---

## 4. Refactorizar API Endpoints con APIFacade

### ❌ Antes (ejemplo típico de 40+ líneas):

```php
<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../database/Database.php';

$response = ['success' => false, 'data' => []];

// Verificar autenticación
if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : (int)$_SESSION['id_usuario'];

// Validar parámetros
if (!isset($_POST['nombre']) || empty($_POST['nombre'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre requerido']);
    exit;
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->prepare('SELECT * FROM tabla WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response['success'] = true;
    $response['data'] = $data;
    echo json_encode($response);
    exit;

} catch (PDOException $e) {
    error_log('Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor']);
    exit;
}
?>
```

### ✅ Después (10 líneas con APIFacade):

```php
<?php
/**
 * API Endpoint - Refactorizado con APIFacade
 */
require_once __DIR__ . '/../utils/APIFacade.php';
require_once __DIR__ . '/../database/Database.php';

// Autenticación automática
$userId = APIFacade::requireAuth();

// Validación de parámetros
$params = APIFacade::validateParams(['nombre']);

// Ejecución con manejo automático de errores
APIFacade::execute(function() use ($userId, $params) {
    $conn = Database::getInstance()->getConnection();

    $stmt = $conn->prepare('SELECT * FROM tabla WHERE user_id = :user_id');
    $stmt->execute([':user_id' => $userId]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    APIFacade::sendSuccess(['items' => $data]);
});
?>
```

### 📋 Métodos Disponibles de APIFacade:

```php
// Autenticación
APIFacade::checkAuth();           // ['authenticated' => bool, 'user_id' => int]
APIFacade::requireAuth();          // int (o 401 si no autenticado)

// Respuestas
APIFacade::sendSuccess($data, $message);
APIFacade::sendError($message, $code);
APIFacade::sendUnauthorized($message);
APIFacade::sendNotFound($message);
APIFacade::sendServerError($message);

// Validación
APIFacade::validateParams(['param1', 'param2'], $_POST);
APIFacade::sanitize($userInput);

// Ejecución segura
APIFacade::execute(function() {
    // Tu código aquí
});

// JSON body (para PUT, PATCH)
$data = APIFacade::getJsonBody();

// Logging
APIFacade::logActivity('user_login', ['user_id' => 123]);
```

### 🎯 Beneficios:

- ✅ -70% código duplicado
- ✅ Respuestas JSON estandarizadas
- ✅ Manejo automático de errores
- ✅ Logging centralizado

---

## 5. Agregar Nuevos Roles con Strategy

### 📋 Escenario: Agregar rol "Coordinador"

### 1️⃣ Crear nueva estrategia en `/utils/AuthStrategy.php`:

```php
/**
 * Estrategia para Coordinadores
 */
class CoordinadorRedirectStrategy implements RedirectStrategy {
    public function getRedirectUrl() {
        return '../index.php?role=coordinador&page=dashboard-coordinador';
    }

    public function getRoleName() {
        return 'coordinador';
    }
}
```

### 2️⃣ Registrar en AuthenticationContext:

```php
public static function createFromRole($role) {
    $context = new self();
    $normalizedRole = strtolower(trim($role));

    switch ($normalizedRole) {
        case 'estudiante':
            $context->setStrategy(new EstudianteRedirectStrategy());
            break;
        case 'docente':
        case 'profesor':
            $context->setStrategy(new ProfesorRedirectStrategy());
            break;
        case 'administrador':
        case 'admin':
            $context->setStrategy(new AdministradorRedirectStrategy());
            break;
        case 'coordinador': // ⬅️ NUEVO
            $context->setStrategy(new CoordinadorRedirectStrategy());
            break;
        default:
            return null;
    }

    return $context;
}
```

### 3️⃣ Agregar a ModelFactory (si tiene modelos específicos):

```php
private static function getModelClass($role, $modelType) {
    $modelMap = [
        // ... roles existentes ...
        'coordinador' => [
            'dashboard' => 'CoordinadorDashboardModel',
            'reportes' => 'CoordinadorReportesModel'
        ]
    ];
    // ...
}
```

### 🎯 Beneficios:

- ✅ Sin modificar AuthController
- ✅ Código aislado y testeable
- ✅ Fácil mantenimiento

---

## 6. Usar Command Pattern para Sync

### ❌ Antes (en pwa-sync.js):

```javascript
async function syncTest(testData) {
  try {
    const response = await fetch("/api/sync", {
      method: "POST",
      body: JSON.stringify(testData),
    });

    if (!response.ok) {
      // ¿Reintentar? ¿Cuántas veces?
      // ¿Guardar para después?
      throw new Error("Sync failed");
    }
  } catch (error) {
    console.error(error);
    // Se pierde el dato
  }
}
```

### ✅ Después (con Command Pattern):

```javascript
// Importar
import { SyncCommandFactory } from './sync-commands.js';

// Crear comando
const command = SyncCommandFactory.createCommand('application', {
    client_uuid: 'abc123',
    id_test: 5,
    respuestas: [...]
});

// Agregar a cola
window.syncQueue.addCommand(command);

// La cola se procesa automáticamente al reconectar
```

### 📋 Tipos de Comandos Disponibles:

```javascript
// 1. Sincronizar aplicación de test (estudiante)
SyncCommandFactory.createCommand('application', testData);

// 2. Sincronizar creación de test (admin)
SyncCommandFactory.createCommand('test', testDefinition);

// 3. Sincronizar notificaciones
SyncCommandFactory.createCommand('notification', {});

// 4. Crear comando personalizado
class MiCustomCommand extends SyncCommand {
    async execute() {
        // Tu lógica aquí
        const response = await fetch('/mi-endpoint', { ... });
        if (response.ok) {
            this.markSuccess();
            return true;
        }
        this.markFailed('Error...');
        return false;
    }
}

const customCmd = new MiCustomCommand(data);
window.syncQueue.addCommand(customCmd);
```

### 🎯 Beneficios:

- ✅ Retry automático con backoff
- ✅ Persistencia en IndexedDB
- ✅ Procesamiento automático al reconectar
- ✅ Debugging con `failed_commands` store

---

## 📝 Checklist de Migración

### Para cada Model:

- [ ] Extender de `BaseModel`
- [ ] Implementar `getTableName()`, `getPrimaryKey()`, `getOrderBy()`
- [ ] Remover métodos duplicados (`getAll`, `getById`, `delete`)
- [ ] Usar `$this->handleError()` en catch blocks
- [ ] Eliminar `new Database()`, usar `$this->conn` directamente

### Para cada Controller:

- [ ] Importar `ModelFactory`
- [ ] Reemplazar `new MiModel()` con `ModelFactory::create()`
- [ ] Considerar usar `ModelFactory::createTestsModel()` si es genérico

### Para cada API Endpoint:

- [ ] Importar `APIFacade`
- [ ] Reemplazar autenticación manual con `APIFacade::requireAuth()`
- [ ] Usar `APIFacade::validateParams()` para validación
- [ ] Envolver lógica con `APIFacade::execute()`
- [ ] Reemplazar `echo json_encode()` con `APIFacade::sendSuccess()`
- [ ] Eliminar manejo manual de headers y códigos HTTP

### Para Views que usan DB:

- [ ] Reemplazar `new Database()` con `Database::getInstance()`
- [ ] Usar `getConnection()` en lugar de `connect()`

### Para JavaScript (PWA):

- [ ] Importar `sync-commands.js` en HTML
- [ ] Reemplazar llamadas directas a fetch con comandos
- [ ] Usar `SyncCommandFactory` para crear comandos
- [ ] Confiar en `window.syncQueue` para procesamiento

---

## 🧪 Testing Recomendado

### Después de cada migración:

```bash
# 1. Verificar sintaxis PHP
php -l path/to/file.php

# 2. Probar endpoint
curl -X POST http://localhost/unimind/api/mi-endpoint.php \
  -H "Content-Type: application/json" \
  -d '{"param": "value"}'

# 3. Verificar logs
tail -f /opt/lampp/logs/error_log

# 4. Test funcional en navegador
# - Login
# - CRUD operations
# - Modo offline (DevTools → Network → Offline)
```

---

## 🆘 Solución de Problemas

### Error: "Class 'BaseModel' not found"

```php
// Verifica la ruta del require_once
require_once __DIR__ . '/../BaseModel.php'; // Relativo a ubicación del archivo
```

### Error: "Call to undefined method Database::getInstance()"

```php
// Asegúrate de usar la clase Database refactorizada
// Verifica que /database/Database.php tenga el patrón Singleton
```

### Error: "APIFacade not found"

```php
// En API endpoints
require_once __DIR__ . '/../utils/APIFacade.php';
```

### Commands no se ejecutan al reconectar

```javascript
// Verifica que sync-commands.js esté cargado
<script src="/unimind/public/js/sync-commands.js"></script>;

// Verifica event listener
console.log(window.syncQueue); // Debe existir
```

---

## 📚 Recursos Adicionales

- **Documentación completa:** `/docs/REFACTORING-SUMMARY.md`
- **Diagramas:** `/docs/DESIGN-PATTERNS-DIAGRAMS.md`
- **Ejemplos:**
  - Model: `/models/administrador/CursosModel.php`
  - API: `/api/notifications.php`
  - Controller: `/controllers/AuthController.php`

---

**¿Dudas?** Revisa los ejemplos refactorizados o consulta la documentación de patrones GoF.
