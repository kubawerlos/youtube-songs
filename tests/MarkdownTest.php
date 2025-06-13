<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests;

use App\Collection\Collection;
use App\Markdown;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(Markdown::class)]
final class MarkdownTest extends TestCase
{
    public function testGeneration(): void
    {
        $collection = Collection::create(
            new \ArrayObject(),
            [
                'country' => 'PL',
                'title' => 'The Collection',
                'Playlist One' => [
                    'Song 1' => ['id' => 'a0123456789'],
                    'Song 2' => ['id' => 'b0123456789'],
                    'Song 3' => ['id' => 'c0123456789'],
                ],
                'Playlist Two' => [
                    'A cover' => ['id' => 'd0123456789', 'cover' => 'John Doe'],
                    'Live one without location' => ['id' => 'f012345678a', 'live' => '2021'],
                    'Live one with location 1' => ['id' => 'f012345678b', 'live' => 'The Stadium, 2020'],
                    'Live one with location 2' => ['id' => 'f012345678c', 'live' => '123 Street'],
                    'Secret one' => ['id' => 'f0123456789', 'source' => 'hidden track'],
                ],
            ],
        );

        $incorrectSongs = ['Song 2', 'Extra Song'];

        self::assertSame(
            <<<'MARKDOWN'
                # The Collection

                :exclamation: Incorrect songs: "Extra Song", "Song 2" :exclamation:

                ### [Playlist One](https://www.youtube.com/watch_videos?title=Playlist%20One&video_ids=a0123456789,b0123456789,c0123456789)
                1. :cd: "[Song 1](https://www.youtube.com/watch?v=a0123456789)"
                1. :cd: "[Song 2](https://www.youtube.com/watch?v=b0123456789)"
                1. :cd: "[Song 3](https://www.youtube.com/watch?v=c0123456789)"

                ### [Playlist Two](https://www.youtube.com/watch_videos?title=Playlist%20Two&video_ids=d0123456789,f012345678a,f012345678b,f012345678c,f0123456789)
                1. :cd: "[A cover](https://www.youtube.com/watch?v=d0123456789)" (John Doe cover)
                1. :fire: "[Live one without location](https://www.youtube.com/watch?v=f012345678a)" (live in 2021)
                1. :fire: "[Live one with location 1](https://www.youtube.com/watch?v=f012345678b)" (live from The Stadium, 2020)
                1. :fire: "[Live one with location 2](https://www.youtube.com/watch?v=f012345678c)" (live from 123 Street)
                1. :cd: "[Secret one](https://www.youtube.com/watch?v=f0123456789)" (from hidden track)

                MARKDOWN,
            Markdown::generate($collection, $incorrectSongs),
        );
    }
}
