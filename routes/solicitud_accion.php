<?php

declare(strict_types=1);

$user = require_roles(['admin']);
if (!is_post()) {
    redirect(app_url('index.php?r=mensajes'));
}
verify_csrf();

$pdo = db();

$solicitudId = (int) ($_POST['solicitud_id'] ?? 0);
$accion = (string) ($_POST['accion'] ?? '');

if ($solicitudId <= 0) {
    flash('err', 'Solicitud inválida.');
    redirect(app_url('index.php?r=mensajes'));
}

if ($accion === 'aceptar') {
    $res = aceptar_solicitud($pdo, $solicitudId);
} elseif ($accion === 'rechazar') {
    $mensaje = trim((string) ($_POST['mensaje_rechazo'] ?? ''));
    $res = rechazar_solicitud($pdo, $solicitudId, $mensaje !== '' ? $mensaje : null);
} else {
    $res = ['ok' => false, 'msg' => 'Acción no válida.'];
}

flash($res['ok'] ? 'ok' : 'err', $res['msg']);
redirect(app_url('index.php?r=mensajes'));
