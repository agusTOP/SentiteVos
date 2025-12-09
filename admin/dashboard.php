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
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand">Panel de Administración</span>
            <div class="d-flex">
                <a class="btn btn-outline-light me-2" href="../index.html">Ir al sitio</a>
                <a class="btn btn-outline-light" href="../php/logout.php">Salir</a>
            </div>
        </div>
    </nav>

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
        </ul>

        <div class="tab-content pt-3" id="adminTabsContent">
            <div class="tab-pane fade show active" id="galeria" role="tabpanel">
                <h2 class="mb-3">Subir imagen a la galería</h2>

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

                <h3 class="mt-4 mb-3">Últimas imágenes</h3>
                <div class="row" id="ultimas-imagenes"></div>
            </div>

            <div class="tab-pane fade" id="usuarios" role="tabpanel">
                <h2 class="mb-3">Gestión de Usuarios</h2>
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
        </div>
    </div>

    <script src="../scripts/bootstrap.bundle.min.js"></script>
    <script>
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