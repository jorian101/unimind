# Diagramas de Patrones de Diseño - UniMind

## 1. Singleton Pattern - Database

```
┌─────────────────────────────────────┐
│          <<Singleton>>              │
│           Database                  │
├─────────────────────────────────────┤
│ - instance: Database (static)       │
│ - conn: PDO                         │
│ - host: string                      │
│ - db_name: string                   │
│ - username: string                  │
│ - password: string                  │
├─────────────────────────────────────┤
│ - __construct()                     │
│ - __clone()                         │
│ - __wakeup()                        │
│ + getInstance(): Database (static)  │
│ + getConnection(): PDO              │
│ + connect(): PDO (deprecated)       │
└─────────────────────────────────────┘
```

## 2. Template Method Pattern - BaseModel

```
┌────────────────────────────────────────────┐
│         <<abstract>>                       │
│          BaseModel                         │
├────────────────────────────────────────────┤
│ # conn: PDO                                │
│ # table: string                            │
│ + lastError: string                        │
├────────────────────────────────────────────┤
│ + __construct()                            │
│ # initialize()                             │
│ + getAll(): array                          │ <- Template Method
│ + getById(id): array                       │ <- Template Method
│ + delete(id): bool                         │ <- Template Method
│ # handleError(method, exception)           │
│ # beginTransaction()                       │
│ # commit()                                 │
│ # rollback()                               │
│ # getTableName(): string (abstract)        │ <- Primitive Operation
│ # getPrimaryKey(): string (abstract)       │ <- Primitive Operation
│ # getOrderBy(): string                     │ <- Hook
└────────────────────────────────────────────┘
                    △
                    │
      ┌─────────────┼─────────────┬──────────────┐
      │             │             │              │
┌─────────┐  ┌──────────┐  ┌─────────┐  ┌──────────────┐
│ Cursos  │  │ Escuelas │  │  Tests  │  │    Reports   │
│  Model  │  │  Model   │  │  Model  │  │    Model     │
└─────────┘  └──────────┘  └─────────┘  └──────────────┘
```

## 3. Factory Method Pattern - ModelFactory

```
┌─────────────────────────────────────────┐
│         <<Factory>>                     │
│        ModelFactory                     │
├─────────────────────────────────────────┤
│ + create(role, type): BaseModel         │
│ + createTestsModel(): BaseModel         │
│ + createShared(type): BaseModel         │
│ - getModelClass(role, type): string     │
└─────────────────────────────────────────┘
               │
               │ creates
               ▼
     ┌─────────────────┐
     │    BaseModel    │
     └─────────────────┘
               △
               │
        ┌──────┴──────┐
        │             │
  ┌──────────┐  ┌──────────┐
  │ TestsModel│ │CursosModel│
  └──────────┘  └──────────┘

Mapeo de Creación:
administrador + tests    → TestsModel
administrador + cursos   → CursosModel
profesor + dashboard     → DashboardModel
estudiante + tests       → TestsEstudianteModel
```

## 4. Facade Pattern - APIFacade

```
┌──────────────────────────────────────────┐
│          <<Facade>>                      │
│          APIFacade                       │
├──────────────────────────────────────────┤
│ + checkAuth(): array                     │
│ + requireAuth(): int                     │
│ + sendSuccess(data, message)             │
│ + sendError(message, code)               │
│ + sendUnauthorized(message)              │
│ + sendNotFound(message)                  │
│ + sendServerError(message)               │
│ + validateParams(params, source): array  │
│ + execute(callback): mixed               │
│ + getJsonBody(): array                   │
│ + sanitize(value): mixed                 │
│ + logActivity(action, details)           │
│ - sendJSON(data, code)                   │
└──────────────────────────────────────────┘
               │
               │ simplifies
               ▼
    ┌──────────────────────┐
    │  Complex Subsystems: │
    │  - Session           │
    │  - JSON encoding     │
    │  - HTTP headers      │
    │  - Error handling    │
    │  - Logging           │
    └──────────────────────┘
```

## 5. Strategy Pattern - AuthStrategy

```
┌────────────────────────────┐
│    <<interface>>           │
│   RedirectStrategy         │
├────────────────────────────┤
│ + getRedirectUrl(): string │
│ + getRoleName(): string    │
└────────────────────────────┘
           △
           │ implements
     ┌─────┴──────┬──────────────┐
     │            │              │
┌────────────┐ ┌───────────┐ ┌──────────────┐
│ Estudiante │ │  Profesor │ │Administrador │
│  Redirect  │ │  Redirect │ │   Redirect   │
│  Strategy  │ │  Strategy │ │   Strategy   │
└────────────┘ └───────────┘ └──────────────┘

┌─────────────────────────────────┐
│  AuthenticationContext          │
├─────────────────────────────────┤
│ - strategy: RedirectStrategy    │
├─────────────────────────────────┤
│ + createFromRole(role): Context │
│ + setStrategy(strategy)         │
│ + redirect()                    │
│ + getRoleName(): string         │
└─────────────────────────────────┘
         │ uses
         ▼
┌─────────────────────────────────┐
│      AuthHelper                 │
├─────────────────────────────────┤
│ + setupSession(usuario)         │
│ + validateCredentials()         │
│ + clearSession()                │
└─────────────────────────────────┘
```

## 6. Command Pattern - SyncCommands

