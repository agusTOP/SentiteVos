<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}

// CSRF
if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido, recarga la página.');
    redirect('../login.php');
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    flash('error', 'Email y contraseña son obligatorios');
    redirect('../login.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id, nombre, email, password FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        flash('error', 'Email o contraseña incorrectos');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }
    $usuario = $result->fetch_assoc();

    if (!password_verify($password, $usuario['password'])) {
        flash('error', 'Email o contraseña incorrectos');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }

    // Rehash si cambió algoritmo por defecto
    if (password_needs_rehash($usuario['password'], PASSWORD_DEFAULT)) {
        $nuevoHash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare('UPDATE usuarios SET password = ? WHERE id = ?');
        $upd->bind_param('si', $nuevoHash, $usuario['id']);
        $upd->execute();
        $upd->close();
    }

    session_regenerate_id(true);
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];

    $stmt->close();
    $conn->close();
    redirect('../index.html');
} catch (Throwable $e) {
    log_error('Login error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../login.php');
}
?>