<?php
session_start();
require_once __DIR__ . '/config/helpers.php';
require_once __DIR__ . '/config/database.php';

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión para ver tus reservas');
    redirect('login.php');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$reservas = [];
try {
    $conn = conectarDB();
    $stmt = $conn->prepare("SELECT id, servicio, fecha, hora, estado, notas FROM reservas WHERE usuario_id = ? AND fecha >= CURDATE() ORDER BY fecha, hora");
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res)
        $reservas = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    log_error('Listar reservas usuario error: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis reservas - Sentite Vos</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="assets/icon.ico">
</head>

<body>
    <?php include __DIR__ . '/includes/nav.php'; ?>
    <main class="container py-5">
        <h2 class="mb-4" style="color:#3a7ca5;">Mis reservas</h2>
        <?php if ($msg = flash_get('error')): ?>
            <div class="alert alert-danger"><?php echo e($msg); ?></div>
        <?php endif; ?>
        <?php if ($msg = flash_get('success')): ?>
            <div class="alert alert-success"><?php echo e($msg); ?></div>
        <?php endif; ?>

        <?php if (empty($reservas)): ?>
            <div class="alert alert-info">No tienes reservas futuras. <a href="reservas.php">Reservar ahora</a></div>
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
                                            <button class="btn btn-sm btn-outline-danger" type="submit">Cancelar</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    <?php include __DIR__ . '/includes/footer.html'; ?>
</body>

</html>