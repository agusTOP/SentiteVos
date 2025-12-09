<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../reservas.php');
}

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión');
    redirect('../login.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../reservas.php');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$servicio = trim($_POST['servicio'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$hora = trim($_POST['hora'] ?? '');
$notas = trim($_POST['notas'] ?? '');

if ($servicio === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
    flash('error', 'Datos de reserva inválidos');
    redirect('../reservas.php');
}

try {
    $conn = conectarDB();
    // Evitar superposiciones: verificar ocupación
    $check = $conn->prepare("SELECT id FROM reservas WHERE fecha=? AND hora=? AND estado IN ('pendiente','confirmada')");
    $check->bind_param('ss', $fecha, $hora);
    $check->execute();
    $r = $check->get_result();
    if ($r && $r->num_rows > 0) {
        $check->close();
        $conn->close();
        flash('error', 'Ese horario ya está ocupado');
        redirect('../reservas.php');
    }
    $check->close();

    $stmt = $conn->prepare('INSERT INTO reservas (usuario_id, servicio, fecha, hora, estado, notas) VALUES (?, ?, ?, ?, "pendiente", ?)');
    $stmt->bind_param('issss', $usuarioId, $servicio, $fecha, $hora, $notas);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    flash('success', 'Reserva creada. Te confirmaremos a la brevedad.');
    redirect('../mis-reservas.php');
} catch (Throwable $e) {
    log_error('Crear reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo crear la reserva');
    redirect('../reservas.php');
}
