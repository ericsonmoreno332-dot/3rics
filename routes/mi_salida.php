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
$obs = trim((string) ($_POST['observacion'] ?? ''));
$res = registrar_salida($pdo, $pid, 'manual', $obs !== '' ? $obs : null, null, null);
flash($res['ok'] ? 'ok' : 'err', $res['msg']);
redirect(app_url('index.php?r=mi_panel'));
