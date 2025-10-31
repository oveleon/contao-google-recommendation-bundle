<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCronJob('minutely')]
readonly class GetGoogleReviewsCron
{
    public function __construct(
        private GooglePlacesApi $googlePlacesApi,
        private CacheInterface $cache,
    ) {
    }

    public function __invoke(): void
    {
        $archives = $this->googlePlacesApi->getSyncArchives();
        $toSync = [];

        foreach ($archives as $archiveId => $syncTime)
        {
            if (!$this->cache->getItem('recommendation_google_sync_' . $archiveId)->isHit())
            {
                $toSync[] = $archiveId;
                $this->delayExecution($archiveId, $syncTime);
            }
        }

        $this->googlePlacesApi->getGoogleReviews($toSync);
    }

    private function delayExecution(int|string $archiveId, int|null $syncTime): void
    {
        $syncTime ??= 86400;

        $this->cache->get(
            'recommendation_google_sync_' . $archiveId,
            static function (ItemInterface $item) use ($syncTime): void
            {
                $item->expiresAfter($syncTime);
            },
        );
    }
}
