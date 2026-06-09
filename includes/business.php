<?php

declare(strict_types=1);

function tardanza_limite_hora(): string
{
    return env_string('TARDANZA_HORA', '08:00:00') ?? '08:00:00';
}

function es_tardanza(string $horaEntrada): bool
{
    $lim = tardanza_limite_hora();
    return $horaEntrada > $lim;
}

/**
 * @return array{0: string, 1: string} estado asistencia fila y estado enum
 */
function estado_asistencia_desde_hora(string $horaEntrada): array
{
    if (es_tardanza($horaEntrada)) {
        return ['tardanza', 'tardanza'];
    }
    return ['presente', 'presente'];
}

function horas_trabajadas(?string $entrada, ?string $salida): ?string
{
    if ($entrada === null || $salida === null) {
        return null;
    }
    $e = DateTimeImmutable::createFromFormat('H:i:s', $entrada);
    $s = DateTimeImmutable::createFromFormat('H:i:s', $salida);
    if (!$e || !$s) {
        return null;
    }
    $diff = $e->diff($s);
    $h = $diff->h + $diff->days * 24;
    return sprintf('%02d:%02d:%02d', $h, $diff->i, $diff->s);
}

/** Abierta: entrada sin salida el mismo día */
function asistencia_abierta_hoy(PDO $pdo, int $practicanteId): ?array
{
    $st = $pdo->prepare(
        'SELECT * FROM asistencias WHERE practicante_id = ? AND fecha = CURDATE() AND hora_entrada IS NOT NULL AND hora_salida IS NULL LIMIT 1'
    );
    $st->execute([$practicanteId]);
    $r = $st->fetch();
    return $r ?: null;
}

/** Ya cerró hoy (entrada y salida) */
function asistencia_cerrada_hoy(PDO $pdo, int $practicanteId): bool
{
    $st = $pdo->prepare(
        'SELECT 1 FROM asistencias WHERE practicante_id = ? AND fecha = CURDATE() AND hora_entrada IS NOT NULL AND hora_salida IS NOT NULL LIMIT 1'
    );
    $st->execute([$practicanteId]);
    return (bool) $st->fetchColumn();
}

function practicante_activo(array $p): bool
{
    return ($p['estado'] ?? '') === 'activo';
}

/** Auto finalizar practicantes cuya fecha_fin < hoy */
function actualizar_practicantes_vencidos(PDO $pdo): int
{
    $st = $pdo->query(
        "UPDATE practicantes SET estado = 'finalizado' WHERE estado = 'activo' AND fecha_fin IS NOT NULL AND fecha_fin < CURDATE()"
    );
    return $st->rowCount();
}

function practicante_por_dni(PDO $pdo, string $dni): ?array
{
    $st = $pdo->prepare('SELECT p.*, a.nombre AS area_nombre, i.nombre AS institucion_nombre FROM practicantes p LEFT JOIN areas a ON a.id = p.area_id LEFT JOIN instituciones i ON i.id = p.institucion_id WHERE p.dni = ? LIMIT 1');
    $st->execute([$dni]);
    $r = $st->fetch();
    return $r ?: null;
}

function practicante_por_id(PDO $pdo, int $id): ?array
{
    $st = $pdo->prepare('SELECT p.*, a.nombre AS area_nombre, i.nombre AS institucion_nombre FROM practicantes p LEFT JOIN areas a ON a.id = p.area_id LEFT JOIN instituciones i ON i.id = p.institucion_id WHERE p.id = ? LIMIT 1');
    $st->execute([$id]);
    $r = $st->fetch();
    return $r ?: null;
}

/**
 * @param 'manual'|'qr'|'dni'|'geo' $metodo
 * @return array{ok:bool,msg:string}
 */
