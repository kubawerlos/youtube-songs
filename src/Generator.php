<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App;

use App\Collection\Collection;
use App\IncorrectSongs\IncorrectSongs;

final class Generator
{
    /**
     * @return array{exitStatus: int, messages: array<int, string>}
     */
    public static function generate(): array
    {
        $allSongs = new \ArrayObject();

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        try {
            if (!\file_exists(self::inputPath())) {
                throw new \RuntimeException(\sprintf('File "%s" does not exist.', self::inputPath()));
            }
            $data = @\yaml_parse_file(self::inputPath());
            if (!\is_array($data)) {
                throw new \RuntimeException(\sprintf('Data in "%s" is not an array.', self::inputPath()));
            }

            $collection = Collection::create($allSongs, $data);
            $incorrectSongs = IncorrectSongs::create(self::outputPath(), $allSongs, $messages);

            \file_put_contents(
                self::outputPath(),
                Markdown::generate($collection, $incorrectSongs),
            );

            $exitStatus = 0;
            $messages[] = 'Generation completed.';
        } catch (\Throwable $exception) {
            $exitStatus = 1;
            $messages[] = $exception->getMessage();
            $messages[] = 'Generation failed.';
        }

        return [
            'exitStatus' => $exitStatus,
            'messages' => $messages->getArrayCopy(),
        ];
    }

    private static function inputPath(): string
    {
        return \getcwd() . '/.github/youtube-songs.yaml';
    }

    private static function outputPath(): string
    {
        return \getcwd() . '/README.md';
    }
}
