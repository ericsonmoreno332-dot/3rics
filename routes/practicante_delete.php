<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
$token = $_GET['_csrf'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    exit('Token inválido');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect(app_url('index.php?r=practicantes'));
}

$pdo = db();
$pdo->beginTransaction();
try {
    // Primero eliminar la cuenta de usuario asociada, si existe
    $pdo->prepare('DELETE FROM usuarios WHERE practicante_id = ?')->execute([$id]);

    // Luego eliminar al practicante (asistencias asociadas se eliminan automáticamente por ON DELETE CASCADE en la BD)
    $pdo->prepare('DELETE FROM practicantes WHERE id = ?')->execute([$id]);

    $pdo->commit();
    flash('ok', 'Practicante y sus datos asociados eliminados completamente');
} catch (Exception $e) {
    $pdo->rollBack();
    flash('err', 'Error al eliminar el practicante: ' . $e->getMessage());
}

redirect(app_url('index.php?r=practicantes'));
