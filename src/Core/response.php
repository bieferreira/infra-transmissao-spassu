<?php

declare(strict_types=1);

function response_http_get(string $url, bool $validar): string
{
    try {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FAILONERROR => false,
        ]);

        $response = curl_exec($ch);

        if($validar) {
            if (curl_errno($ch)) {
                throw new Exception('Falha ao conectar ao serviço externo: ' . curl_error($ch));
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode >= 400) {
                throw new Exception("Erro HTTP {$httpCode} na requisição para {$url}");
            }
        }

    } catch (Throwable $e) {
            response_api_error($e);
    }

    return $response;
}

function response_api_error($error, ?int $statusCode = null): void
{
    $message = $error instanceof Throwable ? $error->getMessage() : (string)$error;

    // Define o status code se não informado
    if ($statusCode === null) {
        $statusCode = match (true) {
            str_contains($message, 'timeout') => 504,
            str_contains($message, 'Could not connect'),
            str_contains($message, 'Connection refused') => 503,
            str_contains($message, 'Unauthorized'),
            str_contains($message, 'token') => 401,
            default => 502,
        };
    }

        // Define o cabeçalho e status HTTP
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode([
            'error' => true,
            'code' => $statusCode,
            'message' => 'Falha ao se comunicar com o serviço externo.',
        ]);

    exit;
}
