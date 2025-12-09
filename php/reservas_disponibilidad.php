<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

$fecha = $_GET['fecha'] ?? '';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
    echo json_encode([]);
    exit;
}

try {
    $conn = conectarDB();
    $stmt = $conn->prepare("SELECT TIME_FORMAT(hora, '%H:%i:00') as h FROM reservas WHERE fecha = ? AND estado IN ('pendiente','confirmada')");
    $stmt->bind_param('s', $fecha);
    $stmt->execute();
    $res = $stmt->get_result();
    $horas = [];
    while ($row = $res->fetch_assoc()) {
        $horas[] = $row['h'];
    }
    $stmt->close();
    $conn->close();
    echo json_encode($horas);
} catch (Throwable $e) {
    log_error('Disponibilidad reservas error: ' . $e->getMessage());
    echo json_encode([]);
}
