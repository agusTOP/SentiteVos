<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/dashboard.php');
}

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    flash('error', 'Acceso no autorizado');
    redirect('../admin/dashboard.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../admin/dashboard.php');
}

$id = (int) ($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';
if ($id <= 0 || !in_array($estado, ['pendiente', 'confirmada', 'cancelada'], true)) {
    flash('error', 'Datos inválidos');
    redirect('../admin/dashboard.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('UPDATE reservas SET estado = ? WHERE id = ?');
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    flash('success', 'Reserva actualizada');
} catch (Throwable $e) {
    log_error('Admin actualizar reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo actualizar');
}

redirect('../admin/dashboard.php');
