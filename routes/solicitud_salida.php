<?php

declare(strict_types=1);

$user = require_roles(['practicante']);
if (!is_post()) {
    redirect(app_url('index.php?r=mi_panel'));
}
verify_csrf();

$pid = (int) ($user['practicante_id'] ?? 0);
if ($pid <= 0) {
    redirect(app_url('index.php?r=login'));
}

$pdo = db();

$hora = trim((string) ($_POST['hora_propuesta'] ?? ''));
$asistenciaId = (int) ($_POST['asistencia_id'] ?? 0);

if ($asistenciaId <= 0) {
    flash('err', 'Asistencia inválida.');
    redirect(app_url('index.php?r=mi_panel'));
}

if (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
    flash('err', 'Hora inválida. Usa el formato HH:MM.');
    redirect(app_url('index.php?r=mi_panel'));
}

// Normalize to HH:MM:SS
if (strlen($hora) === 5) {
    $hora .= ':00';
}

$res = crear_solicitud_salida($pdo, $pid, $asistenciaId, $hora);
flash($res['ok'] ? 'ok' : 'err', $res['msg']);
redirect(app_url('index.php?r=mi_panel'));
