<?php

declare(strict_types=1);

function app_root(): string
{
    return dirname(__DIR__);
}

function env_string(string $key, ?string $default = null): ?string
{
    $v = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
    if ($v === false || $v === '') {
        return $default;
    }
    return (string) $v;
}

function env_int(string $key, int $default): int
{
    $v = env_string($key, (string) $default);
    return (int) $v;
}
