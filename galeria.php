<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

$items = [];
try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id, titulo, descripcion, ruta_imagen, fecha_subida FROM galeria ORDER BY fecha_subida DESC');
    $stmt->execute();
    $res = $stmt->get_result();
    $items = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
} catch (Throwable $e) {
    log_error('List galeria error: ' . $e->getMessage());
    $items = [];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sentite Vos - Galería</title>
    <link rel="stylesheet" href="styles/bootstrap.min.css">
    <script src="scripts/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="styles/styles.css">
    <link rel="icon" href="assets/icon.ico">
    <meta name="theme-color" content="#f8b6d2">
    <style>
        .galeria-img {
            cursor: pointer
        }
    </style>
    <?php
    // Construir arreglo de rutas para el lightbox
    $rutas = array_map(fn($it) => $it['ruta_imagen'], $items);
    ?>
</head>

<body>
    <div id="nav-include"></div>
    <main>
        <section class="instagram" id="galeria">
            <h2>
                <svg width="24" height="24" fill="none" stroke="#3a7ca5" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="5" />
                    <circle cx="12" cy="12" r="3.5" fill="#e5738a" />
                    <circle cx="17.5" cy="6.5" r="1.5" />
                </svg> Últimos trabajos
            </h2>
            <div class="container py-4">
                <?php if (empty($items)): ?>
                    <div class="alert alert-info">No hay imágenes aún.</div>
                <?php else: ?>
                    <div class="row g-3">
                        <?php foreach ($items as $idx => $it): ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <img src="<?php echo e($it['ruta_imagen']); ?>" class="img-fluid rounded shadow-sm galeria-img"
                                    alt="<?php echo e($it['titulo']); ?>" data-bs-toggle="modal" data-bs-target="#galeriaModal"
                                    data-index="<?php echo (int) $idx; ?>">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <a href="https://instagram.com" target="_blank" class="btn-instagram">Ver más en Instagram</a>
            <a href="https://wa.me/543511234567" class="whatsapp-float" target="_blank" title="WhatsApp">
                <img src="assets/images/ws.png" alt="WhatsApp" style="width: 40px; height: auto;">
            </a>

            <!-- Modal Lightbox Bootstrap -->
            <div class="modal fade" id="galeriaModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-transparent border-0">
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-2"
                            data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        <img id="modal-galeria-img" src="" class="img-fluid rounded" alt="Imagen ampliada">
                        <button type="button" class="carousel-control-prev" id="galeriaPrev"
                            style="top:50%;left:0;transform:translateY(-50%);position:absolute;">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Anterior</span>
                        </button>
                        <button type="button" class="carousel-control-next" id="galeriaNext"
                            style="top:50%;right:0;transform:translateY(-50%);position:absolute;">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Siguiente</span>
                        </button>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <div id="footer-include"></div>
    <script src="scripts/include.js"></script>
    <script>
        const galeriaImgs = <?php echo json_encode($rutas, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
        let currentImg = 0;
        document.querySelectorAll('.galeria-img').forEach(img => {
            img.addEventListener('click', function () {
                currentImg = parseInt(this.getAttribute('data-index')) || 0;
                document.getElementById('modal-galeria-img').src = galeriaImgs[currentImg] || '';
            });
        });
        document.getElementById('galeriaPrev').addEventListener('click', function () {
            if (!galeriaImgs.length) return;
            currentImg = (currentImg - 1 + galeriaImgs.length) % galeriaImgs.length;
            document.getElementById('modal-galeria-img').src = galeriaImgs[currentImg];
        });
        document.getElementById('galeriaNext').addEventListener('click', function () {
            if (!galeriaImgs.length) return;
            currentImg = (currentImg + 1) % galeriaImgs.length;
            document.getElementById('modal-galeria-img').src = galeriaImgs[currentImg];
        });
        document.getElementById('galeriaModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modal-galeria-img').src = '';
        });
    </script>

</body>

</html>