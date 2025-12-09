<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../perfil.php');
}

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión');
    redirect('../login.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../perfil.php');
}

$userId = (int) $_SESSION['usuario_id'];
$current = $_POST['current_password'] ?? '';
$new = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($current === '' || $new === '' || $confirm === '') {
    flash('error', 'Completa todos los campos');
    redirect('../perfil.php');
}

if (strlen($new) < 6) {
    flash('error', 'La nueva contraseña debe tener al menos 6 caracteres');
    redirect('../perfil.php');
}

if ($new !== $confirm) {
    flash('error', 'Las contraseñas no coinciden');
    redirect('../perfil.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT password FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if (!$res || $res->num_rows !== 1) {
        $stmt->close();
        $conn->close();
        flash('error', 'Usuario no encontrado');
        redirect('../perfil.php');
    }
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!password_verify($current, $row['password'])) {
        $conn->close();
        flash('error', 'La contraseña actual es incorrecta');
        redirect('../perfil.php');
    }

    $hash = password_hash($new, PASSWORD_DEFAULT);
    $upd = $conn->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
    $upd->bind_param('si', $hash, $userId);
    $upd->execute();
    $upd->close();

    $conn->close();
    flash('success', 'Contraseña actualizada correctamente');
    redirect('../perfil.php');
} catch (Throwable $e) {
    log_error('Change password error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../perfil.php');
}
