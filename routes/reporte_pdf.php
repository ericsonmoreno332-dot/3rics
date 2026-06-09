<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/bootstrap.php';

$user = require_roles(['admin', 'supervisor']);
$pdo = db();
$f = report_filters_from_request();
$rows = fetch_report_rows($pdo, $f);

if (try_send_pdf_report($rows)) {
    exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('Content-Disposition: attachment; filename="reporte_asistencia.html"');

echo '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><title>Reporte de asistencias</title>';
echo '<style>body{font-family:Inter,Segoe UI,sans-serif;font-size:12px;color:#0f172a;} table{border-collapse:collapse;width:100%;margin-top:20px;font-size:11px;} th,td{border:1px solid #cbd5e1;padding:8px;text-align:left;} th{background:#1e40af;color:#fff;text-transform:uppercase;} tr:nth-child(even){background:#f8fafc;} h1{font-size:24px;color:#1e3a8a;margin:0;padding-top:10px;} .header{border-bottom:2px solid #1e40af;padding-bottom:10px;margin-bottom:20px;} .logo{height:60px;float:left;margin-right:15px;}</style></head><body>';
echo '<p style="color:#ef4444;font-size:11px;"><strong>Nota:</strong> no se pudo generar PDF nativo (instale extensiones PHP gd/zip y ejecute <code>composer install</code>). Use «Imprimir» en el navegador y guarde como PDF.</p>';

$logoPath = dirname(__DIR__) . '/public/municipio.jpg';
$logoHtml = '';
if (file_exists($logoPath)) {
    $logoData = base64_encode(file_get_contents($logoPath));
    $logoHtml = '<img src="data:image/jpeg;base64,' . $logoData . '" class="logo" alt="Logo">';
}

echo '<div class="header">';
echo $logoHtml;
echo '<h1>Reporte de asistencias</h1>';
echo '<p style="color:#64748b;margin:5px 0 0 0;">Generado: ' . date('d/m/Y H:i') . '</p>';
echo '<div style="clear:both;"></div></div>';

echo '<table><thead><tr><th>Fecha</th><th>DNI</th><th>Nombre</th><th>Área</th><th>Institución</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th><th>Observación</th></tr></thead><tbody>';
foreach ($rows as $r) {
    $ht = horas_trabajadas($r['hora_entrada'] ?? null, $r['hora_salida'] ?? null);
    echo '<tr>';
    echo '<td>' . htmlspecialchars((string) $r['fecha']) . '</td>';
    echo '<td>' . htmlspecialchars((string) $r['dni']) . '</td>';
    echo '<td>' . htmlspecialchars(nombre_completo($r['nombres'], $r['apellidos'])) . '</td>';
    echo '<td>' . htmlspecialchars((string) ($r['area_nombre'] ?? '')) . '</td>';
    echo '<td>' . htmlspecialchars((string) ($r['institucion_nombre'] ?? '')) . '</td>';
    echo '<td>' . htmlspecialchars($r['hora_entrada'] ? substr((string) $r['hora_entrada'], 0, 5) : '') . '</td>';
    echo '<td>' . htmlspecialchars($r['hora_salida'] ? substr((string) $r['hora_salida'], 0, 5) : '') . '</td>';
    echo '<td>' . htmlspecialchars($ht ? substr($ht, 0, 5) : '') . '</td>';
    echo '<td>' . htmlspecialchars((string) $r['estado']) . '</td>';
    echo '<td>' . htmlspecialchars((string) ($r['observacion'] ?? '')) . '</td>';
    echo '</tr>';
}
echo '</tbody></table></body></html>';
