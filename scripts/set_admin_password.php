<?php

declare(strict_types=1);

/**
 * Fija la contraseña del administrador usando la misma conexión que .env
 *
 * Uso:
 *   php scripts/set_admin_password.php
 *   php scripts/set_admin_password.php MiNuevaClave
 *   php scripts/set_admin_password.php MiNuevaClave admin
 */

$pass = isset($argv[1]) ? (string) $argv[1] : 'admin123';
$userGuess = isset($argv[2]) ? (string) $argv[2] : 'admin';

$root = dirname(__DIR__);

require_once $root . '/includes/env.php';
load_env($root . '/.env');

$host = $_ENV['DB_HOST'] ?? '127.0.0.1';
$port = $_ENV['DB_PORT'] ?? '3306';
$name = $_ENV['DB_NAME'] ?? 'sistema_practicantes';
$user = $_ENV['DB_USER'] ?? 'root';
$pw = $_ENV['DB_PASS'] ?? '';

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
        $user,
        $pw,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    fwrite(STDERR, "No se puede conectar a MySQL.\nRevise DB_* en .env y que Apache/XAMPP MySQL esté encendido.\n");
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

$hash = password_hash($pass, PASSWORD_DEFAULT);
$needle = strtolower(trim($userGuess));

$st = $pdo->prepare('SELECT id, username, CHAR_LENGTH(password) AS len_pw FROM usuarios WHERE LOWER(TRIM(username)) = ?');
$st->execute([$needle]);
$found = $st->fetch(PDO::FETCH_ASSOC);

if ($found) {
    $pdo->prepare('UPDATE usuarios SET password = ? WHERE id = ?')->execute([$hash, (int) $found['id']]);
    $uname = $found['username'];
    fwrite(STDOUT, "OK — contraseña actualizada para usuario de BD «{$uname}» (id {$found['id']}). Hash anterior tenía {$found['len_pw']} caracteres.\n");
} else {
    $pdo->prepare('INSERT INTO usuarios (username, password, nombres, rol) VALUES (?,?,?,?)')
        ->execute([trim($userGuess), $hash, 'Administrador', 'admin']);
    $uname = trim($userGuess);
    fwrite(STDOUT, "OK — creado usuario «{$uname}» como admin.\n");
}

$check = $pdo->prepare('SELECT password FROM usuarios WHERE LOWER(TRIM(username)) = ?');
$check->execute([$needle]);
$dbHash = trim((string) $check->fetchColumn());
if (!password_verify($pass, $dbHash)) {
    fwrite(STDERR, "Error interno: la verificación bcrypt falló. Revise VARCHAR(255) en columna password.\n");
    exit(2);
}

fwrite(STDOUT, "\nEn el LOGIN use exactamente:\n  Usuario: {$uname}\n  Contraseña: {$pass}\n");
