<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
?>
<?php
// Construir base URL para enlaces a assets
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
echo '<link rel="stylesheet" href="' . $baseUrl . '/public/css/theme.css">';
echo '<link rel="stylesheet" href="' . $baseUrl . '/views/administrador/notificaciones.css">';
?>
<!-- Vista de Notificaciones - Administrador -->
<div class="notificaciones-container">
  
  <!-- Page Header -->
  <div class="notif-page-header">
    <div class="notif-header-content">
      <h1 class="notif-page-title">
        <i class="fa-solid fa-bell"></i>
        Notificaciones
      </h1>
      <p class="notif-page-subtitle">Panel de Control - Monitoreo de Bienestar Estudiantil</p>
    </div>
    <button class="notif-mark-all-btn">
      <i class="fa-solid fa-check-double"></i>
      Marcar todas como leídas
    </button>
  </div>

  <!-- Stats Cards -->
  <div class="notif-stats-grid">
    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-total">
        <i class="fa-solid fa-bell"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number">8</h3>
        <p class="notif-stat-label">Total</p>
      </div>
    </div>

    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-unread">
        <i class="fa-solid fa-bell-slash"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number">3</h3>
        <p class="notif-stat-label">No leídas</p>
      </div>
    </div>

    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-citas">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number">3</h3>
        <p class="notif-stat-label">Citas</p>
      </div>
    </div>

    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-alerts">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number">2</h3>
        <p class="notif-stat-label">Alertas</p>
      </div>
    </div>
  </div>

  <!-- Notifications Section -->
  <div class="notif-main-section">
    <div class="notif-section-header">
      <h2 class="notif-section-title">Lista de Notificaciones</h2>
      <p class="notif-section-desc">Mantente informado sobre actividades importantes del sistema</p>
    </div>

    <!-- Tabs -->
    <div class="notif-tabs">
      <button class="notif-tab active" data-tab="todas">
        Todas (8)
      </button>
      <button class="notif-tab" data-tab="no-leidas">
        No leídas (3)
      </button>
      <button class="notif-tab" data-tab="citas">
        Citas (3)
      </button>
      <button class="notif-tab" data-tab="alertas">
        Alertas (2)
      </button>
      <button class="notif-tab" data-tab="evaluaciones">
        Evaluaciones (2)
      </button>
    </div>

    <!-- Notifications List -->
    <div class="notif-list" id="notif-tab-content">
      
      <!-- Notification Item 1 - Nueva solicitud de cita -->
      <div class="notif-item notif-unread notif-type-cita">
        <div class="notif-icon-wrapper">
          <i class="fa-solid fa-calendar-plus"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Nueva solicitud de cita</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">María González ha solicitado una cita para el Lunes 28 Oct a las 10:00</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 5 minutos
            </span>
            <span class="notif-badge notif-badge-media">Media</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
        <div class="notif-status-indicator"></div>
      </div>

      <!-- Notification Item 2 - Estudiante con nivel alto de estrés -->
      <div class="notif-item notif-unread notif-type-alert">
        <div class="notif-icon-wrapper notif-icon-alert">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Estudiante con nivel alto de estrés</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Javier López ha registrado un nivel de estrés de 9.2/10. Requiere atención inmediata.</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 15 minutos
            </span>
            <span class="notif-badge notif-badge-alta">Alta</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
        <div class="notif-status-indicator"></div>
      </div>

      <!-- Notification Item 3 - Recordatorio de cita -->
      <div class="notif-item notif-type-cita">
        <div class="notif-icon-wrapper">
          <i class="fa-solid fa-calendar-check"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Recordatorio de cita</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Tienes una cita con Carlos Ramírez mañana a las 14:00</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 1 hora
            </span>
            <span class="notif-badge notif-badge-baja">Baja</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Item 4 - Nueva evaluación completada -->
      <div class="notif-item notif-unread notif-type-evaluacion">
        <div class="notif-icon-wrapper notif-icon-evaluacion">
          <i class="fa-solid fa-clipboard-check"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Nueva evaluación completada</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Ana Martínez ha completado la evaluación de bienestar emocional. Resultados disponibles.</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 2 horas
            </span>
            <span class="notif-badge notif-badge-media">Media</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
        <div class="notif-status-indicator"></div>
      </div>

      <!-- Notification Item 5 - Cita cancelada -->
      <div class="notif-item notif-type-cita">
        <div class="notif-icon-wrapper notif-icon-warning">
          <i class="fa-solid fa-calendar-xmark"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Cita cancelada</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Pedro Sánchez ha cancelado su cita del viernes 1 Nov a las 11:30</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 3 horas
            </span>
            <span class="notif-badge notif-badge-media">Media</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Item 6 - Nivel de ansiedad bajo control -->
      <div class="notif-item notif-type-success">
        <div class="notif-icon-wrapper notif-icon-success">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Nivel de ansiedad bajo control</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Laura Fernández muestra mejoría significativa en sus últimas 3 evaluaciones.</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 5 horas
            </span>
            <span class="notif-badge notif-badge-baja">Baja</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Item 7 - Recordatorio de seguimiento -->
      <div class="notif-item notif-type-alert">
        <div class="notif-icon-wrapper notif-icon-alert">
          <i class="fa-solid fa-bell-concierge"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Recordatorio de seguimiento</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Recordatorio: Hacer seguimiento con Roberto Torres sobre su plan de tratamiento.</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 1 día
            </span>
            <span class="notif-badge notif-badge-alta">Alta</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
      </div>

      <!-- Notification Item 8 - Evaluación pendiente de revisión -->
      <div class="notif-item notif-type-evaluacion">
        <div class="notif-icon-wrapper notif-icon-evaluacion">
          <i class="fa-solid fa-clipboard-list"></i>
        </div>
        <div class="notif-content">
          <div class="notif-header-row">
            <h3 class="notif-title">Evaluación pendiente de revisión</h3>
            <button class="notif-dismiss-btn" title="Marcar como leída">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="notif-message">Sofía Ruiz completó su evaluación de depresión hace 2 días. Pendiente de revisión.</p>
          <div class="notif-footer">
            <span class="notif-time">
              <i class="fa-regular fa-clock"></i>
              Hace 2 días
            </span>
            <span class="notif-badge notif-badge-media">Media</span>
            <button class="notif-action-btn">
              <i class="fa-regular fa-circle-check"></i>
              Marcar como leída
            </button>
          </div>
        </div>
      </div>

    </div>
  </div>