```
┌─────────────────────────────────┐
│      <<abstract>>               │
│       SyncCommand               │
├─────────────────────────────────┤
│ + data: object                  │
│ + status: string                │
│ + attempts: number              │
│ + maxAttempts: number           │
│ + lastError: string             │
├─────────────────────────────────┤
│ + execute(): Promise<bool>      │
│ + retry(): Promise<bool>        │
│ + canRetry(): bool              │
│ + markSuccess()                 │
│ + markFailed(error)             │
└─────────────────────────────────┘
               △
               │ extends
     ┌─────────┼──────────┬────────────────┐
     │         │          │                │
┌─────────┐ ┌──────┐ ┌────────────┐ ┌─────────────┐
│  Sync   │ │ Sync │ │   Sync     │ │    Sync     │
│ Applic  │ │ Test │ │Notification│ │   Custom    │
│ Command │ │ Cmd  │ │  Command   │ │   Command   │
└─────────┘ └──────┘ └────────────┘ └─────────────┘

┌─────────────────────────────────────┐
│      SyncCommandQueue               │
│        (Invoker)                    │
├─────────────────────────────────────┤
│ - queue: Array<SyncCommand>         │
│ - isProcessing: bool                │
├─────────────────────────────────────┤
│ + addCommand(command)               │
│ + processQueue(): Promise<void>     │
│ + saveFailedCommand(command)        │
│ + getQueueSize(): number            │
│ + clearQueue()                      │
└─────────────────────────────────────┘

┌─────────────────────────────────────┐
│    SyncCommandFactory               │
├─────────────────────────────────────┤
│ + createCommand(type, data): Cmd    │
└─────────────────────────────────────┘
```

## Flujo de Ejecución Completo

### Autenticación con Strategy Pattern

```
  Usuario
    │
    ▼
┌─────────────────┐
│ AuthController  │
└─────────────────┘
    │
    ├─→ AuthHelper.validateCredentials()
    │       │
    │       ▼
    │   Database.getInstance()
    │       │
    │       ▼
    │   Stored Procedure
    │
    ├─→ AuthHelper.setupSession()
    │
    └─→ AuthenticationContext.createFromRole()
            │
            ├─→ EstudianteRedirectStrategy
            ├─→ ProfesorRedirectStrategy
            └─→ AdministradorRedirectStrategy
                    │
                    ▼
                redirect()
```

### API Request con Facade Pattern

```
  Cliente
    │
    ▼
┌────────────────┐
│ API Endpoint   │
└────────────────┘
    │
    └─→ APIFacade.requireAuth()
            │
            ├─→ checkAuth()
            │
            └─→ Database.getInstance()
                    │
                    ▼
               Query Data
                    │
                    ▼
            APIFacade.sendSuccess()
```

### CRUD Operation con Factory + Template Method

```
  Controller
      │
      ▼
  ModelFactory.create('administrador', 'tests')
      │
      ├─→ Resolve: TestsModel
      │
      └─→ new TestsModel()
              │
              ├─→ extends BaseModel
              │       │
              │       └─→ Database.getInstance()
              │
              └─→ getAll() [Template Method]
                      │
                      ├─→ getTableName()
                      ├─→ getPrimaryKey()
                      └─→ getOrderBy()
```

### Offline Sync con Command Pattern

```
  User Action (offline)
      │
      ▼
  SyncCommandFactory.createCommand('application', data)
      │
      ▼
  SyncApplicationCommand
      │
      ▼
  syncQueue.addCommand(command)
      │
      ▼
  [Guardado en IndexedDB]
      │
      │ Connection restored
      ▼
  syncQueue.processQueue()
      │
      ├─→ command.execute()
      │       │
      │       ├─→ fetch(SyncController)
      │       │
      │       └─→ success? markSuccess() : retry()
      │
      └─→ Next command...
```

## Relaciones entre Patrones

```
Database (Singleton)
    │
    ├─→ Used by: BaseModel (Template Method)
    │       │
    │       └─→ Extended by: All Models
    │               │
    │               └─→ Created by: ModelFactory (Factory Method)
    │
    ├─→ Used by: AuthHelper (Strategy)
    │       │
    │       └─→ Used by: AuthenticationContext
    │
    └─→ Used by: APIFacade (Facade)
            │
            └─→ Simplifies: API Endpoints
                    │
                    └─→ Uses: ModelFactory
```

## Patrones No Implementados (Futuros)

### Observer Pattern (para notificaciones real-time)

```
┌──────────────┐
│   Subject    │
│ (Observable) │
└──────────────┘
       △
       │
┌──────────────────┐
│NotificationManager│
└──────────────────┘
       │ notifies
       ▼
┌──────────────┐
│   Observer   │
└──────────────┘
       △
       │
  ┌────┴────┐
  │         │
┌────┐  ┌──────┐
│ UI │  │Toast │
└────┘  └──────┘
```

### Adapter Pattern (para IndexedDB/MySQL)

```
┌─────────────────┐
│  Target         │
│  DataSource     │
└─────────────────┘
       △
       │
┌─────────────────────┐
│     Adapter         │
│ IndexedDBAdapter    │
└─────────────────────┘
       │
       │ adapts
       ▼
┌─────────────────┐
│   Adaptee       │
│   IndexedDB     │
└─────────────────┘
```

---

**Nota:** Estos diagramas usan notación UML simplificada con ASCII art.
Para diagramas profesionales, usar herramientas como PlantUML o draw.io.
