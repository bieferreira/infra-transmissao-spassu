<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/Views');

$twig = new \Twig\Environment($loader, [
    'cache' => __DIR__ . '/../var/cache/twig',
    'debug' => false,
    'auto_reload' => true,
]);

// === App ===
define('APP_ENV', 'dev');
define('SITE_NAME', 'Infraestrutura de Trânsmissão');

// === Paths ===
define('BASE_URL', '');
define('ASSETS_URL', BASE_URL . '/assets');

// === Banco de Dados ===
define('DB_HOST', 'InfraSpassuMysql');
define('DB_PORT', '3306');
define('DB_NAME', 'infratransmissao');
define('DB_USER', 'user');
define('DB_PASS', '1nfr4Sp4ssu');

// === Sessão / Usuário ===
define('ID_USUARIO', 1);

define('APP_TWIG', $twig);

$globals = [
    'BASE_URL'   => BASE_URL,
    'ASSETS_URL' => ASSETS_URL,
    'SITE_NAME'  => SITE_NAME
];

foreach ($globals as $key => $value) {
    $twig->addGlobal($key, $value);
}
