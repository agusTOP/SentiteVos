<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../forgot_password.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../forgot_password.php');
}

$email = trim($_POST['email'] ?? '');
if ($email === '') {
    flash('error', 'Ingresa tu email');
    redirect('../forgot_password.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id, email, nombre FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        // No revelar si existe; mostrar éxito genérico
        $stmt->close();
        $conn->close();
        flash('success', 'Si el email existe, enviamos un enlace para resetear.');
        redirect('../forgot_password.php');
    }
    $user = $res->fetch_assoc();
    $stmt->close();

    // Generar token y guardar con expiración 1 hora
    $token = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', time() + 3600);
    $upd = $conn->prepare('UPDATE usuarios SET password_reset_token = ?, password_reset_expires = ? WHERE id = ?');
    $upd->bind_param('ssi', $token, $expires, $user['id']);
    $upd->execute();
    $upd->close();

    // Enviar email
    $resetLink = sprintf('%s/reset_password.php?token=%s', rtrim(dirname(dirname($_SERVER['REQUEST_URI'])), '/'), $token);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = env('SMTP_HOST', 'smtp.example.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('SMTP_USER', 'user@example.com');
        $mail->Password = env('SMTP_PASS', 'secret');
        $mail->SMTPSecure = env('SMTP_SECURE', 'tls');
        $mail->Port = (int) env('SMTP_PORT', 587);

        $mail->setFrom(env('MAIL_FROM', 'no-reply@sentitevos.com'), 'Sentite Vos');
        $mail->addAddress($user['email'], $user['nombre']);
        $mail->Subject = 'Recuperación de contraseña';
        $mail->isHTML(true);
        $mail->Body = '<p>Hola ' . e($user['nombre']) . ',</p>' .
            '<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace:</p>' .
            '<p><a href="' . e($resetLink) . '">Restablecer contraseña</a></p>' .
            '<p>Si no solicitaste este cambio, ignora este correo.</p>';

        $mail->send();
    } catch (Exception $e) {
        log_error('Password reset email error: ' . $e->getMessage());
        // Continuar sin romper flujo
    }

    $conn->close();
    flash('success', 'Si el email existe, enviamos un enlace para resetear.');
    redirect('../forgot_password.php');
} catch (Throwable $e) {
    log_error('Request password reset error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../forgot_password.php');
}
