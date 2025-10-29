<?php

declare(strict_types=1);

use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenGlobals;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenNormalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenPublicProperty;
use NunoMaduro\PhpInsights\Domain\Insights\CyclomaticComplexityIsHigh;
use NunoMaduro\PhpInsights\Domain\Insights\TooManyPublicMethods;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenTraits;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenPrivateMethods;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenInterfaces;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenFinalClasses;
use NunoMaduro\PhpInsights\Domain\Insights\ForbiddenConstants;
use NunoMaduro\PhpInsights\Domain\Metrics\Architecture\Classes;
use NunoMaduro\PhpInsights\Domain\Metrics\Code\Comments;

return [

    /*
    |--------------------------------------------------------------------------
    | PRESET BASE
    |--------------------------------------------------------------------------
    | "default" aplica regras de boas práticas, PSR e legibilidade
    | Evite presets de frameworks (laravel, symfony, etc.)
    */
    'preset' => 'default',

    /*
    |--------------------------------------------------------------------------
    | DIRETÓRIOS A ANALISAR
    |--------------------------------------------------------------------------
    */
    'paths' => [
        __DIR__ . '/src',
        __DIR__ . '/public',
    ],

    /*
    |--------------------------------------------------------------------------
    | DIRETÓRIOS OU ARQUIVOS A IGNORAR
    |--------------------------------------------------------------------------
    */
    'exclude' => [
        'vendor',
        'storage',
        'tests',
        'node_modules',
        'bootstrap',
        'db',
        'config',
    ],

    /*
    |--------------------------------------------------------------------------
    | REMOVER INSIGHTS QUE NÃO FAZEM SENTIDO EM CÓDIGO PROCEDURAL
    |--------------------------------------------------------------------------
    */
    'remove' => [
        ForbiddenGlobals::class,          // Você pode usar variáveis globais
        ForbiddenNormalClasses::class,    // Permite código sem classes
        ForbiddenPublicProperty::class,
        ForbiddenPrivateMethods::class,
        ForbiddenInterfaces::class,
        ForbiddenTraits::class,
        ForbiddenFinalClasses::class,
        ForbiddenConstants::class,
        CyclomaticComplexityIsHigh::class,
        TooManyPublicMethods::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | CONFIGURAÇÕES ADICIONAIS
    |--------------------------------------------------------------------------
    */
    'config' => [
        Comments::class => [
            'minPercentage' => 5, // exige ao menos 5% de comentários
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | LIMITE DE NOTAS
    |--------------------------------------------------------------------------
    | Ajuste as notas mínimas esperadas (0 a 100)
    */
    'requirements' => [
        'min-quality' => 70,
        'min-complexity' => 50,
        'min-architecture' => 50,
        'min-style' => 70,
    ],
];
