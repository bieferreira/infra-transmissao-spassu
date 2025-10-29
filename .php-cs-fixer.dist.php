<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . '/src',
    ])
    ->append([__DIR__ . '/public/index.php'])
    ->name('*.php')
    ->notPath('vendor')
    ->notPath('storage')
    ->notPath('migrations')
    ->notPath('public')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'array_syntax' => ['syntax' => 'short'],              // usa []
        'binary_operator_spaces' => ['default' => 'align_single_space_minimal'],
        'blank_line_after_namespace' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return', 'if', 'for', 'foreach', 'while', 'switch'],
        ],
//        'braces' => ['position_after_functions_and_oop_constructs' => 'next'],
        'single_space_around_construct' => true,
        'control_structure_braces' => true,
        'control_structure_continuation_position' => true,
        'declare_parentheses' => true,
        'no_multiple_statements_per_line' => true,
        'braces_position' => true,
        'statement_indentation' => true,
        'no_extra_blank_lines' => true,

        'cast_spaces' => ['space' => 'single'],
        'concat_space' => ['spacing' => 'one'],
        'declare_strict_types' => true,                      // adiciona declare(strict_types=1)
        'lowercase_keywords' => true,
        'no_closing_tag' => true,                            // remove ? > no fim
'no_trailing_whitespace' => true,
'no_whitespace_in_blank_line' => true,
'single_quote' => true,                              // usa aspas simples
'indentation_type' => true,
'line_ending' => true,
])
->setRiskyAllowed(true)
->setUsingCache(true)
->setFinder($finder);
