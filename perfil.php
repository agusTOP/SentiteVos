<?php
session_start();
require_once __DIR__ . '/config/helpers.php';

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión para ver tu perfil');
    redirect('login.php');
}

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Sentite Vos</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="assets/icon.ico">
</head>

<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="bg-white rounded shadow-sm p-4">
                    <h2 class="mb-4" style="color:#3a7ca5;">Tu Perfil</h2>
                    <?php if ($msg = flash_get('error')): ?>
                        <div class="alert alert-danger"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <?php if ($msg = flash_get('success')): ?>
                        <div class="alert alert-success"><?php echo e($msg); ?></div>
                    <?php endif; ?>
                    <dl class="row mb-4">
                        <dt class="col-sm-4">Nombre</dt>
                        <dd class="col-sm-8"><?php echo e($nombre); ?></dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8"><?php echo e($email); ?></dd>
                    </dl>
                    <div class="d-flex gap-2">
                        <a href="index.html" class="btn btn-login">Inicio</a>
                        <a href="php/logout.php" class="btn btn-register">Cerrar sesión</a>
                    </div>
                    <hr class="my-4">
                    <h3 class="mb-3" style="color:#e5738a;">Cambiar contraseña</h3>
                    <form method="POST" action="php/change_password.php" class="mt-2">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="currentPassword" class="form-label">Contraseña actual</label>
                            <input type="password" class="form-control" id="currentPassword" name="current_password"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">Nueva contraseña</label>
                            <input type="password" class="form-control" id="newPassword" name="new_password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" class="form-control" id="confirmPassword" name="confirm_password"
                                required>
                        </div>
                        <button type="submit" class="btn btn-login">Actualizar contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.html'; ?>
    <script src="scripts/include.js"></script>

</body>

</html>