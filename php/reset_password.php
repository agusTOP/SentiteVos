<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../reset_password.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../reset_password.php?token=' . urlencode($_POST['token'] ?? ''));
}

$token = trim($_POST['token'] ?? '');
$pass = $_POST['password'] ?? '';
$confirm = $_POST['password_confirm'] ?? '';

if ($token === '' || $pass === '' || $confirm === '') {
    flash('error', 'Completa todos los campos');
    redirect('../reset_password.php?token=' . urlencode($token));
}

if ($pass !== $confirm) {
    flash('error', 'Las contraseñas no coinciden');
    redirect('../reset_password.php?token=' . urlencode($token));
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE password_reset_token = ? AND password_reset_expires > NOW()');
    $stmt->bind_param('s', $token);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) {
        $stmt->close();
        $conn->close();
        flash('error', 'El enlace no es válido o ha expirado');
        redirect('../reset_password.php?token=' . urlencode($token));
    }
    $user = $res->fetch_assoc();
    $stmt->close();

    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $upd = $conn->prepare('UPDATE usuarios SET password = ?, password_reset_token = NULL, password_reset_expires = NULL WHERE id = ?');
    $upd->bind_param('si', $hash, $user['id']);
    $upd->execute();
    $upd->close();

    $conn->close();
    flash('success', 'Contraseña actualizada, ya puedes iniciar sesión');
    redirect('../login.php');
} catch (Throwable $e) {
    log_error('Reset password error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../reset_password.php?token=' . urlencode($token));
}
