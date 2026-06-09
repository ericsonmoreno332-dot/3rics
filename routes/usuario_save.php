<?php

declare(strict_types=1);

$user = require_roles(['admin']);
if (!is_post()) {
    redirect(app_url('index.php?r=usuarios'));
}
verify_csrf();

$pdo = db();
$id  = (int) ($_POST['id'] ?? 0);
$rol = (string) ($_POST['rol'] ?? 'supervisor');

if (!in_array($rol, ['admin', 'supervisor', 'practicante'], true)) {
    $rol = 'supervisor';
}

$estado_usuario = (string) ($_POST['estado_usuario'] ?? 'activo');
if (!in_array($estado_usuario, ['activo', 'inactivo'], true)) {
    $estado_usuario = 'activo';
}

// ── PRACTICANTE branch ────────────────────────────────────────────────────────
if ($rol === 'practicante') {

    $dni         = preg_replace('/\D/', '', (string) ($_POST['dni'] ?? ''));
    $nombres     = trim((string) ($_POST['nombres_pract'] ?? ''));
    $apellidos   = trim((string) ($_POST['apellidos'] ?? ''));
    $carrera     = trim((string) ($_POST['carrera'] ?? ''));
    $correo      = trim((string) ($_POST['correo'] ?? ''));
    $telefono    = trim((string) ($_POST['telefono'] ?? ''));
    $estado      = (string) ($_POST['estado'] ?? 'activo');
    $fecha_inicio = ($_POST['fecha_inicio'] ?? '') !== '' ? (string) $_POST['fecha_inicio'] : null;
    $fecha_fin    = ($_POST['fecha_fin']    ?? '') !== '' ? (string) $_POST['fecha_fin']    : null;
    $institucion_id = ($_POST['institucion_id'] ?? '') !== '' ? (int) $_POST['institucion_id'] : null;
    $area_id        = ($_POST['area_id']        ?? '') !== '' ? (int) $_POST['area_id']        : null;
    $practicante_id = ($_POST['practicante_id'] ?? '') !== '' ? (int) $_POST['practicante_id'] : null;

    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $password_confirm = (string) ($_POST['password_confirm'] ?? '');

    if (!in_array($estado, ['activo', 'finalizado', 'suspendido'], true)) {
        $estado = 'activo';
    }

    // Validations
    if (strlen($dni) !== 8) {
        flash('err', 'El DNI debe tener 8 dígitos');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }
    if ($nombres === '' || $apellidos === '' || $carrera === '') {
        flash('err', 'Complete los campos obligatorios: Nombres, Apellidos y Carrera');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }
    if ($username === '') {
        flash('err', 'Indique un nombre de usuario');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }
    if ($password !== '' && $password !== $password_confirm) {
        flash('err', 'Las contraseñas no coinciden');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }
    if ($password !== '' && (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password))) {
        flash('err', 'La contraseña debe tener al menos 8 caracteres, incluir letras y números');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }
    if ($id === 0 && $password === '') {
        flash('err', 'Indique una contraseña para el nuevo practicante');
        flash_old();
        redirect(app_url('index.php?r=usuario_form'));
    }

    // Username duplicate check
    $stDupUser = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? AND id <> ? LIMIT 1');
    $stDupUser->execute([$username, $id]);
    if ($stDupUser->fetch()) {
        flash('err', 'El nombre de usuario ya existe');
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }

    try {
        $pdo->beginTransaction();

        if ($id > 0 && $practicante_id) {
            // ── UPDATE existing practicante + user ───────────────────────────
            // Check DNI duplicate in other practicantes
            $stDupDni = $pdo->prepare('SELECT id FROM practicantes WHERE dni = ? AND id <> ? LIMIT 1');
            $stDupDni->execute([$dni, $practicante_id]);
            if ($stDupDni->fetch()) {
                $pdo->rollBack();
                flash('err', 'Ya existe otro practicante con ese DNI');
                flash_old();
                redirect(app_url('index.php?r=usuario_form&id=' . $id));
            }
            $pdo->prepare('UPDATE practicantes SET dni=?,nombres=?,apellidos=?,carrera=?,correo=?,telefono=?,institucion_id=?,area_id=?,fecha_inicio=?,fecha_fin=?,estado=? WHERE id=?')
                ->execute([$dni, $nombres, $apellidos, $carrera, $correo ?: null, $telefono ?: null, $institucion_id, $area_id, $fecha_inicio, $fecha_fin, $estado, $practicante_id]);

            if ($password !== '') {
                $pdo->prepare("UPDATE usuarios SET username=?,nombres=?,rol='practicante',password=?,practicante_id=?,estado=? WHERE id=?")
                    ->execute([$username, $nombres . ' ' . $apellidos, password_hash($password, PASSWORD_DEFAULT), $practicante_id, $estado_usuario, $id]);
            } else {
                $pdo->prepare("UPDATE usuarios SET username=?,nombres=?,rol='practicante',practicante_id=?,estado=? WHERE id=?")
                    ->execute([$username, $nombres . ' ' . $apellidos, $practicante_id, $estado_usuario, $id]);
            }
        } else {
            // ── INSERT new practicante + user ────────────────────────────────
            $stDupDni = $pdo->prepare('SELECT id FROM practicantes WHERE dni = ? LIMIT 1');
            $stDupDni->execute([$dni]);
            if ($stDupDni->fetch()) {
                $pdo->rollBack();
                flash('err', 'Ya existe un practicante con ese DNI');
                flash_old();
                redirect(app_url('index.php?r=usuario_form'));
            }

            $pdo->prepare(
                'INSERT INTO practicantes (dni,nombres,apellidos,carrera,correo,telefono,institucion_id,area_id,fecha_inicio,fecha_fin,estado)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?)'
            )->execute([$dni, $nombres, $apellidos, $carrera, $correo ?: null, $telefono ?: null, $institucion_id, $area_id, $fecha_inicio, $fecha_fin, $estado]);

            $new_practicante_id = (int) $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO usuarios (username, password, nombres, rol, practicante_id, estado) VALUES (?,?,?,'practicante',?,?)")
                ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nombres . ' ' . $apellidos, $new_practicante_id, $estado_usuario]);
        }

        $pdo->commit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        flash('err', 'Error al guardar: ' . $e->getMessage());
        flash_old();
        redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
    }

    flash('ok', 'Practicante y usuario guardados correctamente');
    redirect(app_url('index.php?r=usuarios'));
}

