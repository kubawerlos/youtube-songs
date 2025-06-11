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

/**
 * @internal
 */
final readonly class IncorrectSongs
{
    /**
     * @param \ArrayObject<string, Song> $allSongs
     * @param \ArrayObject<int, string>  $messages
     *
     * @return array<int, string>
     */
    public static function create(string $readmePath, \ArrayObject $allSongs, \ArrayObject $messages): array
    {
        $incorrectSongsTitles = \array_filter(
            self::getFromReadme($readmePath),
            static fn (string $songTitle): bool => $allSongs->offsetExists($songTitle),
        );

        $songsToVerify = SongsToVerifyProvider::provideSongsToVerify(
            $incorrectSongsTitles,
            $allSongs,
            \is_string(\getenv(GoogleApiVerifier::API_KEY_ENV_NAME)),
        );
        if ($songsToVerify === []) {
            $messages[] = 'No call to Google API (no songs to verify).';

            return [];
        }

        $correctSongIds = GoogleApiVerifier::filterCorrectIds(
            $messages,
            \array_map(
                static fn (Song $song): string => $song->id,
                $songsToVerify,
            ),
        );

        $incorrectSongTitles = [];
        foreach ($songsToVerify as $song) {
            $isCorrect = \in_array($song->id, $correctSongIds, true);
            $messages[] = \sprintf(
                'Song "%s" verified - %s.',
                $song->title,
                $isCorrect ? 'correct' : 'incorrect',
            );
            if (!$isCorrect) {
                $incorrectSongTitles[] = $song->title;
            }
        }

        \sort($incorrectSongTitles);

        return $incorrectSongTitles;
    }

    /**
     * @return list<string>
     */
    private static function getFromReadme(string $readmePath): array
    {
        if (!\file_exists($readmePath)) {
            return [];
        }

        \preg_match(
            '/:exclamation: Incorrect songs: "(.*)" :exclamation:/',
            (string) \file_get_contents($readmePath),
            $matches,
        );

        if ($matches === []) {
            return [];
        }

        return \explode('", "', \mb_strtolower($matches[1]));
    }
}
