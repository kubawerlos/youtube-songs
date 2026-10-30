<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Collection;

/**
 * @internal
 *
 * @phpstan-import-type _Song from Song
 *
 * @phpstan-type _Playlist array<string, _Song>
 */
final readonly class Playlist
{
    public const int MAX_SONGS_COUNT = 50;

    private function __construct(
        public string $title,
        /** @var list<Song> */
        public array $songs,
    ) {}

    /**
     * @param \ArrayObject<string, Song> $allSongs
     * @param _Playlist                  $data
     */
    public static function create(\ArrayObject $allSongs, string $title, array $data): self
    {
        $songs = [];
        foreach ($data as $songTitle => $songData) {
            $songTitle = (string) $songTitle;
            if (!\is_array($songData)) {
                throw new \RuntimeException(\sprintf('Data is not an array for song "%s".', $songTitle));
            }
            $song = Song::create($songTitle, $songData);
            $songTitleLowercased = \mb_strtolower($song->title);
            if ($allSongs->offsetExists($songTitleLowercased)) {
                throw new \RuntimeException(\sprintf('Song "%s" cannot be added twice.', $song->title));
            }
            $allSongs[$songTitleLowercased] = $song;
            $songs[] = $song;
        }

        if ($songs === []) {
            throw new \RuntimeException(\sprintf('Playlist "%s" does not have any song.', $title));
        }

        if (\count($songs) > self::MAX_SONGS_COUNT) {
            throw new \RuntimeException(\sprintf('Playlist "%s" has too many (%s, maximum is %s) songs.', $title, \count($songs), self::MAX_SONGS_COUNT));
        }

        return new self($title, $songs);
    }
}
