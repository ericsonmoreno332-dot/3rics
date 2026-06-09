<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_login();
$id = (int) ($_GET['id'] ?? 0);

$isAdminOrSup = in_array($user['rol'], ['admin', 'supervisor'], true);
$isOwnQr = ($user['rol'] === 'practicante' && $id === (int)($user['practicante_id'] ?? -1));

if (!$isAdminOrSup && !$isOwnQr) {
    http_response_code(403);
    exit('No autorizado');
}

if ($id <= 0) {
    http_response_code(400);
    exit('ID inválido');
}

$p = practicante_por_id(db(), $id);
if (!$p) {
    http_response_code(404);
    exit('No encontrado');
}

$payload = 'REGIS|' . $p['dni'];

if (class_exists(\Endroid\QrCode\Builder\Builder::class)) {
    try {
        $result = \Endroid\QrCode\Builder\Builder::create()
            ->writer(new \Endroid\QrCode\Writer\PngWriter())
            ->data($payload)
            ->size(280)
            ->margin(8)
            ->build();

        header('Content-Type: ' . $result->getMimeType());
        header('Content-Disposition: inline; filename="qr_' . preg_replace('/\D/', '', (string) $p['dni']) . '.png"');
        echo $result->getString();
        exit;
    } catch (Throwable) {
        // fallback externo
    }
}

$base = env_string(
    'QR_IMAGE_SERVICE',
    'https://api.qrserver.com/v1/create-qr-code/?size=280x280&data='
);
$url = $base . rawurlencode($payload);

header('Location: ' . $url, true, 302);
exit;
