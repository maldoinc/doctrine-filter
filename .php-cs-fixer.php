<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('src');

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    'full_opening_tag' => false,
    'no_superfluous_phpdoc_tags' => true,
    'no_unused_imports' => true,
    'concat_space' => ['spacing' => 'one']
])->setFinder($finder);
