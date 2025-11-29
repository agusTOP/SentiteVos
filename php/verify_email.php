<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';

// Solo vía GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    flash('error', 'Método inválido');
    redirect('../login.php');
}

$email = trim($_GET['email'] ?? '');
$token = trim($_GET['token'] ?? '');

if ($email === '' || $token === '') {
    flash('error', 'Enlace de verificación inválido');
    redirect('../login.php');
}

try {
    $conn = conectarDB();
    // Buscar usuario con email y token
    $stmt = $conn->prepare('SELECT id, email_verified FROM usuarios WHERE email = ? AND email_verify_token = ?');
    $stmt->bind_param('ss', $email, $token);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        flash('error', 'Token inválido o ya utilizado');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }
    $usuario = $result->fetch_assoc();

    if ((int) $usuario['email_verified'] === 1) {
        flash('success', 'Tu correo ya estaba verificado.');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }

    // Marcar verificado y limpiar token
    $upd = $conn->prepare('UPDATE usuarios SET email_verified = 1, email_verified_at = NOW(), email_verify_token = NULL WHERE id = ?');
    $upd->bind_param('i', $usuario['id']);
    $upd->execute();
    $upd->close();

    $stmt->close();
    $conn->close();

    flash('success', 'Correo verificado correctamente. Ya puedes iniciar sesión.');
    redirect('../login.php');
} catch (Throwable $e) {
    log_error('Verify email error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../login.php');
}
