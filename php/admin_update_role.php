<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/dashboard.php');
}

// Auth + authorization
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    flash('error', 'Acceso no autorizado');
    redirect('../admin/dashboard.php');
}

// CSRF
if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../admin/dashboard.php');
}

$userId = (int) ($_POST['user_id'] ?? 0);
$newRole = trim($_POST['role'] ?? '');

if ($userId <= 0 || !in_array($newRole, ['admin', 'cliente'], true)) {
    flash('error', 'Datos inválidos');
    redirect('../admin/dashboard.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('UPDATE usuarios SET rol = ? WHERE id = ?');
    $stmt->bind_param('si', $newRole, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    flash('success', 'Rol actualizado correctamente');
} catch (Throwable $e) {
    log_error('Admin update role error: ' . $e->getMessage());
    flash('error', 'No se pudo actualizar el rol');
}

redirect('../admin/dashboard.php');
