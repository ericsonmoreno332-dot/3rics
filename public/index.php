<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$r = $_GET['r'] ?? 'home';
$r = preg_replace('/[^a-z0-9_]/', '', (string) $r);

$routeFile = dirname(__DIR__) . '/routes/' . $r . '.php';
if (!is_file($routeFile)) {
    http_response_code(404);
    echo 'Ruta no encontrada';
    exit;
}

require $routeFile;
