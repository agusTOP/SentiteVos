<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../mis-reservas.php');
}

if (empty($_SESSION['usuario_id'])) {
    flash('error', 'Debes iniciar sesión');
    redirect('../login.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../mis-reservas.php');
}

$id = (int) ($_POST['id'] ?? 0);
$userId = (int) $_SESSION['usuario_id'];

if ($id <= 0) {
    flash('error', 'Reserva inválida');
    redirect('../mis-reservas.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare("UPDATE reservas SET estado='cancelada' WHERE id=? AND usuario_id=?");
    $stmt->bind_param('ii', $id, $userId);
    $stmt->execute();
    $stmt->close();

    // Obtener datos de la reserva y del usuario para notificar
    $q = $conn->prepare('SELECT r.servicio, r.fecha, r.hora, u.nombre, u.email FROM reservas r JOIN usuarios u ON u.id = r.usuario_id WHERE r.id = ? AND r.usuario_id = ?');
    $q->bind_param('ii', $id, $userId);
    $q->execute();
    $res = $q->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    $q->close();

    $userEmail = $data['email'] ?? ($_SESSION['usuario_email'] ?? null);
    $userName = $data['nombre'] ?? ($_SESSION['usuario_nombre'] ?? 'Cliente');
    $servicio = $data['servicio'] ?? '';
    $fecha = $data['fecha'] ?? '';
    $hora = $data['hora'] ?? '';

    // Enviar confirmación al usuario
    if ($userEmail) {
        $fechaFmt = $fecha ? date('d/m/Y', strtotime($fecha)) : '';
        $horaFmt = $hora ? date('H:i', strtotime($hora)) : '';
        $subject = 'Cancelaste tu turno';
        $htmlUser = '<div style="font-family: Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#e5738a;">Hola ' . e($userName) . '</h2>'
            . '<p>Confirmamos la cancelación de tu turno.</p>'
            . '<ul>'
            . '<li><strong>Servicio:</strong> ' . e($servicio) . '</li>'
            . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
            . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
            . '</ul>'
            . '<p>Podés solicitar un nuevo turno cuando quieras.</p>'
            . '</div>';
        $altUser = 'Cancelaste tu turno: Servicio ' . $servicio . ', ' . $fechaFmt . ' ' . $horaFmt . '.';
        send_mail_simple($userEmail, $userName, $subject, $htmlUser, $altUser);
    }

    // Notificar a la dueña (incluye secundaria si está definida)
    $fechaFmt = $fecha ? date('d/m/Y', strtotime($fecha)) : '';
    $horaFmt = $hora ? date('H:i', strtotime($hora)) : '';
    $usuarioInfo = e($userName) . ' (' . e($userEmail ?? '') . ')';
    $subjectOwner = 'Turno cancelado por el cliente';
    $htmlOwner = '<div style="font-family: Arial, sans-serif; color:#333;">'
        . '<p>Un cliente canceló su turno:</p>'
        . '<ul>'
        . '<li><strong>Cliente:</strong> ' . $usuarioInfo . '</li>'
        . '<li><strong>Servicio:</strong> ' . e($servicio) . '</li>'
        . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
        . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
        . '</ul>'
        . '</div>';
    $altOwner = 'Turno cancelado: Cliente ' . $userName . ' (' . ($userEmail ?? '') . '), Servicio ' . $servicio . ', ' . $fechaFmt . ' ' . $horaFmt . '.';
    notify_owner($subjectOwner, $htmlOwner, $altOwner, true);

    $conn->close();
    flash('success', 'Reserva cancelada');
    redirect('../mis-reservas.php');
} catch (Throwable $e) {
    log_error('Cancelar reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo cancelar');
    redirect('../mis-reservas.php');
}
