<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_roles(['admin', 'supervisor']);
$pdo = db();
$f = report_filters_from_request();
$rows = fetch_report_rows($pdo, $f);

if (try_send_xlsx_report($rows)) {
    exit;
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_asistencia.csv"');

$out = fopen('php://output', 'w');
if ($out === false) {
    exit('Error');
}
fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv($out, [
    'Fecha',
    'DNI',
    'Apellidos',
    'Nombres',
    'Carrera',
    'Área',
    'Institución',
    'Entrada',
    'Salida',
    'Horas_trabajadas',
    'Estado',
    'Observación',
], ',');
foreach ($rows as $r) {
    $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
    fputcsv($out, [
        (string) $r['fecha'],
        (string) $r['dni'],
        (string) $r['apellidos'],
        (string) $r['nombres'],
        (string) $r['carrera'],
        (string) ($r['area_nombre'] ?? ''),
        (string) ($r['institucion_nombre'] ?? ''),
        $r['hora_entrada'] ? substr((string) $r['hora_entrada'], 0, 8) : '',
        $r['hora_salida'] ? substr((string) $r['hora_salida'], 0, 8) : '',
        $ht ?? '',
        (string) $r['estado'],
        (string) ($r['observacion'] ?? ''),
    ], ',');
}
fclose($out);
