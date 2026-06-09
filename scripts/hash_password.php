<?php

declare(strict_types=1);

/** Uso: php scripts/hash_password.php nueva_clave */
$pwd = $argv[1] ?? 'admin123';
echo password_hash($pwd, PASSWORD_DEFAULT), PHP_EOL;
