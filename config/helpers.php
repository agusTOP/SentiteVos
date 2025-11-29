<?php
// Helper functions: flash messages, escaping, CSRF, redirects, logging
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function flash(string $key, string $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function flash_get(string $key): ?string
{
    if (!isset($_SESSION['flash'][$key]))
        return null;
    $val = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $val;
}

function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function ensureCsrfToken(): void
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function csrf_token(): string
{
    ensureCsrfToken();
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . csrf_token() . '">';
}

function verify_csrf(?string $token): bool
{
    ensureCsrfToken();
    return hash_equals($_SESSION['csrf_token'], (string) $token);
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function log_error(string $message): void
{
    $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'logs';
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
    $file = $dir . DIRECTORY_SEPARATOR . 'app.log';
    $date = date('Y-m-d H:i:s');
    @file_put_contents($file, "[$date] $message\n", FILE_APPEND);
}
?>