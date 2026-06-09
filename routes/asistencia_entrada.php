<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
if (!is_post()) {
    redirect(app_url('index.php?r=inicio'));
}
verify_csrf();

$pdo = db();
$obs = trim((string) ($_POST['observacion'] ?? ''));
$obsOut = $obs !== '' ? $obs : null;

$metodo = (string) ($_POST['metodo'] ?? 'manual');
if (!in_array($metodo, ['manual', 'qr', 'dni', 'geo'], true)) {
    $metodo = 'manual';
}

$lat = isset($_POST['ajax_lat']) && $_POST['ajax_lat'] !== '' ? (float) $_POST['ajax_lat'] : null;
$lng = isset($_POST['ajax_lng']) && $_POST['ajax_lng'] !== '' ? (float) $_POST['ajax_lng'] : null;
if (isset($_POST['usar_geo']) && $lat !== null && $lng !== null) {
    $metodo = 'geo';
}

$isQr = ($_POST['metodo'] ?? '') === 'qr';
$redirectUrl = $isQr ? app_url('index.php?r=escaner') : app_url('index.php?r=inicio');

$pid = (int) ($_POST['practicante_id'] ?? 0);
if ($pid <= 0) {
    $dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));
    if (strlen($dni) !== 8) {
        flash('err', 'DNI inválido');
        redirect($redirectUrl);
    }
    $p = practicante_por_dni($pdo, $dni);
    if (!$p) {
        flash('err', 'Practicante no encontrado');
        redirect($redirectUrl);
    }
    $pid = (int) $p['id'];
    if ($metodo !== 'qr') {
        $metodo = 'dni';
    }
}

$res = registrar_entrada($pdo, $pid, $metodo, $obsOut, $lat, $lng);
flash($res['ok'] ? 'ok' : 'err', $res['msg']);
redirect($redirectUrl);
