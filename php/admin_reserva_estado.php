<?php
session_start();
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';
require_once dirname(__DIR__) . '/config/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('../admin/dashboard.php');
}

if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    flash('error', 'Acceso no autorizado');
    redirect('../admin/dashboard.php');
}

if (!verify_csrf($_POST['_token'] ?? null)) {
    flash('error', 'Token CSRF inválido');
    redirect('../admin/dashboard.php');
}

$id = (int) ($_POST['id'] ?? 0);
$estado = $_POST['estado'] ?? '';
if ($id <= 0 || !in_array($estado, ['pendiente', 'confirmada', 'cancelada'], true)) {
    flash('error', 'Datos inválidos');
    redirect('../admin/dashboard.php');
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare('UPDATE reservas SET estado = ? WHERE id = ?');
    $stmt->bind_param('si', $estado, $id);
    $stmt->execute();
    $stmt->close();

    // Obtener datos de la reserva y usuario para notificar
    $q = $conn->prepare('SELECT r.servicio, r.fecha, r.hora, r.estado, u.nombre, u.email FROM reservas r JOIN usuarios u ON u.id = r.usuario_id WHERE r.id = ?');
    $q->bind_param('i', $id);
    $q->execute();
    $res = $q->get_result();
    $data = $res ? $res->fetch_assoc() : null;
    $q->close();

    if ($data && !empty($data['email'])) {
        $fechaFmt = date('d/m/Y', strtotime($data['fecha']));
        $horaFmt = date('H:i', strtotime($data['hora']));
        $estadoNuevo = $data['estado'];

        // Asunto según estado
        $subject = 'Actualización de tu reserva';
        if ($estadoNuevo === 'confirmada') {
            $subject = 'Tu turno fue confirmado';
        } elseif ($estadoNuevo === 'cancelada') {
            $subject = 'Tu turno fue cancelado';
        }
        $htmlUser = '<div style="font-family: Arial, sans-serif; color:#333;">'
            . '<h2 style="color:#e5738a;">Hola ' . e($data['nombre']) . '</h2>'
            . '<p>Tu reserva fue actualizada al estado: <strong>' . e($estadoNuevo) . '</strong>.</p>'
            . '<ul>'
            . '<li><strong>Servicio:</strong> ' . e($data['servicio']) . '</li>'
            . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
            . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
            . '</ul>'
            . '<p>Gracias por elegirnos.</p>'
            . '</div>';
        $altUser = 'Tu reserva cambió a "' . $estadoNuevo . '". Servicio ' . $data['servicio'] . ', ' . $fechaFmt . ' ' . $horaFmt . '.';
        send_mail_simple($data['email'], $data['nombre'], $subject, $htmlUser, $altUser);

        // Notificar a la dueña sobre el cambio de estado (incluye secundaria)
        $subjectOwner = 'Estado de turno actualizado';
        $htmlOwner = '<div style="font-family: Arial, sans-serif; color:#333;">'
            . '<p>El turno cambió de estado:</p>'
            . '<ul>'
            . '<li><strong>Cliente:</strong> ' . e($data['nombre']) . ' (' . e($data['email']) . ')</li>'
            . '<li><strong>Servicio:</strong> ' . e($data['servicio']) . '</li>'
            . '<li><strong>Fecha:</strong> ' . e($fechaFmt) . '</li>'
            . '<li><strong>Hora:</strong> ' . e($horaFmt) . '</li>'
            . '<li><strong>Nuevo estado:</strong> ' . e($estadoNuevo) . '</li>'
            . '</ul>'
            . '</div>';
        $altOwner = 'Turno actualizado: Cliente ' . $data['nombre'] . ' (' . $data['email'] . '), Servicio ' . $data['servicio'] . ', ' . $fechaFmt . ' ' . $horaFmt . '. Estado: ' . $estadoNuevo . '.';
        notify_owner($subjectOwner, $htmlOwner, $altOwner, true);
    }

    $conn->close();
    flash('success', 'Reserva actualizada');
} catch (Throwable $e) {
    log_error('Admin actualizar reserva error: ' . $e->getMessage());
    flash('error', 'No se pudo actualizar');
}

redirect('../admin/dashboard.php');
