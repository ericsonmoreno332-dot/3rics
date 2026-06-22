<?php

declare(strict_types=1);

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function app_url(string $path = ''): string
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? null;
    
    if ($host) {
        $base = $protocol . '://' . $host;
    } else {
        $base = rtrim(env_string('APP_URL', 'http://localhost:8000'), '/');
    }
    
    $path = ltrim($path, '/');
    return $path === '' ? $base : $base . '/' . $path;
}

function nombre_completo(string $nombres, string $apellidos): string
{
    return trim($nombres . ' ' . $apellidos);
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $t = $_POST['_csrf'] ?? '';
    if (!is_string($t) || !hash_equals(csrf_token(), $t)) {
        http_response_code(419);
        exit('Token CSRF inválido');
    }
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $m = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return is_string($m) ? $m : null;
}

/** Store all POST input in session so the form can repopulate after a validation redirect */
function flash_old(): void
{
    // Exclude password fields for security
    $data = $_POST;
    unset($data['password'], $data['password_confirm'], $data['password_admin'], $data['_csrf']);
    $_SESSION['_old'] = $data;
}

/** Retrieve a previously flashed input value (consumed on first call) */
function old(string $key, string $default = ''): string
{
    static $old = null;
    if ($old === null) {
        $old = $_SESSION['_old'] ?? [];
        unset($_SESSION['_old']);
    }
    $v = $old[$key] ?? null;
    return is_string($v) ? $v : $default;
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function is_post(): bool
{
    return request_method() === 'POST';
}

function input(string $key, ?string $default = null): ?string
{
    $v = $_POST[$key] ?? $_GET[$key] ?? null;
    if ($v === null || $v === '') {
        return $default;
    }
    return is_string($v) ? $v : $default;
}

function today_sql(): string
{
    return (new DateTimeImmutable('today'))->format('Y-m-d');
}

function now_time_sql(): string
{
    return (new DateTimeImmutable('now'))->format('H:i:s');
}
