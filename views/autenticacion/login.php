<?php
if (!function_exists('unimind_detect_base')) {
  function unimind_detect_base() {
    $derived = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    $docroot = rtrim($_SERVER['DOCUMENT_ROOT'], '/');

    // Probar primero con raíz vacía, luego con /unimind, finalmente el derivado
    $candidates = ['', '/unimind', $derived];
    foreach ($candidates as $c) {
      $swPath = $docroot . ($c === '' ? '' : $c) . '/sw.js';
      $manifestPath = $docroot . ($c === '' ? '' : $c) . '/public/manifest.webmanifest';
      if (file_exists($swPath) && file_exists($manifestPath)) {
        return $c;
      }
    }

    return $derived;
  }
}

$base = unimind_detect_base();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- PWA Meta Tags -->
  <meta name="description" content="Sistema de evaluación y monitoreo de salud mental para estudiantes universitarios - UniMind">
  <meta name="theme-color" content="#4a90e2">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="default">
  <meta name="apple-mobile-web-app-title" content="UniMind">
  
  <title>UniMind</title>
  
  <!-- PWA Manifest -->
  <link rel="manifest" href="<?= $base ?>/public/manifest.webmanifest">
  
  <!-- Favicon & Icons -->
  <link rel="icon" type="image/svg+xml" href="<?= $base ?>/public/icons/icon.svg">
  <link rel="icon" type="image/png" sizes="192x192" href="<?= $base ?>/public/icons/icon-192x192.png">
  <link rel="apple-touch-icon" href="<?= $base ?>/public/icons/icon-192x192.png">
  
  <!-- Stylesheets -->
  <link rel="stylesheet" href="public/css/style.css">
  <link rel="stylesheet" href="public/css/theme.css">
  <link rel="stylesheet" href="views/autenticacion/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
</head>
<body>
  <main class="login">
    <div class="login__wrapper">
      <!-- Panel izquierdo -->
      <section class="login__panel login__panel--left">
        <div class="login__content">
          <h1 class="login__title">Unimind</h1>
          <p class="login__cookie-msg">⚫ Las 'Cookies' deben estar habilitadas en su navegador</p>
          <h2 class="login__register-text">Registrarse como usuario</h2>

          <h3 class="login__welcome">BIENVENIDO A UNIMIND</h3>
          <p class="login__info-text">
            Se comunica a todos los ingresantes 2025 que, su acceso a la plataforma del UniMind estará siendo habilitada el lunes 18, para acceder deberán digitar sus datos:
          </p>
          <ul class="login__credentials">
            <li class="login__credentials-item"><strong class="login__credentials-label">Nombre de usuario:</strong> "Código-estudiante"</li>
            <li class="login__credentials-item"><strong class="login__credentials-label">Contraseña:</strong> "DNI"</li>
          </ul>
          <p class="login__note">
            Nota: Su código de estudiante será enviada por la Dirección de Servicios Académicos y Registro Central a su correo personal registrado en el momento de su postulación.
          </p>
          <p class="login__guides">
            Se comparte guías y videotutoriales de cómo ingresar a la plataforma de UniMInd
            <a href="#" class="login__manual-link">MANUALES Y GUÍAS</a>
          </p>
          <p class="login__contact">
            Asimismo, para dudas o consulta puede enviar un mensaje al correo <a href="mailto:avirtual@unjbg.edu.pe" class="login__contact-link">avirtual@unjbg.edu.pe</a> o llamar al 052-583000 Anexo 2998
          </p>
          <p class="login__footer-text">UniMind</p>
        </div>
      </section>

      <!-- Panel derecho -->
      <section class="login__panel login__panel--right">
        <div class="login__form-container">
          <h3 class="login__form-title">¿Ya tiene una cuenta?</h3>

          <?php if (isset($_SESSION['login_error'])): ?>
            <div class="login__error" style="color: #dc3545; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
              <?php echo htmlspecialchars($_SESSION['login_error']); ?>
            </div>
            <?php unset($_SESSION['login_error']); ?>
          <?php endif; ?>

          <form class="login__form" method="POST" action="controllers/AuthController.php">
            <label class="login__input-group">
              <input type="text" name="username" placeholder="Usuario" required>
            </label>
            <label class="login__input-group">
              <div class="password-wrapper">
                <input id="password" type="password" name="password" placeholder="Contraseña" required>
                <button type="button" id="passwordToggle" class="password-toggle" aria-label="Mostrar contraseña">
                  <i class="fas fa-eye"></i>
                </button>
              </div>
            </label>

            <div class="login__remember">
              <input type="checkbox" id="remember" name="remember">
              <label for="remember">Recordar nombre de usuario</label>
            </div>

            <button type="submit" class="login__btn">Iniciar sesión (ingresar)</button>

            <a href="#" class="login__forgot">¿Olvidó su usuario o contraseña?</a>
          </form>

          <!-- Información de usuarios de prueba -->
          <div class="login__test-users">
            <p class="login__test-users-item">Estudiante: <code class="login__test-users-code">codigo-estudiante</code> / <code class="login__test-users-code">dni</code></p>
            <p class="login__test-users-item">Profesor: <code class="login__test-users-code">profesor1</code> / <code class="login__test-users-code">123456</code></p>
            <p class="login__test-users-item">Admin: <code class="login__test-users-code">admin</code> / <code class="login__test-users-code">admin123</code></p>
          </div>
        </div>
      </section>
    </div>
  </main>
  
  <!-- PWA Service Worker Registration -->
  <script>
    if ('serviceWorker' in navigator) {
      window.addEventListener('load', () => {
        // Registrar SW usando la base dinámica para funcionar en diferentes entornos
        navigator.serviceWorker.register('<?= $base ?>/sw.js')
          .then(() => {
            // Service Worker registrado
          })
          .catch(() => {
            // Error al registrar Service Worker (silenciado)
          });
      });
    }
  </script>
  <script>
    // Toggle mostrar/ocultar contraseña
    (function(){
      const pw = document.getElementById('password');
      const btn = document.getElementById('passwordToggle');
      if (!pw || !btn) return;
      btn.addEventListener('click', function(){
        const icon = btn.querySelector('i');
        if (pw.type === 'password'){
          pw.type = 'text';
          icon.className = 'fas fa-eye-slash';
          btn.setAttribute('aria-label', 'Ocultar contraseña');
        } else {
          pw.type = 'password';
          icon.className = 'fas fa-eye';
          btn.setAttribute('aria-label', 'Mostrar contraseña');
        }
      });
    })();
  </script>
</body>
</html>