</div>

<!-- Script básico para tabs (visual solamente) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.notif-tab');
  
  tabs.forEach(tab => {
    tab.addEventListener('click', function() {
      // Remover clase active de todos los tabs
      tabs.forEach(t => t.classList.remove('active'));
      // Agregar clase active al tab clickeado
      this.classList.add('active');
    });
  });

  // Manejar botones de marcar como leída (visual)
  const actionButtons = document.querySelectorAll('.notif-action-btn');
  actionButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const notifItem = this.closest('.notif-item');
      notifItem.classList.remove('notif-unread');
      notifItem.style.opacity = '0.6';
    });
  });

  // Manejar botones de cerrar (visual)
  const dismissButtons = document.querySelectorAll('.notif-dismiss-btn');
  dismissButtons.forEach(btn => {
    btn.addEventListener('click', function(e) {
      const notifItem = this.closest('.notif-item');
      notifItem.style.transform = 'translateX(100%)';
      notifItem.style.opacity = '0';
      setTimeout(() => {
        notifItem.style.display = 'none';
      }, 300);
    });
  });

  // Marcar todas como leídas
  const markAllBtn = document.querySelector('.notif-mark-all-btn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
      const unreadItems = document.querySelectorAll('.notif-unread');
      unreadItems.forEach(item => {
        item.classList.remove('notif-unread');
        item.style.opacity = '0.6';
      });
    });
  }
});
</script>
