<?php

declare(strict_types=1);

namespace ContaoManager;

use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Oveleon\ContaoGoogleRecommendationBundle\ContaoManager\Plugin;
use Oveleon\ContaoGoogleRecommendationBundle\ContaoGoogleRecommendationBundle;
use Oveleon\ContaoRecommendationBundle\ContaoRecommendationBundle;
use PHPUnit\Framework\TestCase;

class PluginTest extends TestCase
{
    public function testReturnsTheBundles(): void
    {
        $parser = $this->createMock(ParserInterface::class);

        /** @var BundleConfig $config */
        $config = (new Plugin())->getBundles($parser)[0];

        $this->assertInstanceOf(BundleConfig::class, $config);
        $this->assertSame(ContaoGoogleRecommendationBundle::class, $config->getName());
        $this->assertSame([ContaoRecommendationBundle::class], $config->getLoadAfter());
        $this->assertSame(['google-recommendation'], $config->getReplace());
    }
}
