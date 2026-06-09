<?php

declare(strict_types=1);

$user = require_roles(['admin', 'supervisor']);
if (!is_post()) {
    redirect(app_url('index.php?r=practicantes'));
}
verify_csrf();

$pdo = db();

$id = (int) ($_POST['id'] ?? 0);
$dni = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));
$nombres = trim((string) ($_POST['nombres'] ?? ''));
$apellidos = trim((string) ($_POST['apellidos'] ?? ''));
$carrera = trim((string) ($_POST['carrera'] ?? ''));
$correo = trim((string) ($_POST['correo'] ?? ''));
$telefono = trim((string) ($_POST['telefono'] ?? ''));
$estado = (string) ($_POST['estado'] ?? 'activo');
$fecha_inicio = ($_POST['fecha_inicio'] ?? '') !== '' ? (string) $_POST['fecha_inicio'] : null;
$fecha_fin = ($_POST['fecha_fin'] ?? '') !== '' ? (string) $_POST['fecha_fin'] : null;
$institucion_id = ($_POST['institucion_id'] ?? '') !== '' ? (int) $_POST['institucion_id'] : null;
$area_id = ($_POST['area_id'] ?? '') !== '' ? (int) $_POST['area_id'] : null;

if (strlen($dni) !== 8) {
    flash('err', 'El DNI debe tener 8 dígitos');
    redirect(app_url('index.php?r=practicante_form' . ($id ? '&id=' . $id : '')));
}

$allowedEst = ['activo', 'finalizado', 'suspendido'];
if (!in_array($estado, $allowedEst, true)) {
    $estado = 'activo';
}

try {
    if ($id > 0) {
        $ex = practicante_por_id($pdo, $id);
        if (!$ex) {
            flash('err', 'Registro no encontrado');
            redirect(app_url('index.php?r=practicantes'));
        }
        $stDup = $pdo->prepare('SELECT id FROM practicantes WHERE dni = ? AND id <> ? LIMIT 1');
        $stDup->execute([$dni, $id]);
        if ($stDup->fetch()) {
            flash('err', 'Ya existe otro practicante con ese DNI');
            redirect(app_url('index.php?r=practicante_form&id=' . $id));
        }

        $sql = 'UPDATE practicantes SET dni=?, nombres=?, apellidos=?, carrera=?, correo=?, telefono=?, institucion_id=?, area_id=?, fecha_inicio=?, fecha_fin=?, estado=? WHERE id=?';
        $params = [$dni, $nombres, $apellidos, $carrera, $correo ?: null, $telefono ?: null, $institucion_id, $area_id, $fecha_inicio, $fecha_fin, $estado, $id];
        
        $st = $pdo->prepare($sql);
        $st->execute($params);
    } else {
        $stDup = $pdo->prepare('SELECT id FROM practicantes WHERE dni = ? LIMIT 1');
        $stDup->execute([$dni]);
        if ($stDup->fetch()) {
            flash('err', 'Ya existe un practicante con ese DNI');
            redirect(app_url('index.php?r=practicante_form'));
        }

        $st = $pdo->prepare(
            'INSERT INTO practicantes (dni,nombres,apellidos,carrera,correo,telefono,institucion_id,area_id,fecha_inicio,fecha_fin,estado)
             VALUES (?,?,?,?,?,?,?,?,?,?,?)'
        );
        $st->execute([
            $dni,
            $nombres,
            $apellidos,
            $carrera,
            $correo ?: null,
            $telefono ?: null,
            $institucion_id,
            $area_id,
            $fecha_inicio,
            $fecha_fin,
            $estado,
        ]);
    }
} catch (RuntimeException $e) {
    flash('err', $e->getMessage());
    redirect(app_url('index.php?r=practicante_form' . ($id ? '&id=' . $id : '')));
} catch (PDOException $e) {
    if ($e->getCode() === '23000' || str_contains($e->getMessage(), 'Duplicate')) {
        flash('err', 'DNI duplicado u otro conflicto de datos');
    } else {
        flash('err', 'Error al guardar: verifique la base de datos y la migración');
    }
    redirect(app_url('index.php?r=practicante_form' . ($id ? '&id=' . $id : '')));
}

flash('ok', 'Practicante guardado correctamente');
redirect(app_url('index.php?r=practicantes'));
