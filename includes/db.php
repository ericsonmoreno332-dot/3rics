<?php

declare(strict_types=1);

/** @var PDO|null */
$pdo = null;

function db(): PDO
{
    global $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = env_string('DB_HOST', '127.0.0.1');
    $port = env_string('DB_PORT', '3306');
    $name = env_string('DB_NAME', 'sistema_practicantes');
    $user = env_string('DB_USER', 'root');
    $pass = env_string('DB_PASS', '') ?? '';

    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
