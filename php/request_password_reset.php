<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/env.php';
// Cargar .env para MAIL_*
loadEnv(dirname(__DIR__));
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

    // Construir URL absoluta usando APP_URL
    $appUrl = env('APP_URL', null);
    $resetPath = '/reset_password.php?token=' . urlencode($token);
    $resetLink = $appUrl ? rtrim($appUrl, '/') . $resetPath : ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $resetPath);

    $mail = new PHPMailer(true);
    try {
        // Debug opcional
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->isSMTP();
        // Usar el mismo esquema que register.php (MAIL_*)
        $mail->Host = env('MAIL_HOST', 'smtp.example.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME', 'defaultmail@gmail.com');
        $mail->Password = env('MAIL_PASSWORD', 'pass');
        // Por defecto SMTPS (465) como en register.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) env('MAIL_PORT', 465);
        // Opciones para entorno dev
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
            ]
        ];

        $fromEmail = env('MAIL_FROM', $mail->Username);
        $fromName = env('MAIL_FROM_NAME', 'Sentite Vos');
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($user['email'], $user['nombre']);
        $mail->Subject = 'Recuperación de contraseña';
        $mail->isHTML(true);
        $mail->Body = '<p>Hola ' . e($user['nombre']) . ',</p>' .
            '<p>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace:</p>' .
            '<p><a href="' . e($resetLink) . '">Restablecer contraseña</a></p>' .
            '<p>Si no solicitaste este cambio, ignora este correo.</p>';
        $mail->AltBody = 'Hola ' . $user['nombre'] . ' - Restablecer contraseña: ' . $resetLink;

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
