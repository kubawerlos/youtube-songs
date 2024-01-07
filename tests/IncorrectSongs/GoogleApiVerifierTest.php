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
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\IncorrectSongs\GoogleApiVerifier
 *
 * @internal
 */
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

        $correctSongIds = GoogleApiVerifier::filterCorrectIds($messages, ['a0123456789', 'b0123456789']);

        self::assertSame([], $correctSongIds);
        self::assertSame(['No call to Google API (no API key provided).'], $messages->getArrayCopy());
    }

    public function testCallWithApiKey(): void
    {
        $apiKey = \getenv(GoogleApiVerifier::API_KEY_ENV_NAME);
        if ($apiKey === false) {
            self::markTestSkipped('Needs API key to run');
        }
        self::assertIsString(\getenv(GoogleApiVerifier::API_KEY_ENV_NAME));

        /** @var \ArrayObject<int, string> $messages */
        $messages = new \ArrayObject();

        $correctSongIds = GoogleApiVerifier::filterCorrectIds(
            $messages,
            [
                'OS8taasZl8k', // Black Summer
                'a0000000000',
                'E1FNkf3MLKY', // Tippa My Tongue
            ],
        );

        self::assertSame(['OS8taasZl8k', 'E1FNkf3MLKY'], $correctSongIds);
        self::assertSame(['Calling Google API.'], $messages->getArrayCopy());
    }
}
