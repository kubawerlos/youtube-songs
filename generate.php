<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

require __DIR__ . '/vendor/autoload.php';

$result = App\Generator::generate();

foreach ($result['messages'] as $message) {
    echo $message, PHP_EOL;
}

exit($result['exitStatus']);
