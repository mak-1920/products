<?php

$finder = PhpCsFixer\Finder::create()
    ->exclude(['public', 'tests', 'vendor', 'var'])
    ->notPath('*')
    ->in(__DIR__);

$config = new PhpCsFixer\Config();
return $config->setRules([
        '@Symfony' => true,
        '@PHP80Migration' => true,
        'array_push' => true,
        'random_api_migration' => true,
        'phpdoc_to_property_type' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_var_without_name' => false,
    ])
    ->setFinder($finder);