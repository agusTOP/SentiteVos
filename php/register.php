<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../register.php');
}

// CSRF
if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido, recarga la página.');
    redirect('../register.php');
}

$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($nombre === '' || $email === '' || $password === '') {
    flash('error', 'Todos los campos son obligatorios');
    redirect('../register.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Email inválido');
    redirect('../register.php');
}
if (strlen($password) < 6) {
    flash('error', 'La contraseña debe tener al menos 6 caracteres');
    redirect('../register.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        flash('error', 'Este email ya está registrado');
        $stmt->close();
        $conn->close();
        redirect('../register.php');
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, password) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $nombre, $email, $password_hash);
    if ($stmt->execute()) {
        flash('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }
    flash('error', 'Error al registrar. Intenta nuevamente.');
    $stmt->close();
    $conn->close();
    redirect('../register.php');
} catch (Throwable $e) {
    log_error('Register error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../register.php');
}
?>