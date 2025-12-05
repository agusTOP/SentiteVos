<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id, titulo, ruta_imagen, fecha_subida FROM galeria ORDER BY fecha_subida DESC');
    $stmt->execute();
    $res = $stmt->get_result();
    $items = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    $conn->close();
    echo json_encode($items);
} catch (Throwable $e) {
    log_error('API galeria ultimas error: ' . $e->getMessage());
    echo json_encode([]);
}
?>