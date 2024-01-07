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

use App\Collection\Song;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Collection\Song
 *
 * @internal
 */
final class SongTest extends TestCase
{
    public function testCreatingWithDoubleQuoteInTitle(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Song title (\'double quote -> " <- in title\') cannot contain double quote (").');

        Song::create('double quote -> " <- in title', []);
    }

    public function testCreatingWithoutIdIs(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The "id" is missing for song "song without Id".');

        Song::create('song without Id', []);
    }

    public function testCreatingWithIdNot11CharactersLong(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The "id" must be 11 characters long, got 16 characters for song "song with too long Id".');

        Song::create('song with too long Id', ['id' => 'toooo loooong id']);
    }

    public function testCreatingWithRedundantDataKey(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redundant field ("redundant_key") for song "song with redundant data key".');

        Song::create('song with redundant data key', ['id' => 'i0123456789', 'redundant_key' => 'foo']);
    }

    public function testCreatingWithRedundantDataKeys(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Redundant fields ("redundant_key_1", "redundant_key_2") for song "song with redundant data keys".');

        Song::create('song with redundant data keys', ['id' => 'i0123456789', 'redundant_key_1' => 'foo', 'redundant_key_2' => 'bar']);
    }

    public function testCreatingWithOnlyTitle(): void
    {
        $song = Song::create('the song', ['id' => 'i0123456789']);

        self::assertSame('the song', $song->title);
        self::assertSame('i0123456789', $song->id);
        self::assertNull($song->cover);
        self::assertNull($song->live);
        self::assertNull($song->source);
    }

    public function testCreatingWithAllTheData(): void
    {
        $song = Song::create(
            'the song',
            [
                'id' => 'i0123456789',
                'cover' => 'original artist',
                'live' => 'nice place',
                'source' => 'some soundtrack',
            ],
        );

        self::assertSame('the song', $song->title);
        self::assertSame('i0123456789', $song->id);
        self::assertSame('original artist', $song->cover);
        self::assertSame('nice place', $song->live);
        self::assertSame('some soundtrack', $song->source);
    }
}
