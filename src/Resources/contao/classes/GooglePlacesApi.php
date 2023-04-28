<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle;

use Contao\Frontend;
use Contao\System;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Input;
use Contao\Message;
use Oveleon\ContaoRecommendationBundle\RecommendationModel;
use Oveleon\ContaoRecommendationBundle\RecommendationArchiveModel;
use Psr\Log\LogLevel;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Updates google reviews by Google Places API
 *
 * @author Fabian Ekert <fabian@oveleon.de>
 * @author Sebastian Zoglowek <https://github.com/zoglo>
 */
class GooglePlacesApi extends Frontend
{
    public function getGoogleReviews(?array $arrIds = null, bool $manualSync = false)
    {
        // Check if method is called by cronjob
        $blnCron = false;

        if (null === $arrIds)
        {
            $recTable = RecommendationArchiveModel::getTable();

            $objRecommendationArchives = RecommendationArchiveModel::findBy([
                $recTable . ".syncWithGoogle=?"
            ], [1]);

            if (!$manualSync)
            {
                $blnCron = true;
            }
        }
        else
        {
            $objRecommendationArchives = RecommendationArchiveModel::findMultipleByIds($arrIds);
        }

        if (null === $objRecommendationArchives)
            return;

        foreach ($objRecommendationArchives as $objRecommendationArchive)
        {
            $arrParams = [
                'reviews_sort' => 'newest',
                'place_id' => $objRecommendationArchive->googlePlaceId,
                'fields' => 'reviews',
                'key' => $objRecommendationArchive->googleApiToken,
            ];

            if ($objRecommendationArchive->syncLanguage) {
                $arrParams['language'] = $objRecommendationArchive->syncLanguage;
            } else {
                $arrParams['reviews_no_translations'] = 'true';
            }

            $strSyncUrl = 'https://maps.googleapis.com/maps/api/place/details/json?' . http_build_query($arrParams);
            $client     = HttpClient::create();
            $arrContent = $client->request('POST', $strSyncUrl)->toArray();
            $objContent = (object)$arrContent;

            System::loadLanguageFile('tl_recommendation');

            if ($objContent && $objContent->status !== 'OK')
            {
                $logger = System::getContainer()->get('monolog.logger.contao');
                $logger->log(
                    LogLevel::ERROR,
                    'Recommendations for Archive with ID ' . $objRecommendationArchive->id . ' could not be synced - Reason: ' . ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.'),
                    ['contao' => new ContaoContext(__METHOD__, TL_ERROR)]
                );

                // Display an error if api call was not successful
                if (!$blnCron)
                {
                    Message::addError(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncFailed'], $objRecommendationArchive->id, ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.')));
                }

                continue;
            }

            if ($objContent && $objContent->result && (is_array($arrReviews = $objContent->result['reviews']) ?? null))
            {
                $time = time();

                $objRecommendations = RecommendationModel::findByPid($objRecommendationArchive->id);

                foreach ($arrReviews as $review)
                {
                    // Skip if author url or text is empty or record already exists
                    if (!$review['author_url'] || !$review['text'] || $this->recordExists($objRecommendations, $review['author_url']))
                        continue;

                    // Prepare the record
                    $arrData = [
                        'tstamp'          => $time,
                        'pid'             => $objRecommendationArchive->id,
                        'author'          => $review['author_name'],
                        'date'            => $review['time'],
                        'time'            => $review['time'],
                        'text'            => '<p>' . $review['text'] . '</p>',
                        'rating'          => $review['rating'],
                        'imageUrl'        => $review['profile_photo_url'],
                        'googleAuthorUrl' => $review['author_url'],
                        'published'       => 1
                    ];

                    $objRecommendation = new RecommendationModel();
                    $objRecommendation->setRow($arrData)->save();
                }

                // Sync happened successfully
                if (!$blnCron)
                {
                    Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncSuccess'], $objRecommendationArchive->id));
                }

                //Invalidate archive tag
                $this->invalidateRecommendationArchiveTag($objRecommendationArchive);
            }
        }
    }

    /**
     * Sync all archives manually
     */
    public function syncAllArchives()
    {
        $this->getGoogleReviews(null, true);
        $this->redirect($this->getReferer());
    }

    /**
     * Sync selected archive with Google
     */
    public function syncWithGoogle()
    {
        $this->getGoogleReviews([Input::get('id')]);
        $this->redirect($this->getReferer());
    }

    /**
     * Check if a record exists
     *
     * @param RecommendationModel $objRecommendations
     * @param string $authorUrl
     *
     * @return boolean
     */
    protected function recordExists($objRecommendations, $authorUrl): bool
    {
        if (null === $objRecommendations)
            return false;

        $arrUrls = $objRecommendations->fetchEach('googleAuthorUrl');

        return in_array($authorUrl, $arrUrls);
    }

    /**
     * Invalidates the recommendation cache tag
     */
    public function invalidateRecommendationArchiveTag($objRecommendationArchive)
    {
        /** @var FOS\HttpCacheBundle\CacheManager $cacheManager */
        $cacheManager = System::getContainer()->get('fos_http_cache.cache_manager');
        $cacheManager->invalidateTags(['contao.db.tl_recommendation_archive.' . $objRecommendationArchive->id]);
    }
}
