<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../config/env.php';

// Carga variables desde .env en la raíz del proyecto si existe
loadEnv(dirname(__DIR__));

// Carga PHPMailer vía Composer
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, password, email_verify_token) VALUES (?, ?, ?, ?)');
    // Generar token de verificación
    $verifyToken = bin2hex(random_bytes(32));
    $stmt->bind_param('ssss', $nombre, $email, $password_hash, $verifyToken);
    if ($stmt->execute()) {
        // Envío de email de verificación
        try {
            $mail = new PHPMailer(true);
            if (defined('APP_DEBUG') && APP_DEBUG) {
                $mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST', 'smtp.example.com');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME', 'defaultmail@gmail.com');
            $mail->Password = env('MAIL_PASSWORD', 'pass');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = (int) env('MAIL_PORT', 0);

            $fromEmail = env('MAIL_FROM', $mail->Username);
            $fromName = env('MAIL_FROM_NAME', 'Sentite Vos');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($email, $nombre);

            // Construir URL de verificación
            $appUrl = env('APP_URL', null);
            $verifyPath = '/php/verify_email.php?token=' . urlencode($verifyToken) . '&email=' . urlencode($email);
            $verifyUrl = $appUrl ? rtrim($appUrl, '/') . $verifyPath : ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $verifyPath);

            $mail->isHTML(true);
            $mail->Subject = 'Verifica tu correo - Sentite Vos';
            $mail->Body = '<div style="font-family: Arial, sans-serif; color:#333;">'
                . '<h2 style="color:#e5738a;">Hola ' . e($nombre) . '</h2>'
                . '<p>Gracias por registrarte. Para activar tu cuenta, haz clic en el siguiente enlace:</p>'
                . '<p><a style="background:#3a7ca5;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;" href="' . e($verifyUrl) . '">Verificar mi correo</a></p>'
                . '<p style="font-size:12px;color:#777;">Si no te registraste, ignora este mensaje.</p>'
                . '</div>';
            $mail->AltBody = 'Hola ' . $nombre . ' - Verifica tu cuenta: ' . $verifyUrl;
            $mail->send();
            // Guardar timestamp de último envío
            $upd = $conn->prepare('UPDATE usuarios SET last_verification_sent_at = NOW() WHERE email = ?');
            $upd->bind_param('s', $email);
            $upd->execute();
            $upd->close();
        } catch (Throwable $me) {
            log_error('Mail verificación fallo: ' . $me->getMessage());
        }
        flash('success', 'Registro exitoso. Te enviamos un correo para verificar tu cuenta.');
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