<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../reservas.php');
}

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión');
    redirect('../login.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../reservas.php');
}

$usuarioId = (int) $_SESSION['usuario_id'];
$servicio = trim($_POST['servicio'] ?? '');
$fecha = trim($_POST['fecha'] ?? '');
$hora = trim($_POST['hora'] ?? '');
$notas = trim($_POST['notas'] ?? '');

if ($servicio === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !preg_match('/^\d{2}:\d{2}:\d{2}$/', $hora)) {
    flash('error', 'Datos de reserva inválidos');
    redirect('../reservas.php');
}

try {
    $conn = conectarDB();
    // Evitar superposiciones: verificar ocupación
    $check = $conn->prepare("SELECT id FROM reservas WHERE fecha=? AND hora=? AND estado IN ('pendiente','confirmada')");
    $check->bind_param('ss', $fecha, $hora);
    $check->execute();
    $r = $check->get_result();
    if ($r && $r->num_rows > 0) {
        $check->close();
        $conn->close();
        flash('error', 'Ese horario ya está ocupado');
        redirect('../reservas.php');
    }
    $check->close();

    $stmt = $conn->prepare('INSERT INTO reservas (usuario_id, servicio, fecha, hora, estado, notas) VALUES (?, ?, ?, ?, "pendiente", ?)');
    $stmt->bind_param('issss', $usuarioId, $servicio, $fecha, $hora, $notas);
    $stmt->execute();
    $stmt->close();

    // Obtener datos del usuario para el email
    $userEmail = $_SESSION['usuario_email'] ?? null;
    $userName = $_SESSION['usuario_nombre'] ?? 'Cliente';

    // Construir enlace a Mis Reservas
    $appUrl = env('APP_URL', null);
    $reservasPath = '/mis-reservas.php';
    $reservasLink = $appUrl ? rtrim($appUrl, '/') . $reservasPath : ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost/sentitevos') . $reservasPath);

    // Enviar email al usuario
    if ($userEmail) {
        $fechaFmt = date('d/m/Y', strtotime($fecha));
        $horaFmt = date('H:i', strtotime($hora));
        $subject = 'Solicitud de turno recibida';
        $html = '<div style="font-family: Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#e5738a;">Hola ' . e($userName) . '</h2>'
            . '<p>Recibimos tu solicitud de turno. Estos son los detalles:</p>'
            . '<ul>'
            . '<li><strong>Servicio:</strong> ' . e($servicio) . '</li>'
            . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
            . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
            . '<li><strong>Estado:</strong> pendiente</li>'
            . '</ul>'
            . '<p>Te confirmaremos a la brevedad. Puedes ver el estado en: '
            . '<a style="color:#3a7ca5;" href="' . e($reservasLink) . '">Mis reservas</a></p>'
            . '</div>';
        $alt = 'Hola ' . $userName . ' - Turno solicitado: Servicio ' . $servicio . ', ' . $fechaFmt . ' ' . $horaFmt . '. Ver estado en ' . $reservasLink;
        send_mail_simple($userEmail, $userName, $subject, $html, $alt);
    }

    // Notificar a la dueña (incluye secundaria si está definida)
    $fechaFmt = date('d/m/Y', strtotime($fecha));
    $horaFmt = date('H:i', strtotime($hora));
    $usuarioInfo = e($userName) . ' (' . e($userEmail ?? '') . ')';
    $subjectOwner = 'Nueva solicitud de turno';
    $htmlOwner = '<div style="font-family: Arial, sans-serif; color:#333;">'
        . '<p>Se solicitó un nuevo turno:</p>'
        . '<ul>'
        . '<li><strong>Cliente:</strong> ' . $usuarioInfo . '</li>'
        . '<li><strong>Servicio:</strong> ' . e($servicio) . '</li>'
        . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
        . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
        . '<li><strong>Notas:</strong> ' . e($notas) . '</li>'
        . '</ul>'
        . '</div>';
    $altOwner = 'Nueva solicitud de turno: Cliente ' . $userName . ' (' . ($userEmail ?? '') . '), Servicio ' . $servicio . ', ' . $fechaFmt . ' ' . $horaFmt . '.';
    notify_owner($subjectOwner, $htmlOwner, $altOwner, true);

    $conn->close();
    flash('success', 'Reserva creada. Te confirmaremos a la brevedad.');
    redirect('../mis-reservas.php');
} catch (Throwable $e) {
    log_error('Crear reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo crear la reserva');
    redirect('../reservas.php');
}
