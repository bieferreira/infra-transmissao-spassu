<?php declare(strict_types=1);

function db(): PDO
{

    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = DB_HOST;
    $port = DB_PORT;
    $db   = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;

    $dsn  = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    return $pdo;
}
