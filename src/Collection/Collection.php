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

final readonly class Collection
{
    private function __construct(
        public string $title,
        /** @var list<Playlist> */
        public array $playlists,
    ) {}

    /**
     * @param \ArrayObject<string, Song> $allSongs
     * @param array<array-key, mixed>    $data
     */
    public static function create(\ArrayObject $allSongs, array $data): self
    {
        if (!\array_key_exists('title', $data)) {
            throw new \RuntimeException('Collection title is missing.');
        }
        if (!\is_string($data['title'])) {
            throw new \RuntimeException('Collection title is a number.');
        }
        $title = $data['title'];
        unset($data['title']);

        $playlists = [];
        foreach ($data as $playlistTitle => $playlistData) {
            if (!\is_string($playlistTitle)) {
                throw new \RuntimeException('Collection contains a playlist whose title is a number.');
            }
            if (!\is_array($playlistData)) {
                throw new \RuntimeException(\sprintf('Data is not an array for playlist "%s".', $playlistTitle));
            }
            $playlists[] = Playlist::create($allSongs, $playlistTitle, $playlistData);
        }

        if ($playlists === []) {
            throw new \RuntimeException('Collection does not have any playlist.');
        }

        return new self($title, $playlists);
    }
}
