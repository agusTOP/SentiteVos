<?php
// Carga automática de Composer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Creamos una instancia nueva
$mail = new PHPMailer(true);

try {
    // ---------------------------------------------------------
    // 1. CONFIGURACIÓN DEL SERVIDOR (¡Edita esto!)
    // ---------------------------------------------------------

    // Habilitar salida de depuración detallada (0 = off, 2 = cliente y servidor)
    // Esto te mostrará todo el "diálogo" entre tu PHP y Gmail.
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;

    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // TU CORREO DE GMAIL (el que envía)
    $mail->Username = 'asaquilano42@gmail.com';

    // TU CONTRASEÑA DE APLICACIÓN (La de 16 letras, NO tu clave de Gmail normal)
    $mail->Password = 'xgqh fvit nnws yakn ';

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // ---------------------------------------------------------
    // 2. DESTINATARIOS
    // ---------------------------------------------------------

    // Quien lo envía (debe coincidir con el Username de arriba o Gmail lo reescribe)
    $mail->setFrom('asaquilano42@gmail.com', 'Prueba PHPMailer');

    // A quién le llega (pon tu propio correo para probarte a ti mismo)
    $mail->addAddress('asaquilano42@gmail.com', 'Yo Mismo');

    // ---------------------------------------------------------
    // 3. CONTENIDO
    // ---------------------------------------------------------
    $mail->isHTML(true);
    $mail->Subject = 'Test de PHPMailer - Proyecto Final';
    $mail->Body = '<h1>¡Funciona!</h1><p>Si lees esto, la configuración SMTP es correcta.</p>';
    $mail->AltBody = 'Si lees esto, la configuración SMTP es correcta (texto plano).';

    $mail->send();
    echo 'El mensaje se envió correctamente';

} catch (Exception $e) {
    echo "Hubo un error al enviar el mensaje. Mailer Error: {$mail->ErrorInfo}";
}