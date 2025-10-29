<?php

declare(strict_types=1);

return static function (): string {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = parse_url($uri, PHP_URL_PATH) ?
        parse_url($uri, PHP_URL_PATH) : '/';
    $uri = filter_var($uri, FILTER_SANITIZE_URL);
    $uri = preg_match('#/{2,}#', $uri) ?
        preg_replace('#/{2,}#', '/', $uri) : $uri;
    $uri = str_ends_with($uri, '/') && $uri !== '/' ?
        rtrim($uri, '/') : $uri;

    return $uri !== '' ? $uri : '/';
};
