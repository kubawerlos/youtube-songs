<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\IncorrectSongs;

use App\Collection\Song;

final readonly class SongsToVerifyProvider
{
    /**
     * @param array<int, string>         $incorrectSongsTitles
     * @param \ArrayObject<string, Song> $allSongs
     *
     * @return list<Song>
     */
    public static function provideSongsToVerify(
        array $incorrectSongsTitles,
        \ArrayObject $allSongs,
        bool $addRandomSongs,
    ): array {
        $songsToVerify = [];

        foreach ($allSongs as $title => $song) {
            if (\in_array($title, $incorrectSongsTitles, true)) {
                $songsToVerify[] = $song;
                continue;
            }
            if (!$addRandomSongs || !self::isSongToVerify($song)) {
                continue;
            }
            $songsToVerify[] = $song;
        }

        return $songsToVerify;
    }

    private static function isSongToVerify(Song $song): bool
    {
        $sum = 0;

        foreach (\mb_str_split($song->title) as $letter) {
            $sum += \ord($letter);
        }

        return $sum % 10 === \date('j') % 10;
    }
}
