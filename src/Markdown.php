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
use App\Collection\Song;

final readonly class Markdown
{
    /**
     * @param array<int, string> $incorrectSongs
     */
    public static function generate(Collection $collection, array $incorrectSongs): string
    {
        $markdown = '# ' . $collection->title . "\n";
        if ($incorrectSongs !== []) {
            $markdown .= self::incorrectSongsRow($incorrectSongs);
        }

        foreach ($collection->playlists as $playlist) {
            $markdown .= "\n### " . self::generateAlbumLink($playlist->title, $playlist->songs) . "\n"

                . self::generateSongRows($playlist->songs) . "\n";
        }

        return $markdown;
    }

    /**
     * @param array<int, string> $incorrectSongs
     */
    private static function incorrectSongsRow(array $incorrectSongs): string
    {
        \sort($incorrectSongs);

        return \sprintf(
            "\n:exclamation: Incorrect songs: \"%s\" :exclamation:\n",
            \implode('", "', $incorrectSongs),
        );
    }

    /**
     * @param list<Song> $songs
     */
    private static function generateAlbumLink(string $album, array $songs): string
    {
        $videoIds = [];
        foreach ($songs as $song) {
            $videoIds[] = $song->id;
        }

        return \sprintf(
            '[%s](https://www.youtube.com/watch_videos?title=%s&video_ids=%s)',
            $album,
            \rawurlencode($album),
            \implode(',', $videoIds),
        );
    }

    /**
     * @param list<Song> $songs
     */
    private static function generateSongRows(array $songs): string
    {
        return \implode("\n", \array_map(
            static fn (Song $song): string => self::generateSongRow($song),
            $songs,
        ));
    }

    private static function generateSongRow(Song $song): string
    {
        $row = \sprintf(
            '1. :%s: "[%s](https://www.youtube.com/watch?v=%s)"',
            $song->live !== null ? 'fire' : 'cd',
            $song->title,
            $song->id,
        );

        $info = [];
        if ($song->live !== null) {
            $info[] = 'live at ' . $song->live;
        }
        if ($song->cover !== null) {
            $info[] = $song->cover . ' cover';
        }
        if ($song->source !== null) {
            $info[] = 'from ' . $song->source;
        }

        if ($info !== []) {
            $row .= ' (' . \implode(', ', $info) . ')';
        }

        return $row;
    }
}
