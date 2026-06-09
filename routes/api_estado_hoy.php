<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);

$id  = (int) ($_GET['id'] ?? 0);
$pdo = db();

if ($id <= 0) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID inválido']);
    exit;
}

$abierta = asistencia_abierta_hoy($pdo, $id);
$cerrada  = asistencia_cerrada_hoy($pdo, $id);

header('Content-Type: application/json');
echo json_encode([
    'cerrada'      => (bool) $cerrada,
    'abierta'      => (bool) $abierta,
    'hora_entrada' => $abierta ? substr((string)($abierta['hora_entrada'] ?? ''), 0, 5) : null,
]);
exit;
