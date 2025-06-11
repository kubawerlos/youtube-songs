<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

use PhpCsFixer\Finder;
use PhpCsFixerConfig\Factory;

require_once __DIR__ . '/vendor/autoload.php';

return Factory::createForLibrary('YouTube songs', 'Kuba Werłos', 2024)
    ->setFinder(
        Finder::create()
            ->ignoreDotFiles(false)
            ->files()
            ->in(__DIR__),
    );
