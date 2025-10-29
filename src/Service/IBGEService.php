<?php

declare(strict_types=1);

require __DIR__ . '/../Core/response.php';

function getEstadosIBGE(): array
{
    $validar = false;
    $url = 'https://servicodados.ibge.gov.br/api/v1/localidades/estados';

    try {
        $response = response_http_get($url, $validar);

        $data = json_decode($response, true);
        //var_dump($data); exit;


        if (json_last_error() !== JSON_ERROR_NONE) {
            if($validar) {
                throw new Exception('Resposta inválida da API do IBGE.');
            } else {
                $data = [];
            }
        }
    } catch (Throwable $e) {
        if($validar) {
           response_api_error($e);
        } else {
            $data = [];
        }
    }

    return $data;
}
