<?php

declare(strict_types=1);

$twig = require_once __DIR__ . '/../../config.php';

echo $twig->render('Home/home.twig', [
    'titulo' => 'Principal',
    'principal_url' => 'home',
]);
