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

$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');

if ($titulo === '') {
    flash('error', 'El título es obligatorio.');
    redirect('../admin/dashboard.php');
}

if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'Debe seleccionar una imagen válida.');
    redirect('../admin/dashboard.php');
}

$file = $_FILES['imagen'];

// Validar tamaño (opcional: 5MB)
$maxSize = 5 * 1024 * 1024; // 5MB
if ($file['size'] > $maxSize) {
    flash('error', 'La imagen supera el tamaño máximo de 5MB.');
    redirect('../admin/dashboard.php');
}

// Validar imagen real
$imgInfo = @getimagesize($file['tmp_name']);
if ($imgInfo === false) {
    flash('error', 'El archivo no es una imagen válida.');
    redirect('../admin/dashboard.php');
}

// Validar tipos permitidos
$allowedTypes = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

$mime = $imgInfo['mime'] ?? '';
if (!isset($allowedTypes[$mime])) {
    flash('error', 'Tipo de imagen no permitido. Use JPG, PNG o WEBP.');
    redirect('../admin/dashboard.php');
}

$ext = $allowedTypes[$mime];

// Nombre único
$safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo($file['name'], PATHINFO_FILENAME));
$unique = uniqid($safeBase . '_', true);
$filename = $unique . '.' . $ext;

// Carpeta destino
$uploadDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0775, true);
}

$destPath = $uploadDir . DIRECTORY_SEPARATOR . $filename;
$publicPath = 'assets/uploads/' . $filename; // ruta relativa para servir desde web

// Mover archivo
if (!@move_uploaded_file($file['tmp_name'], $destPath)) {
    flash('error', 'No se pudo guardar la imagen.');
    redirect('../admin/dashboard.php');
}

// Insertar en DB
try {
    $conn = conectarDB();
    $stmt = $conn->prepare('INSERT INTO galeria (titulo, descripcion, ruta_imagen, fecha_subida) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('sss', $titulo, $descripcion, $publicPath);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    flash('success', 'Imagen subida correctamente.');
    redirect('../admin/dashboard.php');
} catch (Throwable $e) {
    log_error('Upload galeria error: ' . $e->getMessage());
    // revertir archivo guardado si falla DB
    @unlink($destPath);
    flash('error', 'Error interno al guardar en la galería.');
    redirect('../admin/dashboard.php');
}
?>