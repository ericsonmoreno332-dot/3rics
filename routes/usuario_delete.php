<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$token = $_GET['_csrf'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    exit('Token inválido');
}

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0 || $id === (int) $user['id']) {
    redirect(app_url('index.php?r=usuarios'));
}

$pdo = db();
$pdo->beginTransaction();
try {
    $st = $pdo->prepare('SELECT practicante_id FROM usuarios WHERE id = ?');
    $st->execute([$id]);
    $pid = $st->fetchColumn();

    $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
    
    if ($pid) {
        $pdo->prepare('DELETE FROM practicantes WHERE id = ?')->execute([$pid]);
    }
    
    $pdo->commit();
    flash('ok', 'Usuario y sus datos asociados eliminados');
} catch (Exception $e) {
    $pdo->rollBack();
    flash('err', 'Error al eliminar: ' . $e->getMessage());
}

redirect(app_url('index.php?r=usuarios'));
