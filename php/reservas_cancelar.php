<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../mis-reservas.php');
}

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión');
    redirect('../login.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../mis-reservas.php');
}

$id = (int) ($_POST['id'] ?? 0);
$userId = (int) $_SESSION['usuario_id'];

if ($id <= 0) {
    flash('error', 'Reserva inválida');
    redirect('../mis-reservas.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare("UPDATE reservas SET estado='cancelada' WHERE id=? AND usuario_id=?");
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $stmt->close();
    $conn->close();
    flash('success', 'Reserva cancelada');
    redirect('../mis-reservas.php');
} catch (Throwable $e) {
    log_error('Cancelar reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo cancelar');
    redirect('../mis-reservas.php');
}
