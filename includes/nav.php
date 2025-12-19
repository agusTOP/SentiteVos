<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/config/helpers.php';

$loggedIn = !empty($_SESSION['usuario_id']);
$nombre = $_SESSION['usuario_nombre'] ?? null;
$nombreCorto = $nombre ? explode(' ', $nombre)[0] : null;
$rol = $_SESSION['usuario_rol'] ?? 'cliente';
?>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
    <div class="container">
        <a class="navbar-brand logo" href="index.html" style="font-weight:bold;padding: 0;margin: 0;"><img
                src="assets/images/logo_sin_fondo.png" alt="Sentite Vos" style="width: 100px; height: auto;"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="index.html">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="servicios.html">Servicios</a></li>
                <li class="nav-item"><a class="nav-link" href="galeria.php">Galería</a></li>
                <li class="nav-item"><a class="nav-link" href="sobre-nosotros.html">Sobre Nosotros</a></li>
                <li class="nav-item"><a class="nav-link" href="contacto.html">Contacto</a></li>
                <li class="nav-item"><a class="nav-link" href="reservas.php">Reservas</a></li>
            </ul>
            <div class="d-flex gap-2 ms-auto" id="auth-buttons">
                <?php if ($loggedIn): ?>
                    <?php if ($rol === 'admin'): ?>
                        <a href="admin/dashboard.php" class="btn btn-admin">Panel Admin</a>
                    <?php endif; ?>
                    <a href="perfil.php" class="btn btn-login"><?php echo e($nombreCorto ?? 'Usuario'); ?></a>
                    <a href="php/logout.php" class="btn btn-register">Salir</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-login">Iniciar sesión</a>
                    <a href="register.php" class="btn btn-register">Registrarse</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>