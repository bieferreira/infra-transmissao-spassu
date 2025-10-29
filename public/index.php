<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/config.php';

$uri = (require __DIR__ . '/../src/Core/request.php')();
$uri = explode('/', $uri);
$uri = $uri[1] ?? '';
$uri = $uri !== '' ? $uri : 'home';

$routes = [
    'home'   => '/../src/Controllers/Home/HomeController.php',
    'antena' => '/../src/Controllers/Antena/AntenaController.php',
];

if (isset($routes[$uri])) {
    require __DIR__ . $routes[$uri];
} else {
    require __DIR__ . '/../src/Controllers/404/404Controller.php';
}
