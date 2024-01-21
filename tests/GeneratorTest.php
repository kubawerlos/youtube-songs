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

use App\Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @covers \App\Generator
 *
 * @internal
 */
final class GeneratorTest extends TestCase
{
    /**
     * @param list<string> $expectedMessages
     */
    #[DataProvider('provideGenerationCases')]
    public function testGeneration(
        int $expectedExitStatus,
        array $expectedMessages,
        ?string $expectedMarkdown = null,
        ?string $inputMarkdown = null,
        ?string $inputYaml = null,
    ): void {
        $filesystem = new Filesystem();
        try {
            $filesystem->mkdir(self::testDirectory());
            \chdir(self::testDirectory());
            if ($inputMarkdown !== null) {
                $filesystem->dumpFile(self::testMarkdownPath(), $inputMarkdown);
            }
            if ($inputYaml !== null) {
                $filesystem->dumpFile(self::testYamlPath(), $inputYaml);
            }
            self::assertSame(
                ['exitStatus' => $expectedExitStatus, 'messages' => $expectedMessages],
                Generator::generate(),
            );
            if ($expectedMarkdown !== null) {
                self::assertSame($expectedMarkdown, \file_get_contents(self::testMarkdownPath()));
            }
        } finally {
            $filesystem->remove(self::testDirectory());
        }
    }

    /**
     * @return iterable<array{
     *     0: int,
     *     1: list<string>,
     *     2?: null|string,
     *     3?: null|string,
     *     4?: null|string,
     * }>
     */
    public static function provideGenerationCases(): iterable
    {
        yield 'File .github/youtube-songs.yaml missing' => [
            1,
            [
                \sprintf('File "%s" does not exist.', self::testYamlPath()),
                'Generation failed.',
            ],
        ];

        yield 'File .github/youtube-songs.yaml has invalid syntax' => [
            1,
            [
                \sprintf('Data in "%s" is not an array.', self::testYamlPath()),
                'Generation failed.',
            ],
            null,
            null,
            <<<'YAML'
                key:
                        - lorem
                    - ipsum
                YAML,
        ];

        yield 'File .github/youtube-songs.yaml content is not an array' => [
            1,
            [
                \sprintf('Data in "%s" is not an array.', self::testYamlPath()),
                'Generation failed.',
            ],
            null,
            null,
            <<<'YAML'
                Lorem ipsum
                YAML,
        ];

        yield 'Generation with duplicated song' => [
            1,
            [
                'Song "Song B" cannot be added twice.',
                'Generation failed.',
            ],
            null,
            null,
            <<<'YAML'
                title: The Collection
                Playlist 1:
                    'Song A':
                        id: a0123456789
                    'Song B':
                        id: b0123456789
                Playlist 2:
                    'Song C':
                        id: c0123456789
                    'Song B':
                        id: d0123456789
                    'Song D':
                        id: e0123456789
                YAML,
        ];

        yield 'Generation with duplicated song (in different casing)' => [
            1,
            [
                'Song "Oye Cómo Va" cannot be added twice.',
                'Generation failed.',
            ],
            null,
            null,
            <<<'YAML'
                title: The Collection
                Playlist 1:
                    'Oye CÓmo Va':
                        id: a0123456789
                    'Oye Cómo Va':
                        id: b0123456789
                YAML,
        ];

        yield 'Successful new generation' => [
            0,
            [
                'No call to Google API (no songs to verify).',
                'Generation completed.',
            ],
            <<<'MARKDOWN'
                # The Collection

                ### [Playlist 1](https://www.youtube.com/watch_videos?title=Playlist%201&video_ids=a0123456789,b0123456789)
                1. :cd: "[Song 1](https://www.youtube.com/watch?v=a0123456789)"
                1. :cd: "[Song 2](https://www.youtube.com/watch?v=b0123456789)"

                ### [Playlist 2](https://www.youtube.com/watch_videos?title=Playlist%202&video_ids=c0123456789,d0123456789,e0123456789)
                1. :cd: "[Song 3](https://www.youtube.com/watch?v=c0123456789)" (The Band cover)
                1. :fire: "[Song 4](https://www.youtube.com/watch?v=d0123456789)" (live at The Place)
                1. :cd: "[Song 5](https://www.youtube.com/watch?v=e0123456789)" (bonus track cover)

                MARKDOWN
            ,
            null,
            <<<'YAML'
                title: The Collection
                Playlist 1:
                    'Song 1':
                        id: a0123456789
                    'Song 2':
                        id: b0123456789
                Playlist 2:
                    'Song 3':
                        id: c0123456789
                        cover: The Band
                    'Song 4':
                        id: d0123456789
                        live: The Place
                    'Song 5':
                        id: e0123456789
                        cover: bonus track
                YAML,
        ];

        yield 'Successful generation with cleaning up incorrect songs' => [
            0,
            [
                'No call to Google API (no API key provided).',
                'Song "Song 1" verified - incorrect.',
                'Song "Song 2" verified - incorrect.',
                'Generation completed.',
            ],
            <<<'MARKDOWN'
                # The Collection

                :exclamation: Incorrect songs: "Song 1", "Song 2" :exclamation:

                ### [Playlist 1](https://www.youtube.com/watch_videos?title=Playlist%201&video_ids=a0123456789,b0123456789)
                1. :cd: "[Song 1](https://www.youtube.com/watch?v=a0123456789)"
                1. :cd: "[Song 2](https://www.youtube.com/watch?v=b0123456789)"

                MARKDOWN
            ,
            <<<'MARKDOWN'
                # The Collection

                :exclamation: Incorrect songs: "Song 1", "Song 404", "Song 2" :exclamation:

                ### [Playlist 1](https://www.youtube.com/watch_videos?title=Playlist%201&video_ids=a0123456789,b0123456789)
                1. :cd: "[Song 1](https://www.youtube.com/watch?v=a0123456789)"
                1. :cd: "[Song 2](https://www.youtube.com/watch?v=b0123456789)"

                MARKDOWN
            ,
            <<<'YAML'
                title: The Collection
                Playlist 1:
                    'Song 1':
                        id: a0123456789
                    'Song 2':
                        id: b0123456789
                YAML,
        ];
    }

    private static function testDirectory(): string
    {
        return __DIR__ . '/.tmp';
    }

    private static function testMarkdownPath(): string
    {
        return self::testDirectory() . '/README.md';
    }

    private static function testYamlPath(): string
    {
        return self::testDirectory() . '/.github/youtube-songs.yaml';
    }
}
