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

use Google\Client;
use Google\Service\YouTube;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoListResponse;

final readonly class GoogleApiVerifier
{
    public const API_KEY_ENV_NAME = 'GOOGLE_API_KEY';

    /**
     * @param \ArrayObject<int, string> $messages
     * @param list<string>              $songsIds
     *
     * @return array<int, string>
     */
    public static function filterCorrectIds(\ArrayObject $messages, array $songsIds): array
    {
        $apiKey = \getenv(self::API_KEY_ENV_NAME);

        if (!\is_string($apiKey)) {
            $messages[] = 'No call to Google API (no API key provided).';

            return [];
        }

        $messages[] = 'Calling Google API.';

        $client = new Client();
        $client->setApplicationName('youtube-songs');
        $client->setDeveloperKey($apiKey);
        $youTube = new YouTube($client);
        $response = $youTube->videos->listVideos('snippet', ['id' => $songsIds]);
        \assert($response instanceof VideoListResponse);

        return \array_map(
            static fn (Video $video): string => $video->id,
            $response->items,
        );
    }
}
