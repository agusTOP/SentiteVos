<?php
session_start();
header('Content-Type: application/json');
$response = ['logged_in' => false];
if (!empty($_SESSION['usuario_id'])) {
    $response['logged_in'] = true;
    $response['nombre'] = $_SESSION['usuario_nombre'] ?? null;
    $response['email'] = $_SESSION['usuario_email'] ?? null;
}
// Allow simple caching prevention
header('Cache-Control: no-store');
echo json_encode($response);
