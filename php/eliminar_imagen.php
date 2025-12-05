<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Solo admin
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    flash('error', 'No autorizado');
    redirect('../admin/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('error', 'Método inválido');
    redirect('../admin/dashboard.php');
}

// CSRF
if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido, recarga la página.');
    redirect('../admin/dashboard.php');
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$ruta = trim($_POST['ruta'] ?? '');
if ($id <= 0 || $ruta === '') {
    flash('error', 'Datos inválidos.');
    redirect('../admin/dashboard.php');
}

try {
    $conn = conectarDB();
    // Verificar que la imagen exista en DB y coincida la ruta
    $stmt = $conn->prepare('SELECT ruta_imagen FROM galeria WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        $stmt->close();
        $conn->close();
        flash('error', 'La imagen no existe.');
        redirect('../admin/dashboard.php');
    }
    $row = $res->fetch_assoc();
    $stmt->close();

    if ($row['ruta_imagen'] !== $ruta) {
        $conn->close();
        flash('error', 'Ruta no coincide.');
        redirect('../admin/dashboard.php');
    }

    // Eliminar archivo físico
    $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $ruta;
    if (is_file($filePath)) {
        @unlink($filePath);
    }

    // Eliminar registro DB
    $del = $conn->prepare('DELETE FROM galeria WHERE id = ?');
    $del->bind_param('i', $id);
    $del->execute();
    $del->close();

    $conn->close();
    flash('success', 'Imagen eliminada.');
    redirect('../admin/dashboard.php');
} catch (Throwable $e) {
    log_error('Eliminar imagen error: ' . $e->getMessage());
    flash('error', 'Error interno al eliminar.');
    redirect('../admin/dashboard.php');
}
?>