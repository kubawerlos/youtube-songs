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
 * @phpstan-import-type _Playlist from Playlist
 */
final readonly class Collection
{
    private function __construct(
        public string $country,
        public string $title,
        /** @var list<Playlist> */
        public array $playlists,
    ) {}

    /**
     * @param \ArrayObject<string, Song>                                     $allSongs
     * @param array<string, _Playlist>|array{country: string, title: string} $data
     */
    public static function create(\ArrayObject $allSongs, array $data): self
    {
        if (!\array_key_exists('country', $data)) {
            throw new \RuntimeException('Collection country code (key "country") must be present.');
        }
        if (!\is_string($data['country'])) {
            throw new \RuntimeException('Collection country code must be a string.');
        }
        if (\preg_match('/^[A-Z]{2}$/', $data['country']) !== 1) {
            throw new \RuntimeException('Collection country code must be 2 uppercase letters.');
        }
        $country = $data['country'];
        unset($data['country']);

        if (!\array_key_exists('title', $data)) {
            throw new \RuntimeException('Collection title (key "title") must be present.');
        }
        if (!\is_string($data['title'])) {
            throw new \RuntimeException('Collection title must be a string.');
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

        return new self($country, $title, $playlists);
    }
}
