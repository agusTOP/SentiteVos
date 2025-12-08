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
    <title>Panel Admin - Subir Imagen</title>
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
        <h1 class="mb-4">Subir imagen a la galería</h1>

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
                        <input type="text" class="form-control" id="titulo" name="titulo" maxlength="150" required>
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

        <h2 class="mt-5 mb-3">Últimas imágenes</h2>
        <div class="row" id="ultimas-imagenes"></div>
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