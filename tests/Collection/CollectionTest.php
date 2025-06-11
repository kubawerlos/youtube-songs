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

use App\Collection\Collection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Collection::class)]
final class CollectionTest extends TestCase
{
    public function testCreatingWithoutAnyPlaylist(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection does not have any playlist.');

        Collection::create(
            new \ArrayObject(),
            ['title' => 'the collection'],
        );
    }

    public function testCreatingWithoutTitle(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection title is missing.');

        Collection::create(new \ArrayObject(), []);
    }

    public function testCreatingWithTitleBeingANumber(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection title is a number.');

        // @phpstan-ignore-next-line argument.type
        Collection::create(new \ArrayObject(), ['title' => 42]);
    }

    public function testCreatingWithPlaylistHavingNumberAsTitle(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Collection contains a playlist whose title is a number.');

        Collection::create(
            new \ArrayObject(),
            // @phpstan-ignore-next-line argument.type
            [
                'title' => 'the collection',
                42 => [],
            ],
        );
    }

    public function testCreatingWithSongHavingNonArrayData(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Data is not an array for playlist "Playlist 1".');

        Collection::create(
            new \ArrayObject(),
            [
                'title' => 'the collection',
                'Playlist 1' => 'not an array',
            ],
        );
    }

    public function testCreatingCorrectly(): void
    {
        $collection = Collection::create(
            new \ArrayObject(),
            [
                'title' => 'the collection',
                'playlist 1' => [
                    'song 1' => ['id' => 'a0123456789'],
                    'song 2' => ['id' => 'b0123456789'],
                ],
                'playlist 2' => [
                    'song 3' => ['id' => 'c0123456789', 'cover' => 'original artist'],
                    'song 4' => ['id' => 'd0123456789', 'live' => 'the stadium'],
                    'song 5' => ['id' => 'e0123456789', 'source' => 'hidden track'],
                ],
            ],
        );

        self::assertSame('the collection', $collection->title);
        self::assertSame('playlist 1', $collection->playlists[0]->title);
        self::assertSame('song 1', $collection->playlists[0]->songs[0]->title);
        self::assertSame('a0123456789', $collection->playlists[0]->songs[0]->id);
        self::assertNull($collection->playlists[0]->songs[0]->cover);
        self::assertNull($collection->playlists[0]->songs[0]->live);
        self::assertNull($collection->playlists[0]->songs[0]->source);
        self::assertSame('song 2', $collection->playlists[0]->songs[1]->title);
        self::assertSame('b0123456789', $collection->playlists[0]->songs[1]->id);
        self::assertNull($collection->playlists[0]->songs[1]->cover);
        self::assertNull($collection->playlists[0]->songs[1]->live);
        self::assertNull($collection->playlists[0]->songs[1]->source);
        self::assertSame('playlist 2', $collection->playlists[1]->title);
        self::assertSame('song 3', $collection->playlists[1]->songs[0]->title);
        self::assertSame('c0123456789', $collection->playlists[1]->songs[0]->id);
        self::assertSame('original artist', $collection->playlists[1]->songs[0]->cover);
        self::assertNull($collection->playlists[1]->songs[0]->live);
        self::assertNull($collection->playlists[1]->songs[0]->source);
        self::assertSame('song 4', $collection->playlists[1]->songs[1]->title);
        self::assertSame('d0123456789', $collection->playlists[1]->songs[1]->id);
        self::assertNull($collection->playlists[1]->songs[1]->cover);
        self::assertSame('the stadium', $collection->playlists[1]->songs[1]->live);
        self::assertNull($collection->playlists[1]->songs[1]->source);
        self::assertSame('song 5', $collection->playlists[1]->songs[2]->title);
        self::assertSame('e0123456789', $collection->playlists[1]->songs[2]->id);
        self::assertNull($collection->playlists[1]->songs[2]->cover);
        self::assertNull($collection->playlists[1]->songs[2]->live);
        self::assertSame('hidden track', $collection->playlists[1]->songs[2]->source);
    }
}
