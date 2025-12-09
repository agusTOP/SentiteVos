<?php
session_start();
require_once __DIR__ . '/config/helpers.php';
ensureCsrfToken();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - Sentite Vos</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="assets/icon.ico">
</head>

<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">
                <div class="bg-white rounded shadow-sm p-4">
                    <h2 class="mb-4 text-center" style="color:#3a7ca5;">Recuperar contraseña</h2>
                    <?php if ($msg = flash_get('error')): ?>
                        <div class="alert alert-danger"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <?php if ($msg = flash_get('success')): ?>
                        <div class="alert alert-success"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="php/request_password_reset.php">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="resetEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="resetEmail" name="email" required>
                        </div>
                        <button type="submit" class="btn btn-login w-100">Enviar enlace</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="login.php" style="color:#e5738a;">Volver a iniciar sesión</a>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.html'; ?>
</body>

</html>