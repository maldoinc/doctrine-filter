<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
    '@Symfony' => true,
    'full_opening_tag' => false,
    'no_superfluous_phpdoc_tags' => ['allow_mixed' => true], // allow for phpstan
    'no_unused_imports' => true,
    'align_multiline_comment' => [],
    'phpdoc_align' => ['align' => 'left'],
    'concat_space' => ['spacing' => 'one'],
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline'
    ],
    'phpdoc_summary' => true,
])->setFinder($finder);
