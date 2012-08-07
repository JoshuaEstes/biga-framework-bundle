<?php

/**
 * This file is part of the Biga Framework Bundle
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

if (!is_file($loader = __DIR__ . '/../vendor/autoload.php')) {
    throw new \LogicException('run "composer.phar install --dev"');
}

require $loader;
