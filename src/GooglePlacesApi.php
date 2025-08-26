<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle;

use Contao\Controller;
use Contao\System;
use Contao\Input;
use Contao\Message;
use Oveleon\ContaoRecommendationBundle\Model\RecommendationModel;
use Oveleon\ContaoRecommendationBundle\Model\RecommendationArchiveModel;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * Updates google reviews by Google Places API
 */
class GooglePlacesApi
{
    const string PLACES_URI   = 'https://maps.googleapis.com/maps/api/place/details';
    const string MAPS_URI     = 'https://www.google.com/maps';
    const string CONTRIBUTION = '/contrib';
    const string PLACE        = '/place';

    private string $placeId = '';

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getGoogleReviews(?array $arrIds = null, bool $manualSync = false): void
    {
        // Check if method is called by cronjob
        $blnCron = false;

        if (null === $arrIds)
        {
            $t = RecommendationArchiveModel::getTable();
            $objArchives = RecommendationArchiveModel::findBy([$t . ".syncWithGoogle=?"], [1]);

            if (!$manualSync)
            {
                $blnCron = true;
            }
        }
        else
        {
            $objArchives = RecommendationArchiveModel::findMultipleByIds($arrIds);
        }

        if (null === $objArchives)
        {
            return;
        }

        System::loadLanguageFile('tl_recommendation');

        foreach ($objArchives as $objArchive)
        {
            $arrParams = [
                'reviews_sort' => 'newest',
                'fields'       => 'reviews',
                'place_id'     => ($this->placeId = $objArchive->googlePlaceId),
                'key'          => $objArchive->googleApiToken,
            ];

            if ($objArchive->syncLanguage)
            {
                $arrParams['language'] = $objArchive->syncLanguage;
            }
            else
            {
                $arrParams['reviews_no_translations'] = 'true';
            }

            $strSyncUrl = self::PLACES_URI . '/json?' . http_build_query($arrParams);
            $client     = HttpClient::create();
            $arrContent = $client->request('POST', $strSyncUrl)->toArray();
            $objContent = (object) $arrContent;

            if ($objContent && $objContent->status !== 'OK')
            {
                System::getContainer()->get('monolog.logger.contao')?->error('Recommendations for Archive with ID ' . $objArchive->id . ' could not be synced - Reason: ' . ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.'));

                if (!$blnCron)
                {
                    // Display an error if api call was not successful
                    Message::addError(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncFailed'], $objArchive->id, ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.')));
                }

                continue;
            }

            if ($objContent && $objContent->result && (is_array($arrReviews = $objContent->result['reviews']) ?? null))
            {
                $time = time();
                $objRecommendations = RecommendationModel::findByPid($objArchive->id);

                foreach ($arrReviews as $review)
                {
                    // Skip if author url is empty or record already exists
                    if (!$review['author_url'] || (!(null === $objRecommendations) && in_array($review['author_url'], $objRecommendations->fetchEach('googleAuthorUrl'))))
                    {
                        continue;
                    }

                    // Save the record
                    $reviewURI = self::getReviewURI($review['author_url']) ?? '';

                    $recommendation = (new RecommendationModel())->setRow([
                        'tstamp'          => $time,
                        'pid'             => $objArchive->id,
                        'author'          => $review['author_name'] ?? '',
                        'date'            => (int) ($review['time'] ?? 0),
                        'time'            => (int) ($review['time'] ?? 0),
                        'text'            => $review['text'] ? '<p>' . $review['text'] . '</p>' : '',
                        'rating'          => (int) $review['rating'],
                        'imageUrl'        => $review['profile_photo_url'] ?? '',
                        'googleAuthorUrl' => $review['author_url'],
                        'googleReviewUrl' => self::getReviewURI($review['author_url']) ?? '',
                        'published'       => 1
                    ]);

                    $recommendation->save();
                }

                if (!$blnCron)
                {
                    Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncSuccess'], $objArchive->id));
                }

                // Invalidate cache tag
                System::getContainer()->get('fos_http_cache.cache_manager')->invalidateTags(['contao.db.tl_recommendation_archive.' . $objArchive->id]);
            }
        }
    }

    /**
     * Sync all archives manually
     */
    public function syncAllArchives(): void
    {
        try {
            $this->getGoogleReviews(null, true);
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
            // Noop
        }

        Controller::redirect(System::getReferer());
    }

    /**
     * Sync selected archive with Google
     */
    public function syncWithGoogle(): void
    {
        try {
            $this->getGoogleReviews([Input::get('id')]);
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface) {
            // Noop
        }

        Controller::redirect(System::getReferer());
    }

    public function getUserID(string $authorUri): ?string
    {
        if (preg_match('/\/contrib\/(\d+)\//', $authorUri, $match))
        {
            return $match[1];
        }

        return '';
    }

    public function getReviewURI(string $authorURI): string
    {
        if (!!$this->placeId && !!($id = self::getUserID($authorURI)))
        {
            return self::MAPS_URI . self::CONTRIBUTION . '/' . $id . self::PLACE . '/' . $this->placeId;
        }

        return '';
    }
}

class_alias(GooglePlacesApi::class, 'GooglePlacesApi');
