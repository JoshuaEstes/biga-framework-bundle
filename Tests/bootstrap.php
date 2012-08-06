<?php

if (!is_file($loader = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('run "composer.phar install --dev"');
}

require $loader;
