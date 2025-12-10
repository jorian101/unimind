/**
 * Command Pattern para PWA Sync Queue
 *
 * Encapsula operaciones de sincronización como comandos ejecutables
 * Facilita retry, undo y logging de operaciones offline
 */

/**
 * Interfaz para comandos de sincronización
 */
class SyncCommand {
  constructor(data) {
    this.data = data;
    this.status = "pending";
    this.attempts = 0;
    this.maxAttempts = 3;
    this.lastError = null;
  }

  async execute() {
    throw new Error("Execute method must be implemented");
  }

  async retry() {
    this.attempts++;
    if (this.attempts >= this.maxAttempts) {
      this.status = "failed";
      return false;
    }
    return await this.execute();
  }

  canRetry() {
    return this.attempts < this.maxAttempts;
  }

  markSuccess() {
    this.status = "success";
  }

  markFailed(error) {
    this.status = "failed";
    this.lastError = error;
  }
}

/**
 * Comando para sincronizar aplicación de test (estudiante)
 */
class SyncApplicationCommand extends SyncCommand {
  async execute() {
    try {
      const response = await fetch("/unimind/controllers/SyncController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "syncApplication",
          data: this.data,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();

      if (result.success) {
        this.markSuccess();
        return true;
      } else {
        throw new Error(result.message || "Sync failed");
      }
    } catch (error) {
      this.markFailed(error.message);
      console.error("SyncApplicationCommand error:", error);
      return false;
    }
  }
}

/**
 * Comando para sincronizar creación de test (admin)
 */
class SyncTestCreationCommand extends SyncCommand {
  async execute() {
    try {
      const response = await fetch("/unimind/controllers/TestsController.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "create",
          ...this.data,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();

      if (result.success) {
        this.markSuccess();
        return true;
      } else {
        throw new Error(result.message || "Test creation failed");
      }
    } catch (error) {
      this.markFailed(error.message);
      console.error("SyncTestCreationCommand error:", error);
      return false;
    }
  }
}

/**
 * Comando para sincronizar notificaciones
 */
class SyncNotificationCommand extends SyncCommand {
  async execute() {
    try {
      const response = await fetch("/unimind/api/notifications.php", {
        method: "GET",
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
      }

      const result = await response.json();

      if (result.success) {
        this.markSuccess();
        // Actualizar UI con notificaciones
        if (window.updateNotifications) {
          window.updateNotifications(result.data.notifications);
        }
        return true;
      } else {
        throw new Error(result.message || "Notification sync failed");
      }
    } catch (error) {
      this.markFailed(error.message);
      console.error("SyncNotificationCommand error:", error);
      return false;
    }
  }
}

/**
 * Invoker: Gestor de cola de comandos
 */
class SyncCommandQueue {
  constructor() {
    this.queue = [];
    this.isProcessing = false;
  }

  addCommand(command) {
    this.queue.push(command);
    console.log(`Command added to queue. Queue size: ${this.queue.length}`);
  }

  async processQueue() {
    if (this.isProcessing || this.queue.length === 0) {
      return;
    }

    this.isProcessing = true;
    console.log(`Processing ${this.queue.length} commands...`);

    while (this.queue.length > 0) {
      const command = this.queue[0];

      const success = await command.execute();

      if (success) {
        // Remover comando exitoso
        this.queue.shift();
        console.log("Command executed successfully");
      } else if (command.canRetry()) {
        // Mover al final de la cola para retry
        this.queue.push(this.queue.shift());
        console.log("Command failed, will retry");
      } else {
        // Comando falló definitivamente
        this.queue.shift();
        console.error("Command failed permanently:", command.lastError);

        // Opcional: guardar en failed_queue para inspección
        this.saveFailedCommand(command);
      }

      // Pequeño delay entre comandos
      await new Promise((resolve) => setTimeout(resolve, 100));
    }

    this.isProcessing = false;
    console.log("Queue processing complete");
  }

  async saveFailedCommand(command) {
    // Guardar comandos fallidos en IndexedDB para debugging
    if (window.IDBWrapper) {
      try {
        await window.IDBWrapper.add("failed_commands", {
          timestamp: new Date().toISOString(),
          data: command.data,
          error: command.lastError,
          attempts: command.attempts,
        });
      } catch (e) {
        console.error("Failed to save failed command:", e);
      }
    }
  }

  getQueueSize() {
    return this.queue.length;
  }

  clearQueue() {
    this.queue = [];
  }
}

/**
 * Factory para crear comandos según tipo
 */
class SyncCommandFactory {
  static createCommand(type, data) {
    switch (type) {
      case "application":
        return new SyncApplicationCommand(data);
      case "test":
        return new SyncTestCreationCommand(data);
      case "notification":
        return new SyncNotificationCommand(data);
      default:
        throw new Error(`Unknown command type: ${type}`);
    }
  }
}

// Instancia global de la cola
window.syncQueue = new SyncCommandQueue();

// Event listener para procesar cola cuando vuelve la conexión
window.addEventListener("online", () => {
  console.log("Connection restored, processing sync queue...");
  window.syncQueue.processQueue();
});

// Exportar para uso en otros módulos
if (typeof module !== "undefined" && module.exports) {
  module.exports = {
    SyncCommand,
    SyncApplicationCommand,
    SyncTestCreationCommand,
    SyncNotificationCommand,
    SyncCommandQueue,
    SyncCommandFactory,
  };
}
