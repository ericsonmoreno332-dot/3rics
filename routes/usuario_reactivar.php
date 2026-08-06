<?php

declare(strict_types=1);

$user = require_roles(['admin']);
$token = $_GET['_csrf'] ?? '';
if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
    http_response_code(419);
    exit('Token inválido');
}

if (!is_post()) {
    redirect(app_url('index.php?r=usuarios'));
}

$id = (int) ($_POST['id'] ?? 0);
$fecha_inicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
$fecha_fin    = trim((string) ($_POST['fecha_fin']    ?? ''));

if ($id <= 0) {
    flash('err', 'Usuario inválido');
    redirect(app_url('index.php?r=usuarios'));
}

if ($fecha_inicio === '' || $fecha_fin === '') {
    flash('err', 'Debes ingresar las fechas de inicio y fin de prácticas');
    redirect(app_url('index.php?r=usuarios'));
}

if ($fecha_inicio >= $fecha_fin) {
    flash('err', 'La fecha de inicio debe ser anterior a la fecha de fin');
    redirect(app_url('index.php?r=usuarios'));
}

$pdo = db();
$st = $pdo->prepare('SELECT u.id, u.estado, u.practicante_id FROM usuarios u WHERE u.id = ? AND u.rol = ? LIMIT 1');
$st->execute([$id, 'practicante']);
$row = $st->fetch();

if (!$row) {
    flash('err', 'Usuario no encontrado');
    redirect(app_url('index.php?r=usuarios'));
}

if ($row['estado'] === 'activo') {
    flash('err', 'El usuario ya está activo');
    redirect(app_url('index.php?r=usuarios'));
}

$practicante_id = (int) $row['practicante_id'];

try {
    $pdo->beginTransaction();

    // Reactivar usuario
    $pdo->prepare("UPDATE usuarios SET estado = 'activo' WHERE id = ?")
        ->execute([$id]);

    // Actualizar fechas y reactivar practicante
    if ($practicante_id > 0) {
        $pdo->prepare("UPDATE practicantes SET estado = 'activo', fecha_inicio = ?, fecha_fin = ? WHERE id = ?")
            ->execute([$fecha_inicio, $fecha_fin, $practicante_id]);
    }

    $pdo->commit();
    flash('ok', 'Practicante reactivado y fechas actualizadas correctamente ✅');
} catch (PDOException $e) {
    $pdo->rollBack();
    flash('err', 'Error al reactivar: ' . $e->getMessage());
}

redirect(app_url('index.php?r=usuarios'));
