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
const DB_HOST = 'InfraTransmissaoSpassuMysql';
const DB_PORT = '3306';
const DB_NAME = 'infratransmissao';
const DB_USER = 'user';
const DB_PASS = '1nfr4Sp4ssu';
const ID_USUARIO = 1;

$globals = [
    'BASE_URL'   => BASE_URL,
    'ASSETS_URL' => ASSETS_URL,
    'SITE_NAME'  => SITE_NAME
];

foreach ($globals as $key => $value) {
    $twig->addGlobal($key, $value);
}

//return $twig;
