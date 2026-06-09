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
$st = $pdo->prepare('SELECT estado, practicante_id FROM usuarios WHERE id = ? LIMIT 1');
$st->execute([$id]);
$row = $st->fetch();

if ($row) {
    $nuevoEstado = $row['estado'] === 'activo' ? 'inactivo' : 'activo';
    $pdo->prepare('UPDATE usuarios SET estado = ? WHERE id = ?')->execute([$nuevoEstado, $id]);
    
    // Si tiene un practicante asociado, también actualizamos su estado
    if (!empty($row['practicante_id'])) {
        $nuevoEstadoPracticante = $nuevoEstado === 'activo' ? 'activo' : 'suspendido';
        $pdo->prepare('UPDATE practicantes SET estado = ? WHERE id = ?')->execute([$nuevoEstadoPracticante, $row['practicante_id']]);
    }
    
    flash('ok', 'Estado del usuario actualizado a: ' . ucfirst($nuevoEstado));
}

redirect(app_url('index.php?r=usuarios'));
