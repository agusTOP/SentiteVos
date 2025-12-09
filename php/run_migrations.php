<?php
// Simple migration runner: applies SQL files in migrations/ in order
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/helpers.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Admin only if run via web
if (PHP_SAPI !== 'cli') {
    if (empty($_SESSION['usuario_id']) || ($_SESSION['usuario_rol'] ?? 'cliente') !== 'admin') {
        die('Acceso no autorizado');
    }
}

function runSql(mysqli $conn, string $sql): void
{
    // Split by semicolon respecting simple cases
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if ($stmt === '')
            continue;
        try {
            $conn->query($stmt);
        } catch (Throwable $e) {
            // Log and continue; some engines may not support IF NOT EXISTS in ALTER
            log_error('Migration statement failed: ' . $e->getMessage() . ' | SQL: ' . $stmt);
        }
    }
}

try {
    $conn = conectarDB();
    $conn->set_charset('utf8mb4');
    $migrationsDir = dirname(__DIR__) . '/migrations';
    if (!is_dir($migrationsDir)) {
        die('No migrations directory found');
    }
    $files = glob($migrationsDir . '/*.sql');
    sort($files);
    foreach ($files as $file) {
        $sql = file_get_contents($file);
        runSql($conn, $sql);
    }
    $conn->close();
    echo 'Migrations applied.';
} catch (Throwable $e) {
    log_error('Run migrations error: ' . $e->getMessage());
    echo 'Error applying migrations.';
}
