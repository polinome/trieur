<?php

declare(strict_types=1);

$autoload = dirname(__DIR__).'/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(
        STDERR,
        "Composer autoloader not found at {$autoload}. Run 'composer install' before running tests.".PHP_EOL
    );
    exit(1);
}

require $autoload;
