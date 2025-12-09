<?php
session_start();
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/database.php';
ensureCsrfToken();

$token = trim($_GET['token'] ?? '');
$valid = false;
if ($token !== '') {
    try {
        $conn = conectarDB();
        $stmt = $conn->prepare('SELECT id, email FROM usuarios WHERE password_reset_token = ? AND password_reset_expires > NOW()');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $res = $stmt->get_result();
        $valid = $res && $res->num_rows === 1;
        $stmt->close();
        $conn->close();
    } catch (Throwable $e) {
        log_error('Reset password view error: ' . $e->getMessage());
        $valid = false;
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - Sentite Vos</title>
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
                    <h2 class="mb-4 text-center" style="color:#3a7ca5;">Restablecer contraseña</h2>
                    <?php if (!$valid): ?>
                        <div class="alert alert-danger">El enlace no es válido o ha expirado.</div>
                    <?php else: ?>
                        <?php if ($msg = flash_get('error')): ?>
                            <div class="alert alert-danger"><?php echo e($msg); ?></div>
                        <?php endif; ?>
                        <?php if ($msg = flash_get('success')): ?>
                            <div class="alert alert-success"><?php echo e($msg); ?></div>
                        <?php endif; ?>
                        <form method="POST" action="php/reset_password.php">
                            <?php echo csrf_field(); ?>
                            <input type="hidden" name="token" value="<?php echo e($token); ?>">
                            <div class="mb-3">
                                <label for="newPassword" class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" id="newPassword" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirmar contraseña</label>
                                <input type="password" class="form-control" id="confirmPassword" name="password_confirm"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-login w-100">Actualizar contraseña</button>
                        </form>
                    <?php endif; ?>
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