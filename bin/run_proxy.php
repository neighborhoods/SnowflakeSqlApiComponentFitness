#!/usr/bin/env php
<?php

declare(strict_types=1);

error_reporting(E_ALL);

if ($argc < 2) {
    echo 'Usage: ' . $argv[0] . ' src/ClientV1/SingleSelectStatement' . PHP_EOL;
    exit(1);
}

require_once __DIR__ . '/../vendor/autoload.php';

$className = 'Neighborhoods\\SnowflakeSqlApiComponentFitness\\'
    . trim(
        str_replace(
            '/',
            '\\',
            substr($argv[1], strpos($argv[1], 'src') + 3)
        ),
        '\\'
    )
    . '\\Proxy';
if (!class_exists($className)) {
    echo 'Proxy class "' . $className . '" not found in "' . $argv[1] . '"' . PHP_EOL;
    exit(2);
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$proxy = new $className();
$proxy->run(); // @phpstan-ignore method.notFound
