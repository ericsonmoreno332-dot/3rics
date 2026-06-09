<?php

declare(strict_types=1);

/**
 * @return array{desde: string, hasta: string, area_id: int, practicante_id: int, institucion_id: int}
 */
function report_filters_from_request(): array
{
    return [
        'desde' => trim((string) ($_GET['desde'] ?? $_POST['desde'] ?? '')),
        'hasta' => trim((string) ($_GET['hasta'] ?? $_POST['hasta'] ?? '')),
        'area_id' => (int) ($_GET['area_id'] ?? $_POST['area_id'] ?? 0),
        'practicante_id' => (int) ($_GET['practicante_id'] ?? $_POST['practicante_id'] ?? 0),
        'institucion_id' => (int) ($_GET['institucion_id'] ?? $_POST['institucion_id'] ?? 0),
    ];
}

/**
 * @param array{desde: string, hasta: string, area_id: int, practicante_id: int, institucion_id: int} $f
 * @return array{0: string, 1: array<int|string>}
 */
function build_report_query(array $f): array
{
    $where = ['1=1'];
    $params = [];

    if ($f['desde'] !== '') {
        $where[] = 'a.fecha >= ?';
        $params[] = $f['desde'];
    }
    if ($f['hasta'] !== '') {
        $where[] = 'a.fecha <= ?';
        $params[] = $f['hasta'];
    }
    if ($f['area_id'] > 0) {
        $where[] = 'p.area_id = ?';
        $params[] = $f['area_id'];
    }
    if ($f['practicante_id'] > 0) {
        $where[] = 'p.id = ?';
        $params[] = $f['practicante_id'];
    }
    if ($f['institucion_id'] > 0) {
        $where[] = 'p.institucion_id = ?';
        $params[] = $f['institucion_id'];
    }

    $sql = 'SELECT a.*, p.dni, p.nombres, p.apellidos, p.carrera,
            ar.nombre AS area_nombre, i.nombre AS institucion_nombre
            FROM asistencias a
            INNER JOIN practicantes p ON p.id = a.practicante_id
            LEFT JOIN areas ar ON ar.id = p.area_id
            LEFT JOIN instituciones i ON i.id = p.institucion_id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY a.fecha DESC, a.hora_entrada DESC';

    return [$sql, $params];
}

/**
 * @param array<string,mixed> $f
 */
function fetch_report_rows(PDO $pdo, array $f): array
{
    [$sql, $params] = build_report_query($f);
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}
