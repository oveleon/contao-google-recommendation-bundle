<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Oveleon\ContaoGoogleRecommendationBundle\ContaoGoogleRecommendationBundle;
use Oveleon\ContaoRecommendationBundle\ContaoRecommendationBundle;

/**
 * Plugin for the Contao Manager.
 *
 * @author Fabian Ekert <fabian@oveleon.de>
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoGoogleRecommendationBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class])
                ->setLoadAfter([ContaoRecommendationBundle::class])
                ->setReplace(['google-recommendation']),
        ];
    }
}
