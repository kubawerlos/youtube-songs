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
 * @phpstan-type _Song array{id: string, cover?: string, live?: string, source?: string}
 */
final readonly class Song
{
    private function __construct(
        public string $title,
        public string $id,
        public ?string $cover,
        public ?string $live,
        public ?string $source,
    ) {}

    /**
     * @param _Song $data
     */
    public static function create(string $title, array $data): self
    {
        if (\str_contains($title, '"')) {
            throw new \RuntimeException(\sprintf('Song title (\'%s\') cannot contain double quote (").', $title));
        }

        if (!\array_key_exists('id', $data)) {
            throw new \RuntimeException(\sprintf('The "id" is missing for song "%s".', $title));
        }
        if (\mb_strlen($data['id']) !== 11) {
            throw new \RuntimeException(\sprintf('The "id" must be 11 characters long, got %d characters for song "%s".', \mb_strlen($data['id']), $title));
        }
        $id = $data['id'];
        unset($data['id']);

        $cover = $data['cover'] ?? null;
        unset($data['cover']);

        $live = $data['live'] ?? null;
        unset($data['live']);

        $source = $data['source'] ?? null;
        unset($data['source']);

        if ($data !== []) {
            throw new \RuntimeException(\sprintf('Redundant field%s ("%s") for song "%s".', \count($data) > 1 ? 's' : '', \implode('", "', \array_keys($data)), $title));
        }

        return new self($title, $id, $cover, $live, $source);
    }
}
