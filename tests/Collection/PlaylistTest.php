<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Collection;

use App\Collection\Playlist;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Playlist::class)]
final class PlaylistTest extends TestCase
{
    public function testCreatingWithoutAnySong(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Playlist "the playlist" does not have any song.');

        Playlist::create(
            new \ArrayObject(),
            'the playlist',
            [],
        );
    }

    public function testCreatingWithSongHavingNumberAsTitle(): void
    {
        $playlist = Playlist::create(
            new \ArrayObject(),
            'the playlist',
            // @phpstan-ignore-next-line argument.type
            [
                42 => ['id' => 'i0123456789'],
            ],
        );

        self::assertSame('the playlist', $playlist->title);
        self::assertSame('42', $playlist->songs[0]->title);
        self::assertSame('i0123456789', $playlist->songs[0]->id);
    }

    public function testCreatingWithSongHavingNonArrayData(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data is not an array for song "the song".');

        Playlist::create(
            new \ArrayObject(),
            'the playlist',
            // @phpstan-ignore-next-line argument.type
            [
                'the song' => 'not an array',
            ],
        );
    }

    public function testCreatingWithTheSameSongTitleTwice(): void
    {
        $allSongs = new \ArrayObject();

        Playlist::create(
            $allSongs,
            'playlist 1',
            [
                'the song' => ['id' => 'i0123456789'],
            ],
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Song "the song" cannot be added twice.');

        Playlist::create(
            $allSongs,
            'playlist 2',
            [
                'the song' => ['id' => 'j0123456789'],
            ],
        );
    }

    public function testCreatingWithTooManySongs(): void
    {
        $data = [];
        for ($i = 1; $i <= 51; $i++) {
            $data['song ' . $i] = ['id' => 'i0123456789'];
        }

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Playlist "the playlist" has too many (51, maximum is 50) songs.');

        Playlist::create(new \ArrayObject(), 'the playlist', $data);
    }

    public function testCreatingCorrectly(): void
    {
        $playlist = Playlist::create(
            new \ArrayObject(),
            'the playlist',
            [
                'song 1' => ['id' => 'i0123456789'],
                'song 2' => ['id' => 'j0123456789'],
            ],
        );

        self::assertSame('the playlist', $playlist->title);
        self::assertSame('song 1', $playlist->songs[0]->title);
        self::assertSame('song 2', $playlist->songs[1]->title);
        self::assertSame('i0123456789', $playlist->songs[0]->id);
        self::assertSame('j0123456789', $playlist->songs[1]->id);
    }
}