// ── ADMIN / SUPERVISOR branch ─────────────────────────────────────────────────
$username = trim((string) ($_POST['username_admin'] ?? ''));
$nombres  = trim((string) ($_POST['nombres'] ?? ''));
$password = (string) ($_POST['password_admin'] ?? '');

if ($username === '' || $nombres === '') {
    flash('err', 'Complete los campos obligatorios');
    flash_old();
    redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
}

$stDup = $pdo->prepare('SELECT id FROM usuarios WHERE username = ? AND id <> ? LIMIT 1');
$stDup->execute([$username, $id]);
if ($stDup->fetch()) {
    flash('err', 'El nombre de usuario ya existe');
    flash_old();
    redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
}

try {
    if ($id > 0) {
        if ($password !== '') {
            $pdo->prepare("UPDATE usuarios SET username=?, nombres=?, rol=?, password=?, practicante_id=NULL, estado=? WHERE id=? AND rol IN ('admin','supervisor')")
                ->execute([$username, $nombres, $rol, password_hash($password, PASSWORD_DEFAULT), $estado_usuario, $id]);
        } else {
            $pdo->prepare("UPDATE usuarios SET username=?, nombres=?, rol=?, practicante_id=NULL, estado=? WHERE id=? AND rol IN ('admin','supervisor')")
                ->execute([$username, $nombres, $rol, $estado_usuario, $id]);
        }
    } else {
        if ($password === '') {
            flash('err', 'Indique una contraseña para el nuevo usuario');
            flash_old();
            redirect(app_url('index.php?r=usuario_form'));
        }
        if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            flash('err', 'La contraseña debe tener al menos 8 caracteres, incluir letras y números');
            flash_old();
            redirect(app_url('index.php?r=usuario_form'));
        }
        $pdo->prepare('INSERT INTO usuarios (username, password, nombres, rol, practicante_id, estado) VALUES (?,?,?,?,NULL,?)')
            ->execute([$username, password_hash($password, PASSWORD_DEFAULT), $nombres, $rol, $estado_usuario]);
    }
} catch (PDOException $e) {
    flash('err', 'No se pudo guardar el usuario');
    flash_old();
    redirect(app_url('index.php?r=usuario_form' . ($id ? '&id=' . $id : '')));
}

flash('ok', 'Usuario guardado correctamente');
redirect(app_url('index.php?r=usuarios'));
