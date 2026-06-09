<?php

declare(strict_types=1);

// Seguridad de la sesión (prevenir robo de sesión por XSS y ataques CSRF)
ini_set('session.use_only_cookies', '1');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

// Cabeceras HTTP de Seguridad
if (!headers_sent()) {
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
}

$root = dirname(__DIR__);
require_once $root . '/includes/env.php';
load_env($root . '/.env');

if (is_file($root . '/vendor/autoload.php')) {
    require_once $root . '/vendor/autoload.php';
}

require_once $root . '/includes/config.php';

date_default_timezone_set(env_string('TIMEZONE', 'America/Lima') ?? 'America/Lima');

require_once $root . '/includes/db.php';
require_once $root . '/includes/helpers.php';
require_once $root . '/includes/business.php';
require_once $root . '/includes/auth.php';
require_once $root . '/includes/reports.php';
require_once $root . '/includes/export_reports.php';
require_once $root . '/includes/dni_api.php';
