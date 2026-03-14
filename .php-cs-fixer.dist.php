<?php

$finder = PhpCsFixer\Finder::create()
    ->in(['src', 'tests'])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        '@PSR12' => true,
        'compact_nullable_type_declaration' => true,
        'declare_strict_types' => true,
        'explicit_indirect_variable' => true,
        'linebreak_after_opening_tag' => true,
        'no_extra_blank_lines' => true,
        'no_useless_return' => true,
        'phpdoc_to_comment' => false,
        'psr_autoloading' => true,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_first'
        ],
    ])
    ->setFinder($finder)
;
