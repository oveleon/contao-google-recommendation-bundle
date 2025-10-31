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
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use FOS\HttpCacheBundle\CacheManager;
use Oveleon\ContaoRecommendationBundle\Model\RecommendationModel;
use Oveleon\ContaoRecommendationBundle\Model\RecommendationArchiveModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Updates google reviews by Google Places API
 */
class GooglePlacesApi
{
    private string $placeId = '';

    public bool $cron = false;

    public function __construct(
        private readonly Connection $connection,
        private readonly CacheManager $cacheManager,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface|null $logger = null,
    ) {
    }

    public function getGoogleReviews(array|null $ids = null): void
    {
        $ids ??= array_keys($this->getSyncArchives());

        foreach (RecommendationArchiveModel::findMultipleByIds($ids) as $archive)
            $this->syncArchive($archive);
    }

    public function getSyncArchives(): array
    {
        $archives = $this->connection->createQueryBuilder()
            ->select('id', 'syncInterval')
            ->from('tl_recommendation_archive')
            ->andWhere('syncWithGoogle = 1')
            ->fetchAllAssociative()
        ;

        return array_column($archives, 'syncInterval', 'id');
    }

    public function syncArchive(RecommendationArchiveModel $archive): void
    {
        if (!$archive->syncWithGoogle) {
            return;
        }

        $arrParams = [
            'reviews_sort' => 'newest',
            'fields'       => 'reviews',
            'place_id'     => ($this->placeId = $archive->googlePlaceId),
            'key'          => $archive->googleApiToken,
        ];

        if ($archive->syncLanguage) {
            $arrParams['language'] = $archive->syncLanguage;
        } else {
            $arrParams['reviews_no_translations'] = 'true';
        }

        $strSyncUrl = Google::PLACES_URI->value . '/json?' . http_build_query($arrParams);
        $client     = HttpClient::create();
        $arrContent = $client->request('POST', $strSyncUrl)->toArray();
        $objContent = (object) $arrContent;

        if ($objContent && $objContent->status !== 'OK')
        {
            $this->logger?->error('Recommendations for Archive with ID ' . $archive->id . ' could not be synced - Reason: ' . ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.'));

            if (!$this->cron) {
                Message::addError($this->translator->trans('recommendation_google.sync.failure', ['%id%' => $archive->id, '%reason%' => ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.')]));
            }

            return;
        }

        if ($objContent && $objContent->result && (is_array($arrReviews = $objContent->result['reviews']) ?? null))
        {
            $time = time();
            $objRecommendations = RecommendationModel::findByPid($archive->id);

            foreach ($arrReviews as $review) {
                // Skip if author url is empty or record already exists
                if (!$review['author_url'] || (!(null === $objRecommendations) && in_array($review['author_url'], $objRecommendations->fetchEach('googleAuthorUrl')))) {
                    continue;
                }

                // Save the record
                $reviewURI = self::getReviewURI($review['author_url']) ?? '';

                $recommendation = (new RecommendationModel())->setRow([
                    'tstamp'          => $time,
                    'pid'             => $archive->id,
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

            if (!$this->cron) {
                Message::addInfo($this->translator->trans('recommendation_google.sync.failure', ['%id%' => $archive->id]));
            }

            // Invalidate cache tag
            $this->cacheManager->invalidateTags(['contao.db.tl_recommendation_archive.' . $archive->id]);
        }
    }

    /**
     * Sync all archives manually
     */
    public function syncAllArchives(): void
    {
        $this->cron = true;

        try {
            $this->getGoogleReviews();
        } catch (\Exception) {
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
        } catch (\Exception) {
            // Noop
        }

        Controller::redirect(System::getReferer());
    }

    public function getReviewURI(string $authorURI): string
    {
        if (!!$this->placeId && !!($id = self::getUserID($authorURI)))
        {
            return Google::MAPS_URI->value . Google::CONTRIBUTION->value . '/' . $id . Google::PLACE->value . '/' . $this->placeId;
        }

        return '';
    }

    protected function getUserID(string $authorUri): ?string
    {
        if (preg_match('/\/contrib\/(\d+)\//', $authorUri, $match))
        {
            return $match[1];
        }

        return '';
    }
}

class_alias(GooglePlacesApi::class, 'GooglePlacesApi');
