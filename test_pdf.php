<?php
require_once 'includes/bootstrap.php';
require_once 'includes/reports.php';
require_once 'includes/export_reports.php';

try {
    $pdo = db();
    $rows = fetch_report_rows($pdo, report_filters_from_request());
    $res = try_send_pdf_report($rows);
    var_dump($res);
} catch (Throwable $e) {
    echo $e->getMessage();
}
