<?php
declare(strict_types=1);

$sql = file_get_contents(__DIR__ . '/../database/test_result.sql');

echo "Ocurrencias de la palabra 'areas' en el archivo limpio:\n";
preg_match_all('/areas/i', $sql, $matches, PREG_OFFSET_CAPTURE);
foreach ($matches[0] as $match) {
    $offset = $match[1];
    echo "  Offset $offset: " . trim(substr($sql, max(0, $offset - 40), 80)) . "\n";
}
