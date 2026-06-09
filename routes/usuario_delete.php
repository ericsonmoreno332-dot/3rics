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
$pdo->prepare("DELETE FROM usuarios WHERE id = ? AND rol IN ('admin','supervisor')")->execute([$id]);
flash('ok', 'Usuario eliminado');
redirect(app_url('index.php?r=usuarios'));
