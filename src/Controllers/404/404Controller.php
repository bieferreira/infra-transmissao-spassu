<?php

declare(strict_types=1);

try {
    echo APP_TWIG->render('/404/404.twig', ['']);
} catch (\Twig\Error\LoaderError $e) {
    http_response_code(404);
    echo 'Não foi possível localizar está página.';
} catch (\Twig\Error\SyntaxError $e) {
    http_response_code(500);
    echo 'Não foi possível apresentar está página';
} catch (\Twig\Error\RuntimeError $e) {
    http_response_code(500);
    echo 'Não foi possível gerar está página';
}
