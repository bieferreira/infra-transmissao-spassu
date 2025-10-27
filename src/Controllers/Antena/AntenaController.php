<?php

declare(strict_types=1);

$uri = (require __DIR__ . '/../../Core/request.php')();
$uri = explode('/', $uri);
$uri_rota = $uri[2] ?? '';
$uri_rota = $uri_rota !== '' ? $uri_rota : 'listar';
$id_antena = $uri[3] ?? '';

require_once __DIR__ . '/../../Models/Antena/AntenaModel.php';

// parâmetros consulta
$page    = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$search  = isset($_GET['q']) ? (string)$_GET['q'] : '';
$uf      = isset($_GET['uf']) ? (string)$_GET['uf'] : '';

$options = [
    'page'     => $page,
    'per_page' => 10,
    'search'   => $search,
    'uf'       => $uf,
];

//$ranking_ufs  = antena_top_ufs(5);
//$antenas = antena_list($options);
//$antena = [];
//$antena = antena_find($id_antena);

$total   = antena_count($options);
$pages   = (int)ceil($total / 10);

$allowed = '/^(cadastrar|editar|excluir|listar|ver|mapa)$/';

try {
    $action = preg_match($allowed, $uri_rota, $match) ? $match[0] : '';

    echo match ($action) {
        'listar' => (function () use ($options, $page, $total, $pages, $search, $uf) {
            $ranking_ufs  = antena_top_ufs(5);
            $antenas = antena_list($options);

            return APP_TWIG->render('/Antena/antena_list.twig', [
                'titulo'        => 'Lista de Antenas',
                'principal_url' => 'home',
                'ranking_ufs'  => $ranking_ufs,
                'antenas'  => $antenas,
                'page'     => $page,
                'total'    => $total,
                'pages'    => $pages,
                'q'        => $search,
                'uf'       => $uf,
            ]);
        })(),
        'ver' => (function () use ($id_antena) {
            $id_antena  = (int)$id_antena;
            $antena = antena_find($id_antena);

            return APP_TWIG->render('/Antena/antena_view.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'antena'       => $antena,
                ]);
        })(),
        'mapa' => (function () use ($id_antena) {
            $id_antena  = (int)$id_antena;
            $antena = antena_find($id_antena);

            return APP_TWIG->render('/Antena/mapa.twig', [
                'titulo'        => 'Ver Antena',
                'principal_url' => 'home',
                'latitude'       => '-20.4698',
                'antena'       => $antena,
            ]);
        })(),
        'cadastrar1' => APP_TWIG->render('/Antena/antena_form.twig', [
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
