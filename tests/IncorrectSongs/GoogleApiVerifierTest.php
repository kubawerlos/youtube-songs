<?php declare(strict_types=1);

/*
 * This file is part of YouTube songs.
 *
 * (c) 2024 Kuba Werłos
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\IncorrectSongs;

use App\IncorrectSongs\GoogleApiVerifier;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(GoogleApiVerifier::class)]
final class GoogleApiVerifierTest extends TestCase
{
    public function testCallWithoutApiKey(): void
    {
        $apiKey = \getenv(GoogleApiVerifier::API_KEY_ENV_NAME);
        if (\is_string($apiKey)) {
            self::markTestSkipped('Skipped when API key is provided.');
        }

        self::assertFalse(\getenv(GoogleApiVerifier::API_KEY_ENV_NAME));

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        $correctSongIds = GoogleApiVerifier::filterCorrectIds('PL', $messages, ['a0123456789', 'b0123456789']);

        self::assertSame([], $correctSongIds);
        self::assertSame(['No call to Google API (no API key provided).'], $messages->getArrayCopy());
    }

    #[DataProvider('provideCallWithApiKeyCases')]
    public function testCallWithApiKey(string $country): void
    {
        $apiKey = \getenv(GoogleApiVerifier::API_KEY_ENV_NAME);
        if ($apiKey === false) {
            self::markTestSkipped('Needs API key to run');
        }
        self::assertIsString(\getenv(GoogleApiVerifier::API_KEY_ENV_NAME));

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        $correctSongIds = GoogleApiVerifier::filterCorrectIds(
            $country,
            $messages,
            [
                'OS8taasZl8k', // Black Summer
                'a0000000000',
                'E1FNkf3MLKY', // Tippa My Tongue
                'CkwV0TWRAok', // Parallel Universe, unavailable in many countries
                'o8fX0mcU6to', // Behind The Sun, not blocked, but allowed
                '2FnK3BPBuSo', // The Adventures of Rain Dance Maggie, blocked in Brazil
            ],
        );

        $expectedCorrectSongIds = [
            'OS8taasZl8k',
            'E1FNkf3MLKY',
            'o8fX0mcU6to',
            ...($country === 'BR' ? [] : ['2FnK3BPBuSo']),
        ];

        self::assertSame($expectedCorrectSongIds, $correctSongIds);
        self::assertSame(['Calling Google API.'], $messages->getArrayCopy());
    }

    /**
     * @return iterable<array{string}>
     */
    public static function provideCallWithApiKeyCases(): iterable
    {
        yield ['BR'];
        yield ['PL'];
    }
}
