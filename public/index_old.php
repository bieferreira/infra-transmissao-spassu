<?php

declare(strict_types=1);

$twig = require __DIR__ . '/../src/config.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = $uri ? $uri : '/';
$uri = rtrim($uri, '/') ? rtrim($uri, '/') : '/';

switch ($uri) {
    case '/':
    case '/home':
        //require __DIR__ . '/../src/Controllers/Home/HomeController.php';
        echo $twig->render('/Home/home.twig', [
            'titulo' => 'Principal',
            'principal_url' => 'home',
        ]);
        break;

    default:
        http_response_code(404);
        echo 'Not found';
        break;
}
