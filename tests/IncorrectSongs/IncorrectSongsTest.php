<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\IncorrectSongs;

use App\Collection\Song;
use App\IncorrectSongs\IncorrectSongs;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\IncorrectSongs\IncorrectSongs
 *
 * @internal
 */
final class IncorrectSongsTest extends TestCase
{
    public function testCreatingWithoutReadme(): void
    {
        $root = vfsStream::setup();

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        IncorrectSongs::create($root->url() . '/non-existing-file', new \ArrayObject(), $messages);

        self::assertSame(['No call to Google API (no songs to verify).'], $messages->getArrayCopy());
    }

    public function testCreatingWithoutIncorrectSongs(): void
    {
        $root = vfsStream::setup();

        \file_put_contents(
            $root->url() . '/README.md',
            '# the collection
             ### the playlist',
        );

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        IncorrectSongs::create($root->url() . '/README.md', new \ArrayObject(), $messages);

        self::assertSame(['No call to Google API (no songs to verify).'], $messages->getArrayCopy());
    }

    public function testCreatingWithIncorrectSongs(): void
    {
        $root = vfsStream::setup();

        \file_put_contents(
            $root->url() . '/README.md',
            '# the collection

:exclamation: Incorrect songs: "song 1", "song 404", "song 2" :exclamation:
',
        );

        /** @var \ArrayObject<string, Song> $allSongs */
        $allSongs = new \ArrayObject();
        $allSongs['song 2'] = Song::create('song 2', ['id' => 'b0123456789']);
        $allSongs['song 1'] = Song::create('song 1', ['id' => 'a0123456789']);

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        $incorrectSongsTitles = IncorrectSongs::create($root->url() . '/README.md', $allSongs, $messages);

        self::assertSame(['song 1', 'song 2'], $incorrectSongsTitles);

        self::assertSame(
            [
                'No call to Google API (no API key provided).',
                'Song "song 2" verified - incorrect.',
                'Song "song 1" verified - incorrect.',
            ],
            $messages->getArrayCopy(),
        );
    }
}
