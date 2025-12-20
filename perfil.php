<?php
session_start();
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión para ver tu perfil');
    redirect('login.php');
}

$nombre = $_SESSION['usuario_nombre'] ?? 'Usuario';
$email = $_SESSION['usuario_email'] ?? '';

// Cargar reservas futuras del usuario
$reservas = [];
try {
    $uid = (int) ($_SESSION['usuario_id'] ?? 0);
    if ($uid > 0) {
        $conn = conectarDB();
        $stmt = $conn->prepare("SELECT id, servicio, fecha, hora, estado FROM reservas WHERE usuario_id = ? AND fecha >= CURDATE() ORDER BY fecha, hora");
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res)
            $reservas = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $conn->close();
    }
} catch (Throwable $e) {
    log_error('Perfil listar reservas error: ' . $e->getMessage());
}
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
                    <ul class="nav nav-tabs" id="perfilTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info"
                                type="button" role="tab">Información</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password"
                                type="button" role="tab">Contraseña</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas"
                                type="button" role="tab">Reservas</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="perfilTabsContent">
                        <div class="tab-pane fade show active" id="info" role="tabpanel">
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
                        </div>

                        <div class="tab-pane fade" id="password" role="tabpanel">
                            <h3 class="mb-3" style="color:#e5738a;">Cambiar contraseña</h3>
                            <form method="POST" action="php/change_password.php" class="mt-2">
                                <?php echo csrf_field(); ?>
                                <div class="mb-3">
                                    <label for="currentPassword" class="form-label">Contraseña actual</label>
                                    <input type="password" class="form-control" id="currentPassword"
                                        name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="newPassword" class="form-label">Nueva contraseña</label>
                                    <input type="password" class="form-control" id="newPassword" name="new_password"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label">Confirmar nueva contraseña</label>
                                    <input type="password" class="form-control" id="confirmPassword"
                                        name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-login">Actualizar contraseña</button>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="reservas" role="tabpanel">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h3 class="mb-0" style="color:#3a7ca5;">Tus reservas</h3>
                                <a href="reservas.php" class="btn btn-sm btn-outline-primary">Reservar</a>
                            </div>
                            <?php if (empty($reservas)): ?>
                                <div class="alert alert-info">No tienes reservas futuras.</div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Hora</th>
                                                <th>Servicio</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($reservas as $r): ?>
                                                <tr>
                                                    <td><?php echo e(date('d/m/Y', strtotime($r['fecha']))); ?></td>
                                                    <td><?php echo substr($r['hora'], 0, 5); ?></td>
                                                    <td><?php echo e($r['servicio']); ?></td>
                                                    <td><span
                                                            class="badge bg-<?php echo $r['estado'] === 'confirmada' ? 'success' : ($r['estado'] === 'pendiente' ? 'warning text-dark' : 'secondary'); ?>"><?php echo e($r['estado']); ?></span>
                                                    </td>
                                                    <td>
                                                        <?php if ($r['estado'] !== 'cancelada'): ?>
                                                            <form method="post" action="php/reservas_cancelar.php"
                                                                onsubmit="return confirm('¿Cancelar este turno?');">
                                                                <?php echo csrf_field(); ?>
                                                                <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                                                <button class="btn btn-sm btn-outline-danger"
                                                                    type="submit">Cancelar</button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.html'; ?>
    <script src="scripts/include.js"></script>
    <script>
        // Persistir pestaña activa del perfil respetando estilos Bootstrap
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('#perfilTabs button[data-bs-toggle="tab"]');
            const urlParams = new URLSearchParams(window.location.search);
            const requested = urlParams.get('tab');
            const stored = localStorage.getItem('perfilTab');
            // Activar la pestaña almacenada si existe
            const initialTarget = requested ? `#${requested}` : stored;
            if (initialTarget) {
                const targetBtn = Array.from(tabs).find(btn => btn.getAttribute('data-bs-target') === initialTarget);
                if (targetBtn) {
                    const tab = new bootstrap.Tab(targetBtn);
                    tab.show();
                }
            }
            // Guardar cambios de pestaña
            tabs.forEach(btn => {
                btn.addEventListener('shown.bs.tab', function (e) {
                    const target = e.target.getAttribute('data-bs-target');
                    localStorage.setItem('perfilTab', target);
                });
            });
        });
    </script>

</body>

</html>