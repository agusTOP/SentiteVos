<?php
// Configuración de la base de datos con soporte .env y logging
require_once __DIR__ . '/env.php';
require_once __DIR__ . '/helpers.php';

// Carga variables desde .env en la raíz del proyecto si existe
loadEnv(dirname(__DIR__));

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', '')); // XAMPP por defecto no tiene contraseña
define('DB_NAME', env('DB_NAME', 'sentitevos'));
define('DB_PORT', (int) env('DB_PORT', 3306));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true');

// Función para conectar a la base de datos
function conectarDB()
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
        $conn->set_charset('utf8mb4');
        return $conn;
    } catch (Throwable $e) {
        log_error('DB connection failed: ' . $e->getMessage());
        if (APP_DEBUG) {
            die('Error de conexión: ' . e($e->getMessage()));
        }
        die('Error interno de base de datos');
    }
}
?>