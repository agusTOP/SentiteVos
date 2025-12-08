<?php
// Retorna el array de items de la galería desde la base de datos
// Uso en vistas: $items = require __DIR__ . '/obtener_galeria.php';

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

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
    log_error('Obtener galeria error: ' . $e->getMessage());
    $items = [];
}

return $items;
?>