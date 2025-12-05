<?php session_start();
require_once __DIR__ . '/config/helpers.php';
ensureCsrfToken(); ?>
<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión - Sentite Vos</title>
  <link rel="stylesheet" href="styles/bootstrap.min.css">
  <script src="scripts/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="styles/styles.css">
  <link rel="icon" href="assets/icon.ico">
</head>

<body>
  <div id="nav-include"></div>
  <main class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-md-6 col-lg-4">
        <div class="bg-white rounded shadow-sm p-4">
          <h2 class="mb-4 text-center" style="color:#e5738a;">Iniciar sesión</h2>
          <?php if ($msg = flash_get('error')): ?>
            <div class="alert alert-danger"><?php echo e($msg); ?></div>
          <?php endif; ?>
          <?php if ($msg = flash_get('success')): ?>
            <div class="alert alert-success"><?php echo e($msg); ?></div>
          <?php endif; ?>
          <form method="POST" action="php/login.php">
            <?php echo csrf_field(); ?>
            <div class="mb-3">
              <label for="loginEmail" class="form-label">Email</label>
              <input type="email" class="form-control" id="loginEmail" name="email" required>
            </div>
            <div class="mb-3">
              <label for="loginPassword" class="form-label">Contraseña</label>
              <input type="password" class="form-control" id="loginPassword" name="password" required>
            </div>
            <button type="submit" class="btn btn-register w-100">Ingresar</button>
          </form>
          <hr class="my-4">
          <div>
            <p class="mb-2" style="color:#555;">¿No te llegó el correo de verificación?</p>
            <form method="POST" action="php/resend_verification.php" class="d-flex gap-2">
              <?php echo csrf_field(); ?>
              <input type="email" class="form-control" name="email" placeholder="Tu email" required>
              <button type="submit" class="btn btn-login">Reenviar</button>
            </form>
          </div>
          <div class="text-center mt-3">
            <a href="register.php" style="color:#3a7ca5;">¿No tienes cuenta? Registrate</a>
          </div>
        </div>
      </div>
    </div>
  </main>
  <div id="footer-include"></div>
  <script src="scripts/include.js"></script>

</body>

</html>