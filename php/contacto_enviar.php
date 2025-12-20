<?php
session_start();
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Método inválido']);
    exit;
}

// Rate limit simple por sesión (60 segundos)
$now = time();
$last = (int) ($_SESSION['last_contact_at'] ?? 0);
if ($last && ($now - $last) < 60) {
    echo json_encode(['ok' => false, 'error' => 'Espera unos segundos antes de enviar otro mensaje.']);
    exit;
}

// Leer datos del formulario
$nombre = trim($_POST['nombre'] ?? '');
$email = trim($_POST['email'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');
$honeypot = trim($_POST['hp'] ?? '');

// Honeypot: si viene con contenido, ignorar
if ($honeypot !== '') {
    echo json_encode(['ok' => true, 'message' => 'Gracias por tu mensaje']);
    exit;
}

if ($nombre === '' || $email === '' || $mensaje === '') {
    echo json_encode(['ok' => false, 'error' => 'Completa todos los campos.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'error' => 'Email inválido.']);
    exit;
}
if (strlen($mensaje) > 2000) {
    echo json_encode(['ok' => false, 'error' => 'El mensaje es demasiado largo.']);
    exit;
}

try {
    // Notificar a la dueña (incluye secundaria si está definida)
    $subjectOwner = 'Nuevo mensaje de contacto';
    $htmlOwner = '<div style="font-family: Arial, sans-serif; color:#333;">'
        . '<p>Nuevo mensaje desde el formulario de contacto:</p>'
        . '<ul>'
        . '<li><strong>Nombre:</strong> ' . e($nombre) . '</li>'
        . '<li><strong>Email:</strong> ' . e($email) . '</li>'
        . '<li><strong>Mensaje:</strong><br>' . nl2br(e($mensaje)) . '</li>'
        . '</ul>'
        . '</div>';
    $altOwner = 'Contacto: ' . $nombre . ' <' . $email . '>\n\n' . $mensaje;
    notify_owner($subjectOwner, $htmlOwner, $altOwner, true);

    // Auto-respuesta al usuario
    $subjectUser = 'Recibimos tu mensaje - Sentite Vos';
    $htmlUser = '<div style="font-family: Arial, sans-serif; color:#333;">'
        . '<h2 style="color:#e5738a;">Hola ' . e($nombre) . '</h2>'
        . '<p>Recibimos tu mensaje y te responderemos a la brevedad.</p>'
        . '<p><em>Resumen:</em></p>'
        . '<blockquote style="border-left:3px solid #e5738a;padding-left:10px;color:#555;">' . nl2br(e($mensaje)) . '</blockquote>'
        . '<p>¡Gracias por contactarte!</p>'
        . '</div>';
    $altUser = 'Recibimos tu mensaje. Te responderemos pronto.';
    send_mail_simple($email, $nombre, $subjectUser, $htmlUser, $altUser);

    $_SESSION['last_contact_at'] = $now;
    echo json_encode(['ok' => true, 'message' => 'Mensaje enviado. Gracias por contactarte.']);
} catch (Throwable $e) {
    log_error('Contacto enviar error: ' . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'No se pudo enviar el mensaje. Intenta más tarde.']);
}