function registrar_entrada(PDO $pdo, int $practicanteId, string $metodo, ?string $obs, ?float $lat, ?float $lng): array
{
    $p = practicante_por_id($pdo, $practicanteId);
    if (!$p || !practicante_activo($p)) {
        return ['ok' => false, 'msg' => 'Practicante no encontrado o no está activo'];
    }

    $st = $pdo->prepare('SELECT id, hora_entrada FROM asistencias WHERE practicante_id = ? AND fecha = CURDATE() LIMIT 1');
    $st->execute([$practicanteId]);
    $row = $st->fetch();

    if ($row && $row['hora_entrada'] !== null) {
        // Check if also has exit
        $stFull = $pdo->prepare('SELECT hora_salida FROM asistencias WHERE practicante_id = ? AND fecha = CURDATE() AND hora_entrada IS NOT NULL AND hora_salida IS NOT NULL LIMIT 1');
        $stFull->execute([$practicanteId]);
        if ($stFull->fetchColumn()) {
            return ['ok' => false, 'msg' => 'Ya registraste tu entrada y salida por hoy. Espera a mañana. Cualquier consulta, pregunta al administrador.'];
        }
        return ['ok' => false, 'msg' => 'Ya registraste tu entrada por hoy. Si necesitas registrar la salida, usa el botón "Registrar Salida".'];
    }

    $hora = now_time_sql();
    [$estadoEnum] = estado_asistencia_desde_hora($hora);

    $obs = $obs !== null && $obs !== '' ? $obs : null;

    if ($row) {
        $sets = 'hora_entrada = ?, estado = ?, observacion = ?';
        $params = [$hora, $estadoEnum, $obs];

        if (column_exists($pdo, 'asistencias', 'metodo_entrada')) {
            $sets .= ', metodo_entrada = ?';
            $params[] = $metodo;
        }
        if (column_exists($pdo, 'asistencias', 'lat_entrada')) {
            $sets .= ', lat_entrada = ?, lng_entrada = ?';
            $params[] = $lat;
            $params[] = $lng;
        }
        $params[] = $row['id'];

        $pdo->prepare("UPDATE asistencias SET $sets WHERE id = ?")->execute($params);
    } else {
        $cols = 'practicante_id, fecha, hora_entrada, hora_salida, estado, observacion';
        $vals = '?,?,?,?,?,?';
        $params = [$practicanteId, today_sql(), $hora, null, $estadoEnum, $obs];

        $extraCols = '';
        if (column_exists($pdo, 'asistencias', 'metodo_entrada')) {
            $extraCols .= ', metodo_entrada';
            $vals .= ',?';
            $params[] = $metodo;
        }
        if (column_exists($pdo, 'asistencias', 'lat_entrada')) {
            $extraCols .= ', lat_entrada, lng_entrada';
            $vals .= ',?,?';
            $params[] = $lat;
            $params[] = $lng;
        }

        $pdo->prepare("INSERT INTO asistencias ($cols $extraCols) VALUES ($vals)")->execute($params);
    }

    return ['ok' => true, 'msg' => $estadoEnum === 'tardanza' ? 'Entrada registrada (tardanza)' : 'Entrada registrada'];
}

/**
 * @param 'manual'|'qr'|'dni'|'geo' $metodo
 * @return array{ok:bool,msg:string}
 */
function registrar_salida(PDO $pdo, int $practicanteId, string $metodo, ?string $obs, ?float $lat, ?float $lng): array
{
    if (asistencia_cerrada_hoy($pdo, $practicanteId)) {
        return ['ok' => false, 'msg' => 'Ya registraste tu entrada y salida por hoy. Espera a mañana. Cualquier consulta, pregunta al administrador.'];
    }

    $abierta = asistencia_abierta_hoy($pdo, $practicanteId);
    if (!$abierta) {
        return ['ok' => false, 'msg' => 'Primero registrar entrada'];
    }

    $hora = now_time_sql();
    $obsVal = trim((string) ($abierta['observacion'] ?? '') . ($obs !== null && $obs !== '' ? ' ' . $obs : ''));

    $sets = ['hora_salida = ?'];
    $params = [$hora];

    if ($obsVal !== '') {
        $sets[] = 'observacion = ?';
        $params[] = $obsVal;
    }

    if (column_exists($pdo, 'asistencias', 'metodo_salida')) {
        $sets[] = 'metodo_salida = ?';
        $params[] = $metodo;
    }

    if (column_exists($pdo, 'asistencias', 'lat_salida')) {
        $sets[] = 'lat_salida = ?';
        $sets[] = 'lng_salida = ?';
        $params[] = $lat;
        $params[] = $lng;
    }

    $params[] = $abierta['id'];

    $sql = 'UPDATE asistencias SET ' . implode(', ', $sets) . ' WHERE id = ?';
    $pdo->prepare($sql)->execute($params);

    return ['ok' => true, 'msg' => 'Salida registrada'];
}

function column_exists(PDO $pdo, string $table, string $column): bool
{
    static $cache = [];
    $key = "$table.$column";
    if (array_key_exists($key, $cache)) {
        return $cache[$key];
    }
    $db = env_string('DB_NAME', 'sistema_practicantes');
    $st = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $st->execute([$db, $table, $column]);
    $cache[$key] = (bool) $st->fetchColumn();
    return $cache[$key];
}
