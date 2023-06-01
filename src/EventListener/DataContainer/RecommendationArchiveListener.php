<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Security;

class RecommendationArchiveListener
{
    public function __construct(
        protected ContaoFramework $framework,
        protected Connection $connection,
        protected Security $security
    ){}

    /**
     * Returns the Google sync button
     */
    public function addSyncButton(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        if (!$row['syncWithGoogle'])
        {
            $icon = 'bundles/contaogooglerecommendation/icons/sync_disabled.svg';
            return Image::getHtml($icon, $label);
        }

        return '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }
}
