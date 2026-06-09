<?php
declare(strict_types=1);

$sql = file_get_contents(__DIR__ . '/../database/local_sistema_practicantes.sql');

echo "--- SQL Alrededor de offset 866 ---\n";
echo substr($sql, 800, 400) . "\n";
