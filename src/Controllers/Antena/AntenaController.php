<?php

declare(strict_types=1);

$uri = (require __DIR__ . '/../../Core/request.php')();
$uri = explode('/', $uri);
$uri = $uri[2] ?? '';
$uri = $uri !== '' ? $uri : 'listar';

$antenas = [];

$allowed = '/^(cadastrar|editar|excluir|listar|ver)$/';

try {
    $action = preg_match($allowed, $uri, $match) ? $match[0] : '';

    echo match ($action) {
        'listar' => APP_TWIG->render('/Antena/antena_list.twig', [
            'titulo'        => 'Lista de Antenas',
            'principal_url' => 'home',
            'antenas'       => $antenas,
        ]),
        'ver' => APP_TWIG->render('/Antena/antena_list.twig', [
            'titulo'        => 'Lista de Antenas',
            'principal_url' => 'home',
            'antenas'       => $antenas,
        ]),
        'cadastrar' => APP_TWIG->render('/Antena/antena_form.twig', [
            'titulo'        => 'Nova Antena',
            'principal_url' => 'home',
        ]),
        'editar' =>APP_TWIG->render('/Antena/antena_form.twig', [
            'titulo'        => 'Nova Antena',
            'principal_url' => 'home',
        ]),
        'excluir' => APP_TWIG->render('/Antena/antena_form.twig', [
            'titulo'        => 'Nova Antena',
            'principal_url' => 'home',
        ]),
        default => APP_TWIG->render('/404/404.twig'),
    };
} catch (\Twig\Error\LoaderError $e) {
    echo APP_TWIG->render('/404/404.twig');
} catch (\Twig\Error\SyntaxError $e) {
    http_response_code(500);
    echo 'Não foi possível apresentar está página';
} catch (\Twig\Error\RuntimeError $e) {
    http_response_code(500);
    echo 'Não foi possível gerar está página';
}
