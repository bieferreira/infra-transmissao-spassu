<?php

declare(strict_types=1);

// Gera um token dinâmico
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Recupera token CSRF da sessão
 *
 * @param string $_SESSION['csrf_token'] var session token CSRF
 *
 * @return string retorna token CSRF da session
 */
function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

/**
 * Verifica token CSRF da sessão
 *
 * @param string $token var entrada local token CSRF
 *
 * @return string retorna true false da validação hash_equals Verifica se o token é igual ao armazenado na sessão
 */
function verifyCsrfToken($token) {
    return hash_equals($_SESSION['csrf_token'], $token);
}