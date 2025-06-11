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
use App\IncorrectSongs\SongsToVerifyProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SongsToVerifyProvider::class)]
final class SongsToVerifyProviderTest extends TestCase
{
    public function testWithoutAddingRandomSongs(): void
    {
        $incorrectSongTitles = ['song 123', 'song 420'];

        $songsToVerify = SongsToVerifyProvider::provideSongsToVerify(
            $incorrectSongTitles,
            self::getAllSongs(),
            false,
        );

        self::assertSame(
            $incorrectSongTitles,
            \array_map(
                static fn (Song $song): string => $song->title,
                $songsToVerify,
            ),
        );
    }

    public function testWithAddingRandomSongs(): void
    {
        $incorrectSongTitles = [];
        for ($i = 200; $i <= 299; $i++) {
            $incorrectSongTitles[] = 'song ' . $i;
        }

        $songsToVerify = SongsToVerifyProvider::provideSongsToVerify(
            $incorrectSongTitles,
            self::getAllSongs(),
            true,
        );

        self::assertGreaterThan(
            \count($incorrectSongTitles),
            \count($songsToVerify),
        );

        $songTitlesToVerify = \array_map(
            static fn (Song $song): string => $song->title,
            $songsToVerify,
        );

        foreach ($incorrectSongTitles as $incorrectSongTitle) {
            self::assertContains($incorrectSongTitle, $songTitlesToVerify);
        }
    }

    /**
     * @return \ArrayObject<string, Song>
     */
    private static function getAllSongs(): \ArrayObject
    {
        /** @var \ArrayObject<string, Song> $allSongs */
        $allSongs = new \ArrayObject();

        for ($i = 100; $i <= 999; $i++) {
            $allSongs['song ' . $i] = Song::create('song ' . $i, ['id' => 'a0000000' . $i]);
        }

        return $allSongs;
    }
}
