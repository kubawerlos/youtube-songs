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
use Google\Service\YouTube\Resource\Videos;
use Google\Service\YouTube\Video;
use Google\Service\YouTube\VideoContentDetails;
use Google\Service\YouTube\VideoContentDetailsRegionRestriction;
use Google\Service\YouTube\VideoListResponse;

/**
 * @internal
 */
final readonly class GoogleApiVerifier
{
    public const string API_KEY_ENV_NAME = 'GOOGLE_API_KEY';

    /**
     * @param \ArrayObject<int, string> $messages
     * @param list<string>              $songsIds
     *
     * @return array<array-key, string>
     */
    public static function filterCorrectIds(string $country, \ArrayObject $messages, array $songsIds): array
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
        \assert($youTube->videos instanceof Videos);

        $response = $youTube->videos->listVideos('contentDetails', ['id' => $songsIds]);
        \assert($response instanceof VideoListResponse);

        return \array_map(
            static fn (Video $video): string => $video->id,
            \array_values(\array_filter(
                $response->getItems(),
                static fn (Video $video): bool => self::isWithoutRestrictions($video, $country),
            )),
        );
    }

    private static function isWithoutRestrictions(Video $video, string $country): bool
    {
        $contentDetails = $video->getContentDetails();
        if (!$contentDetails instanceof VideoContentDetails) {
            return false;
        }

        $regionRestriction = $contentDetails->getRegionRestriction();
        if (!$regionRestriction instanceof VideoContentDetailsRegionRestriction) {
            return true;
        }

        $blocked = $regionRestriction->getBlocked();
        if (!\is_array($blocked)) {
            return true;
        }

        return !\in_array($country, $blocked, true);
    }
}
