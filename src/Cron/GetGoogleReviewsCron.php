<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\Cron;

use Contao\Config;
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
        if ($this->cache->getItem('recommendation_google_sync')->isHit())
        {
            return;
        }

        $this->googlePlacesApi->getGoogleReviews();

        $this->delayExecution();
    }

    private function delayExecution(): void
    {
        $this->cache->get(
            'recommendation_google_sync',
            static function (ItemInterface $item): void
            {
                $item->expiresAfter((int) (Config::get('recommendation_sync_interval') ?? 86400));
            },
        );
    }
}
