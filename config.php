<?php
declare(strict_types=1);

function loadEnv(string $path): void
{
    if (!is_readable($path)) {
        throw new RuntimeException('No se encontró el archivo .env');
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = explode('=', $line, 2);
        $_ENV[trim($key)] = trim($value, " \t\n\r\0\x0B\"");
    }
}

loadEnv(__DIR__ . '/.env');

function env(string $key, ?string $default = null): ?string
{
    return $_ENV[$key] ?? $default;
}

function database(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', env('DB_HOST'), env('DB_PORT', '3306'), env('DB_NAME'));
    $pdo = new PDO($dsn, env('DB_USER'), env('DB_PASSWORD', ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
