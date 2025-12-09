<?php
// Retorna la lista de usuarios para el panel admin
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asegurar solo admin
if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
    http_response_code(403);
    return [];
}

$usuarios = [];
try {
    $conn = conectarDB();
    // Seleccionar campos relevantes; asume columna 'rol' existe
    $sql = 'SELECT id, nombre, email, IFNULL(rol, "cliente") AS rol, email_verified, fecha_registro FROM usuarios ORDER BY fecha_registro DESC';
    $res = $conn->query($sql);
    if ($res) {
        $usuarios = $res->fetch_all(MYSQLI_ASSOC);
    }
    $conn->close();
} catch (Throwable $e) {
    log_error('Admin list users error: ' . $e->getMessage());
    $usuarios = [];
}

return $usuarios;
