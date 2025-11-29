<?php
session_start();
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../login.php');
}

// CSRF
if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido, recarga la página.');
    redirect('../login.php');
}

$email = trim($_POST['email'] ?? '');
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Ingresa un email válido para reenviar verificación.');
    redirect('../login.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('SELECT id, nombre, email_verified, last_verification_sent_at FROM usuarios WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        flash('error', 'No existe un usuario con ese email.');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }
    $usuario = $result->fetch_assoc();

    if ((int) $usuario['email_verified'] === 1) {
        flash('success', 'Tu correo ya está verificado. Puedes iniciar sesión.');
        $stmt->close();
        $conn->close();
        redirect('../login.php');
    }

    // Rate-limit: 10 minutos de cooldown entre envíos
    if (!empty($usuario['last_verification_sent_at'])) {
        $last = strtotime($usuario['last_verification_sent_at']);
        if ($last !== false && (time() - $last) < (10 * 60)) {
            $mins = ceil(((10 * 60) - (time() - $last)) / 60);
            flash('error', 'Espera ' . $mins . ' minuto(s) antes de reenviar la verificación.');
            $stmt->close();
            $conn->close();
            redirect('../login.php');
        }
    }

    // Generar nuevo token y guardar
    $newToken = bin2hex(random_bytes(32));
    $upd = $conn->prepare('UPDATE usuarios SET email_verify_token = ?, last_verification_sent_at = NOW() WHERE id = ?');
    $upd->bind_param('si', $newToken, $usuario['id']);
    $upd->execute();
    $upd->close();

    // Enviar correo
    try {
        $mail = new PHPMailer(true);
        if (defined('APP_DEBUG') && APP_DEBUG) {
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        }
        $mail->isSMTP();
        $mail->Host = env('MAIL_HOST', 'smtp.gmail.com');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_USERNAME', 'asaquilano42@gmail.com');
        $mail->Password = env('MAIL_PASSWORD', 'xgqh fvit nnws yakn ');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = (int) env('MAIL_PORT', 465);

        $fromEmail = env('MAIL_FROM', $mail->Username);
        $fromName = env('MAIL_FROM_NAME', 'Sentite Vos');
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($email, $usuario['nombre']);

        $appUrl = env('APP_URL', null);
        $verifyPath = '/php/verify_email.php?token=' . urlencode($newToken) . '&email=' . urlencode($email);
        $verifyUrl = $appUrl ? rtrim($appUrl, '/') . $verifyPath : ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $verifyPath);

        $mail->isHTML(true);
        $mail->Subject = 'Reenvío de verificación - Sentite Vos';
        $mail->Body = '<div style="font-family: Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#e5738a;">Hola ' . e($usuario['nombre']) . '</h2>'
            . '<p>Te reenviamos el enlace para verificar tu cuenta:</p>'
            . '<p><a style="background:#3a7ca5;color:#fff;padding:10px 14px;border-radius:6px;text-decoration:none;" href="' . e($verifyUrl) . '">Verificar mi correo</a></p>'
            . '<p style="font-size:12px;color:#777;">Si no solicitaste esto, ignora el mensaje.</p>'
            . '</div>';
        $mail->AltBody = 'Hola ' . $usuario['nombre'] . ' - Verifica tu cuenta: ' . $verifyUrl;
        $mail->send();
    } catch (Throwable $me) {
        log_error('Reenvío verificación fallo: ' . $me->getMessage());
    }

    $stmt->close();
    $conn->close();
    flash('success', 'Te enviamos nuevamente el correo de verificación.');
    redirect('../login.php');
} catch (Throwable $e) {
    log_error('Resend verify error: ' . $e->getMessage());
    flash('error', 'Error interno, intenta más tarde');
    redirect('../login.php');
}
