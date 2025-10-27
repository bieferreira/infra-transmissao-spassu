<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/Views');

$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

const BASE_URL   = '';
const ASSETS_URL = BASE_URL . '/assets';
const SITE_NAME  = 'Infraestrutura de Trânsmissão';

define('APP_TWIG', $twig);

$globals = [
    'BASE_URL'   => BASE_URL,
    'ASSETS_URL' => ASSETS_URL,
    'SITE_NAME'  => SITE_NAME
];

foreach ($globals as $key => $value) {
    $twig->addGlobal($key, $value);
}

//return $twig;
