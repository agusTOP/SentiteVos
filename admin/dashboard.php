<?php
session_start();
require_once __DIR__ . '/../config/helpers.php';

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    die('Acceso no autorizado.');
}

$success = flash_get('success');
$error = flash_get('error');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin</title>
    <link href="../styles/bootstrap.min.css" rel="stylesheet">
    <link href="../styles/styles.css" rel="stylesheet">
    <link rel="icon" href="../assets/icon.ico">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top">
        <div class="container">
            <a class="navbar-brand logo d-flex align-items-center" href="../index.html" style="font-weight:bold;padding:0;margin:0;">
                <img src="../assets/images/logo_sin_fondo.png" alt="Sentite Vos" style="width: 100px; height: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar"
                aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <span class="nav-link active" aria-current="page" style="font-weight:700;color:#e5738a;">Panel de Administración</span>
                    </li>
                </ul>
                <div class="d-flex gap-2 ms-auto">
                    <a class="btn btn-login" href="../index.html">Ir al sitio</a>
                    <a class="btn btn-register" href="../php/logout.php">Salir</a>
                </div>
            </div>
        </div>
    </nav>

    <main>
    <div class="container py-4">
        <h1 class="mb-4">Panel de Administración</h1>

        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="galeria-tab" data-bs-toggle="tab" data-bs-target="#galeria"
                    type="button" role="tab">Galería</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="usuarios-tab" data-bs-toggle="tab" data-bs-target="#usuarios" type="button"
                    role="tab">Usuarios</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reservas-tab" data-bs-toggle="tab" data-bs-target="#reservas" type="button"
                    role="tab">Reservas</button>
            </li>
        </ul>

        <div class="tab-content pt-3" id="adminTabsContent">
            <div class="tab-pane fade show active" id="galeria" role="tabpanel">
                <h2 class="mb-3 admin-section-title">Subir imagen a la galería</h2>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo e($success); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo e($error); ?></div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <form action="../php/subir_imagen.php" method="post" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" maxlength="150"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion" class="form-label">Descripción (opcional)</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                    maxlength="1000"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="imagen" class="form-label">Imagen (JPG, PNG, WEBP)</label>
                                <input class="form-control" type="file" id="imagen" name="imagen"
                                    accept="image/jpeg,image/png,image/webp" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Subir</button>
                        </form>
                    </div>
                </div>

                <h3 class="mt-4 mb-3 admin-section-subtitle">Últimas imágenes</h3>
                <div class="row" id="ultimas-imagenes"></div>
            </div>

            <div class="tab-pane fade" id="usuarios" role="tabpanel">
                <h2 class="mb-3 admin-section-title">Gestión de Usuarios</h2>
                <?php $usuarios = require __DIR__ . '/../php/admin_list_users.php'; ?>
                <?php if (empty($usuarios)): ?>
                    <div class="alert alert-info">No hay usuarios para mostrar.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Verificado</th>
                                    <th>Rol</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                    <tr>
                                        <td><?php echo (int) $u['id']; ?></td>
                                        <td><?php echo e($u['nombre']); ?></td>
                                        <td><?php echo e($u['email']); ?></td>
                                        <td>
                                            <?php echo ((int) $u['email_verified'] === 1) ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
                                        </td>
                                        <td><span
                                                class="badge bg-<?php echo ($u['rol'] === 'admin') ? 'primary' : 'info'; ?>"><?php echo e($u['rol']); ?></span>
                                        </td>
                                        <td class="d-flex gap-2">
                                            <form method="post" action="../php/admin_update_role.php">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                                                <input type="hidden" name="role" value="admin">
                                                <button type="submit" class="btn btn-sm btn-outline-primary" <?php echo ($u['rol'] === 'admin') ? 'disabled' : ''; ?>>Hacer Admin</button>
                                            </form>
                                            <form method="post" action="../php/admin_update_role.php">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="user_id" value="<?php echo (int) $u['id']; ?>">
                                                <input type="hidden" name="role" value="cliente">
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" <?php echo ($u['rol'] === 'cliente') ? 'disabled' : ''; ?>>Hacer Cliente</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="reservas" role="tabpanel">
                <h2 class="mb-3 admin-section-title">Gestión de Reservas</h2>
                <?php
                try {
                    require_once __DIR__ . '/../config/database.php';
                    $conn = conectarDB();
                    $res = $conn->query("SELECT r.id, r.servicio, r.fecha, r.hora, r.estado, u.nombre, u.email FROM reservas r JOIN usuarios u ON u.id=r.usuario_id ORDER BY r.fecha DESC, r.hora DESC");
                    $reservas = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
                    $conn->close();
                } catch (Throwable $e) {
                    log_error('Admin listar reservas error: ' . $e->getMessage());
                    $reservas = [];
                }
                ?>
                <?php if (empty($reservas)): ?>
                    <div class="alert alert-info">No hay reservas aún.</div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Servicio</th>
                                    <th>Cliente</th>
                                    <th>Email</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas as $r): ?>
                                    <tr>
                                        <td><?php echo e($r['fecha']); ?></td>
                                        <td><?php echo substr($r['hora'], 0, 5); ?></td>
                                        <td><?php echo e($r['servicio']); ?></td>
                                        <td><?php echo e($r['nombre']); ?></td>
                                        <td><?php echo e($r['email']); ?></td>
                                        <td><span
                                                class="badge bg-<?php echo $r['estado'] === 'confirmada' ? 'success' : ($r['estado'] === 'pendiente' ? 'warning text-dark' : 'secondary'); ?>"><?php echo e($r['estado']); ?></span>
                                        </td>
                                        <td class="d-flex gap-2">
                                            <form method="post" action="../php/admin_reserva_estado.php">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                                <input type="hidden" name="estado" value="confirmada">
                                                <button class="btn btn-sm btn-outline-success" type="submit" <?php echo $r['estado'] === 'confirmada' ? 'disabled' : ''; ?>>Confirmar</button>
                                            </form>
                                            <form method="post" action="../php/admin_reserva_estado.php">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="id" value="<?php echo (int) $r['id']; ?>">
                                                <input type="hidden" name="estado" value="cancelada">
                                                <button class="btn btn-sm btn-outline-danger" type="submit" <?php echo $r['estado'] === 'cancelada' ? 'disabled' : ''; ?>>Cancelar</button>
                                            </form>
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
    </main>

    <footer class="footer-minimal py-4 bg-white border-top mt-auto">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <div class="footer-logo" style="font-size:1.5rem;font-weight:bold;color:#e5738a;">
                <img src="../assets/images/logo.png" alt="Sentite Vos" style="width: 100px; height: auto;">
            </div>
            <ul class="footer-links list-unstyled d-flex gap-3 mb-0">
                <li><a href="../index.html">Inicio</a></li>
                <li><a href="../servicios.html">Servicios</a></li>
                <li><a href="../galeria.php">Galería</a></li>
                <li><a href="../sobre-nosotros.html">Sobre Nosotros</a></li>
                <li><a href="../contacto.html">Contacto</a></li>
            </ul>
            <div class="footer-redes d-flex gap-2">
                <a href="https://wa.me/3534207231" target="_blank" title="WhatsApp">
                    <img src="../assets/images/ws.png" alt="WhatsApp" style="width: 24px; height: auto;">
                </a>
                <a href="https://www.instagram.com/sentitevos_ld?igsh=aG5wN2F3aWpiaG9s" target="_blank" title="Instagram">
                    <img src="../assets/images/insta_logo.png" alt="Instagram" style="width: 24px; height: auto;">
                </a>
                <a href="https://www.facebook.com/TerminalBallestero/" target="_blank" title="Facebook">
                    <img src="../assets/images/face_logo.png" alt="Facebook" style="width: 24px; height: auto;">
                </a>
            </div>
        </div>
        <div class="text-center mt-3" style="font-size:0.95rem;color:#888;">&copy; 2025 Sentite Vos. Todos los derechos
            reservados.</div>
    </footer>

    <script src="../scripts/bootstrap.bundle.min.js"></script>
    <script>
        // Persist active admin tab across visits
        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('#adminTabs button[data-bs-toggle="tab"]');
            const stored = localStorage.getItem('adminTab');
            if (stored) {
                const targetBtn = Array.from(tabs).find(btn => btn.getAttribute('data-bs-target') === stored);
                if (targetBtn) {
                    const tab = new bootstrap.Tab(targetBtn);
                    tab.show();
                }
            }
            tabs.forEach(btn => {
                btn.addEventListener('shown.bs.tab', function (e) {
                    const target = e.target.getAttribute('data-bs-target');
                    localStorage.setItem('adminTab', target);
                });
            });
        });
        fetch('../api/galeria_ultimas.php')
            .then(r => r.json())
            .then(items => {
                const row = document.getElementById('ultimas-imagenes');
                if (!row) return;
                if (!Array.isArray(items) || items.length === 0) {
                    row.innerHTML = '<div class="alert alert-info">No hay imágenes aún.</div>';
                    return;
                }
                const render = items.slice(0, 12).map(it => `
          <div class="col-12 col-sm-6 col-lg-4 mb-3">
            <div class="card h-100 shadow-sm">
              <img src="../${it.ruta_imagen}" class="card-img-top" alt="${it.titulo}">
              <div class="card-body">
                <h5 class="card-title">${it.titulo}</h5>
                <p class="text-muted small mb-0">${it.fecha_subida}</p>
                                    <form method="post" action="../php/eliminar_imagen.php" onsubmit="return confirm('¿Eliminar esta imagen?');">
                                        <input type="hidden" name="id" value="${it.id}">
                                        <input type="hidden" name="ruta" value="${it.ruta_imagen}">
                                        <input type="hidden" name="_token" value="<?php echo csrf_token(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
              </div>
            </div>
          </div>
                    `).join('');
                row.innerHTML = render;
            }).catch(() => { });
    </script>
</body>

</html>