<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\Cron;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCronJob;
use Contao\CoreBundle\Framework\ContaoFramework;
use Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi;
use Psr\Log\LoggerInterface;

#[AsCronJob('daily')]
class GetGoogleReviewsCron
{
    public function __construct(private ContaoFramework $framework, private LoggerInterface|null $logger)
    {
    }

    public function __invoke(): void
    {
        (new GooglePlacesApi)->getGoogleReviews();
    }
}
