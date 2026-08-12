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

// ═══════════════════════════════════════════════════════════════
// Solicitudes de salida pendiente
// ═══════════════════════════════════════════════════════════════

/** Crear solicitud de salida (practicante propone hora) */
function crear_solicitud_salida(PDO $pdo, int $practicanteId, int $asistenciaId, string $horaPropuesta): array
{
    // Verificar que la asistencia existe, pertenece al practicante y es de un día anterior sin salida
    $st = $pdo->prepare(
        "SELECT id, hora_entrada FROM asistencias WHERE id = ? AND practicante_id = ? AND hora_salida IS NULL AND fecha < CURDATE() LIMIT 1"
    );
    $st->execute([$asistenciaId, $practicanteId]);
    $abierta = $st->fetch();

    if (!$abierta) {
        return ['ok' => false, 'msg' => 'Asistencia inválida o no corresponde a un día anterior.'];
    }

    if ($horaPropuesta < substr((string)$abierta['hora_entrada'], 0, 5)) {
        return ['ok' => false, 'msg' => 'La hora de salida (' . substr((string)$horaPropuesta, 0, 5) . ') no puede ser anterior a la hora de entrada (' . substr((string)$abierta['hora_entrada'], 0, 5) . ').'];
    }

    // Check if already has a pending solicitud for this asistencia
    $st = $pdo->prepare(
        'SELECT id FROM solicitudes_salida WHERE asistencia_id = ? AND estado = ? LIMIT 1'
    );
    $st->execute([$abierta['id'], 'pendiente']);
    if ($st->fetchColumn()) {
        return ['ok' => false, 'msg' => 'Ya tienes una solicitud pendiente para esta asistencia.'];
    }

    // Invalidate any previous rejected solicitud for this asistencia
    $pdo->prepare(
        "UPDATE solicitudes_salida SET estado = 'rechazada' WHERE asistencia_id = ? AND estado = 'rechazada'"
    )->execute([$abierta['id']]);

    $pdo->prepare(
        'INSERT INTO solicitudes_salida (asistencia_id, practicante_id, hora_propuesta, estado) VALUES (?, ?, ?, ?)'
    )->execute([$abierta['id'], $practicanteId, $horaPropuesta, 'pendiente']);

    return ['ok' => true, 'msg' => 'Solicitud enviada. El administrador revisará tu hora de salida.'];
}

/** Contar solicitudes pendientes (para badge) */
function contar_solicitudes_pendientes(PDO $pdo): int
{
    $st = $pdo->query("SELECT COUNT(*) FROM solicitudes_salida WHERE estado = 'pendiente'");
    return (int) $st->fetchColumn();
}

/** Listar solicitudes pendientes con datos del practicante */
function solicitudes_pendientes(PDO $pdo): array
{
    $st = $pdo->query(
        "SELECT s.*, a.fecha, a.hora_entrada,
                p.nombres, p.apellidos, p.dni
         FROM solicitudes_salida s
         JOIN asistencias a ON a.id = s.asistencia_id
         JOIN practicantes p ON p.id = s.practicante_id
         WHERE s.estado = 'pendiente'
         ORDER BY s.created_at ASC"
    );
    return $st->fetchAll();
}

/** Listar todas las solicitudes (historial) */
function solicitudes_historial(PDO $pdo, int $limit = 50): array
{
    $limit = (int) $limit;
    $st = $pdo->prepare(
        "SELECT s.*, a.fecha, a.hora_entrada,
                p.nombres, p.apellidos, p.dni
         FROM solicitudes_salida s
         JOIN asistencias a ON a.id = s.asistencia_id
         JOIN practicantes p ON p.id = s.practicante_id
         ORDER BY s.created_at DESC
         LIMIT $limit"
    );
    $st->execute();
    return $st->fetchAll();
}

/** Aceptar solicitud: registra la hora propuesta como hora_salida en asistencias */
function aceptar_solicitud(PDO $pdo, int $solicitudId): array
{
    $st = $pdo->prepare('SELECT * FROM solicitudes_salida WHERE id = ? LIMIT 1');
    $st->execute([$solicitudId]);
    $sol = $st->fetch();

    if (!$sol) {
        return ['ok' => false, 'msg' => 'Solicitud no encontrada.'];
    }
    if ($sol['estado'] !== 'pendiente') {
        return ['ok' => false, 'msg' => 'Esta solicitud ya fue procesada.'];
    }

    // Update asistencia with the proposed exit time
    $pdo->prepare(
        'UPDATE asistencias SET hora_salida = ?, metodo_salida = ? WHERE id = ?'
    )->execute([$sol['hora_propuesta'], 'manual', $sol['asistencia_id']]);

    // Mark solicitud as accepted
    $pdo->prepare(
        "UPDATE solicitudes_salida SET estado = 'aceptada' WHERE id = ?"
    )->execute([$solicitudId]);

    return ['ok' => true, 'msg' => 'Solicitud aceptada. Salida registrada.'];
}

/** Rechazar solicitud */
function rechazar_solicitud(PDO $pdo, int $solicitudId, ?string $mensaje): array
{
    $st = $pdo->prepare('SELECT * FROM solicitudes_salida WHERE id = ? LIMIT 1');
    $st->execute([$solicitudId]);
    $sol = $st->fetch();

    if (!$sol) {
        return ['ok' => false, 'msg' => 'Solicitud no encontrada.'];
    }
    if ($sol['estado'] !== 'pendiente') {
        return ['ok' => false, 'msg' => 'Esta solicitud ya fue procesada.'];
    }

    $pdo->prepare(
        "UPDATE solicitudes_salida SET estado = 'rechazada', mensaje_rechazo = ? WHERE id = ?"
    )->execute([$mensaje, $solicitudId]);

    return ['ok' => true, 'msg' => 'Solicitud rechazada.'];
}

/** Obtener asistencias de días anteriores que no tienen salida, junto con su solicitud activa (pendiente/rechazada) */
function obtener_asistencias_abiertas_pasadas(PDO $pdo, int $practicanteId): array
{
    $st = $pdo->prepare(
        "SELECT a.id as asistencia_id, a.fecha, a.hora_entrada,
                s.id as solicitud_id, s.estado as solicitud_estado, s.hora_propuesta, s.mensaje_rechazo
         FROM asistencias a
         LEFT JOIN solicitudes_salida s ON s.asistencia_id = a.id AND s.estado IN ('pendiente', 'rechazada')
         WHERE a.practicante_id = ? 
           AND a.hora_salida IS NULL 
           AND a.hora_entrada IS NOT NULL
           AND a.fecha < CURDATE()
         ORDER BY a.fecha ASC"
    );
    $st->execute([$practicanteId]);
    return $st->fetchAll();
}
