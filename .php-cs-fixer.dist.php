<?php

$finder = PhpCsFixer\Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/htdocs/custom/choruspro');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules(array(
        '@PSR12' => true,
        'array_syntax' => array('syntax' => 'long'),
        'binary_operator_spaces' => array('default' => 'single_space'),
        'blank_line_after_opening_tag' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
    ))
    ->setFinder($finder);
