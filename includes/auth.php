<?php

declare(strict_types=1);

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $u = current_user();
    if ($u === null) {
        redirect(app_url('index.php?r=login'));
    }
    return $u;
}

function require_roles(array $roles): array
{
    $u = require_login();
    if (!in_array($u['rol'], $roles, true)) {
        http_response_code(403);
        exit('No autorizado');
    }
    return $u;
}

function is_admin(array $u): bool
{
    return $u['rol'] === 'admin';
}

function is_supervisor(array $u): bool
{
    return $u['rol'] === 'supervisor';
}

function is_practicante_user(array $u): bool
{
    return $u['rol'] === 'practicante';
}

function attempt_login(PDO $pdo, string $username, string $password): bool
{
    $userLookup = strtolower(trim($username));

    $sql = 'SELECT id, username, password, nombres, rol';
    if (column_exists($pdo, 'usuarios', 'practicante_id')) {
        $sql .= ', practicante_id';
    }
    if (column_exists($pdo, 'usuarios', 'estado')) {
        $sql .= ', estado';
    }
    $sql .= ' FROM usuarios WHERE LOWER(TRIM(username)) = ? LIMIT 1';

    $st = $pdo->prepare($sql);
    $st->execute([$userLookup]);
    $row = $st->fetch();
    $stored = trim((string) ($row['password'] ?? ''));
    if (!$row || !password_verify($password, $stored)) {
        return false;
    }

    if (isset($row['estado']) && $row['estado'] === 'inactivo') {
        $_SESSION['login_inactive_user'] = true;
        return false;
    }

    $pid = null;
    if (array_key_exists('practicante_id', $row) && $row['practicante_id'] !== null && $row['practicante_id'] !== '') {
        $pid = (int) $row['practicante_id'];
    }

    // Regenerar el ID de sesión para prevenir Session Fixation
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => (int) $row['id'],
        'username' => $row['username'],
        'nombres' => $row['nombres'],
        'rol' => $row['rol'],
        'practicante_id' => $pid,
    ];
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
