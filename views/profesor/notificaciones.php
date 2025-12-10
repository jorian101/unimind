<?php
require_once dirname(__DIR__) . '/pageHeader.php';
renderPageHeader();
$baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
echo '<link rel="stylesheet" href="' . $baseUrl . '/public/css/theme.css">';
echo '<link rel="stylesheet" href="' . $baseUrl . '/views/administrador/notificaciones.css">';
require_once dirname(__DIR__, 2) . '/database/Database.php';
$db = Database::getInstance()->getConnection();
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
$id_usuario = isset($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : null;
$notificaciones = [];
if ($id_usuario) {
  $stmt = $db->prepare('SELECT * FROM Notificaciones WHERE id_usuario = ? ORDER BY fecha_creacion DESC');
  $stmt->execute([$id_usuario]);
  $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$total = count($notificaciones);
$no_leidas = 0;
$citas = 0;
$alertas = 0;
$evaluaciones = 0;
foreach ($notificaciones as $n) {
  if ($n['estado'] === 'nueva') $no_leidas++;
  if (stripos($n['tipo'], 'cita') !== false) $citas++;
  if (stripos($n['tipo'], 'alerta') !== false || stripos($n['tipo'], 'warning') !== false) $alertas++;
  if (stripos($n['tipo'], 'evaluacion') !== false) $evaluaciones++;
}
?>
<div class="notificaciones-container">
  <div class="notif-page-header">
    <div class="notif-header-content">
      <h1 class="notif-page-title">Notificaciones</h1>
      <p class="notif-page-subtitle">Panel de Control - Profesor</p>
    </div>
    <button class="notif-mark-all-btn">
      <i class="fa-solid fa-check-double"></i>
      Marcar todas como leídas
    </button>
  </div>
  <div class="notif-stats-grid">
    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-total">
        <i class="fa-solid fa-list"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number"><?php echo $total; ?></h3>
        <p class="notif-stat-label">Total</p>
      </div>
    </div>
    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-unread">
        <i class="fa-solid fa-envelope"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number"><?php echo $no_leidas; ?></h3>
        <p class="notif-stat-label">No leídas</p>
      </div>
    </div>
    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-citas">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number"><?php echo $citas; ?></h3>
        <p class="notif-stat-label">Citas</p>
      </div>
    </div>
    <div class="notif-stat-card">
      <div class="notif-stat-icon notif-stat-alerts">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>
      <div class="notif-stat-info">
        <h3 class="notif-stat-number"><?php echo $alertas; ?></h3>
        <p class="notif-stat-label">Alertas</p>
      </div>
    </div>
  </div>
  <div class="notif-main-section">
    <div class="notif-section-header">
      <h2 class="notif-section-title">Lista de Notificaciones</h2>
      <p class="notif-section-desc">Mantente informado sobre actividades importantes del sistema</p>
    </div>
    <div class="notif-tabs">
      <button class="notif-tab active" data-tab="todas">Todas (<?php echo $total; ?>)</button>
      <button class="notif-tab" data-tab="no-leidas">No leídas (<?php echo $no_leidas; ?>)</button>
      <button class="notif-tab" data-tab="citas">Citas (<?php echo $citas; ?>)</button>
      <button class="notif-tab" data-tab="alertas">Alertas (<?php echo $alertas; ?>)</button>
      <button class="notif-tab" data-tab="evaluaciones">Evaluaciones (<?php echo $evaluaciones; ?>)</button>
    </div>
    <div class="notif-list" id="notif-tab-content">
      <?php if (empty($notificaciones)): ?>
        <div class="notif-item">
          <div class="notif-content">
            <h3 class="notif-title">No hay notificaciones</h3>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($notificaciones as $n):
          $itemClass = 'notif-item';
          if ($n['estado'] === 'nueva') $itemClass .= ' notif-unread';
          if (stripos($n['tipo'], 'cita') !== false) $itemClass .= ' notif-type-cita';
          elseif (stripos($n['tipo'], 'alerta') !== false || stripos($n['tipo'], 'warning') !== false) $itemClass .= ' notif-type-alert';
          elseif (stripos($n['tipo'], 'evaluacion') !== false) $itemClass .= ' notif-type-evaluacion';
          elseif (stripos($n['tipo'], 'success') !== false) $itemClass .= ' notif-type-success';
          $icon = 'fa-solid fa-list';
          if (stripos($n['tipo'], 'cita') !== false && stripos(strtolower($n['titulo']), 'cancelada') !== false) $icon = 'fa-solid fa-calendar-xmark';
          elseif (stripos($n['tipo'], 'cita') !== false && stripos(strtolower($n['titulo']), 'solicitud') !== false) $icon = 'fa-solid fa-calendar-plus';
          elseif (stripos($n['tipo'], 'cita') !== false) $icon = 'fa-solid fa-calendar-check';
          elseif (stripos($n['tipo'], 'alerta') !== false || stripos($n['tipo'], 'warning') !== false) $icon = 'fa-solid fa-triangle-exclamation';
          elseif (stripos($n['tipo'], 'evaluacion') !== false && stripos(strtolower($n['titulo']), 'pendiente') !== false) $icon = 'fa-solid fa-clipboard-list';
          elseif (stripos($n['tipo'], 'evaluacion') !== false) $icon = 'fa-solid fa-clipboard-check';
          elseif (stripos($n['tipo'], 'success') !== false) $icon = 'fa-solid fa-circle-check';
          elseif (stripos($n['tipo'], 'info') !== false) $icon = 'fa-solid fa-envelope';
          elseif (stripos($n['tipo'], 'flag') !== false) $icon = 'fa-solid fa-flag';
          $badge = '';
          if (stripos($n['tipo'], 'alta') !== false || stripos($n['tipo'], 'alerta') !== false) $badge = '<span class="notif-badge notif-badge-alta">Alta</span>';
          elseif (stripos($n['tipo'], 'media') !== false) $badge = '<span class="notif-badge notif-badge-media">Media</span>';
          elseif (stripos($n['tipo'], 'baja') !== false) $badge = '<span class="notif-badge notif-badge-baja">Baja</span>';
          $fecha = new DateTime($n['fecha_creacion']);
          $ahora = new DateTime();
          $diff = $ahora->getTimestamp() - $fecha->getTimestamp();
          if ($diff < 60) $tiempo = 'Hace ' . $diff . ' segundos';
          elseif ($diff < 3600) $tiempo = 'Hace ' . floor($diff/60) . ' minutos';
          elseif ($diff < 86400) $tiempo = 'Hace ' . floor($diff/3600) . ' horas';
          else $tiempo = 'Hace ' . floor($diff/86400) . ' días';
        ?>
        <div class="<?php echo $itemClass; ?>" data-id-notificacion="<?php echo $n['id_notificacion']; ?>">
          <div class="notif-icon-wrapper">
            <i class="<?php echo $icon; ?>"></i>
          </div>
          <div class="notif-content">
            <div class="notif-header-row">
              <h3 class="notif-title"><?php echo htmlspecialchars($n['titulo']); ?></h3>
              <button class="notif-dismiss-btn" title="Eliminar notificación">
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>
            <p class="notif-message"><?php echo htmlspecialchars($n['mensaje']); ?></p>
            <div class="notif-footer">
              <span class="notif-time">
                <i class="fa-regular fa-clock"></i>
                <?php echo $tiempo; ?>
              </span>
              <?php echo $badge; ?>
              <button class="notif-action-btn">
                <i class="fa-regular fa-circle-check"></i>
                Marcar como leída
              </button>
            </div>
          </div>
          <div class="notif-status-indicator"></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<script src="<?php echo $baseUrl; ?>/public/js/toast.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const tabs = document.querySelectorAll('.notif-tab');
  tabs.forEach(tab => {
    tab.addEventListener('click', function() {
      tabs.forEach(t => t.classList.remove('active'));
      this.classList.add('active');
    });
  });
  document.querySelectorAll('.notif-action-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const notifItem = this.closest('.notif-item');
      const id = notifItem.getAttribute('data-id-notificacion');
      fetch('<?php echo $baseUrl; ?>/api/notifications.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_notificacion: id })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          notifItem.classList.remove('notif-unread');
          notifItem.style.opacity = '0.6';
          if (window.Toast) Toast.success('Notificación marcada como leída');
        } else {
          if (window.Toast) Toast.error('No se pudo marcar como leída');
        }
      })
      .catch(() => { if (window.Toast) Toast.error('Error de red'); });
    });
  });
  document.querySelectorAll('.notif-dismiss-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const notifItem = this.closest('.notif-item');
      const id = notifItem.getAttribute('data-id-notificacion');
      fetch('<?php echo $baseUrl; ?>/api/notifications.php?id_notificacion=' + id, {
        method: 'DELETE'
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          notifItem.style.transform = 'translateX(100%)';
          notifItem.style.opacity = '0';
          setTimeout(() => {
            notifItem.style.display = 'none';
          }, 300);
          if (window.Toast) Toast.success('Notificación eliminada');
        } else {
          if (window.Toast) Toast.error('No se pudo eliminar la notificación');
        }
      })
      .catch(() => { if (window.Toast) Toast.error('Error de red'); });
    });
  });
  const markAllBtn = document.querySelector('.notif-mark-all-btn');
  if (markAllBtn) {
    markAllBtn.addEventListener('click', function() {
      const unreadItems = document.querySelectorAll('.notif-unread');
      let total = unreadItems.length, success = 0, fail = 0;
      if (!total) {
        if (window.Toast) Toast.info('No hay notificaciones nuevas');
        return;
      }
      unreadItems.forEach(item => {
        const id = item.getAttribute('data-id-notificacion');
        fetch('<?php echo $baseUrl; ?>/api/notifications.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id_notificacion: id })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            item.classList.remove('notif-unread');
            item.style.opacity = '0.6';
            success++;
          } else {
            fail++;
          }
          if (success + fail === total) {
            if (window.Toast) {
              if (success) Toast.success('Todas marcadas como leídas');
              if (fail) Toast.error('Algunas no se pudieron marcar');
            }
          }
        })
        .catch(() => {
          fail++;
          if (success + fail === total && window.Toast) Toast.error('Error de red');
        });
      });
    });
  }
});
</script>
