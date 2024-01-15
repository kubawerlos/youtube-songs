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

final readonly class Playlist
{
    private function __construct(
        public string $title,
        /** @var list<Song> */
        public array $songs,
    ) {}

    /**
     * @param \ArrayObject<string, Song> $allSongs
     * @param array<array-key, mixed>    $data
     */
    public static function create(\ArrayObject $allSongs, string $title, array $data): self
    {
        $songs = [];
        foreach ($data as $songTitle => $songData) {
            if (!\is_string($songTitle)) {
                throw new \RuntimeException(\sprintf('Playlist "%s" has song whose title is a number.', $title));
            }
            if (!\is_array($songData)) {
                throw new \RuntimeException(\sprintf('Data is not an array for song "%s".', $songTitle));
            }
            $song = Song::create($songTitle, $songData);
            if ($allSongs->offsetExists($songTitle)) {
                throw new \RuntimeException(\sprintf('Song "%s" cannot be added twice.', $song->title));
            }
            $allSongs[$songTitle] = $song;
            $songs[] = $song;
        }

        if ($songs === []) {
            throw new \RuntimeException(\sprintf('Playlist "%s" does not have any song.', $title));
        }

        if (\count($songs) > 50) {
            throw new \RuntimeException(\sprintf('Playlist "%s" has too many (%s, maximum is %s) songs.', $title, \count($songs), 50));
        }

        return new self($title, $songs);
    }
}
