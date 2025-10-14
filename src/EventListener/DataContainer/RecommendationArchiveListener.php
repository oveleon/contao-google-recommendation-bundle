<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\EventListener\DataContainer;

use Contao\Backend;
use Contao\Image;
use Contao\StringUtil;

class RecommendationArchiveListener
{
    /**
     * Returns the Google sync button
     */
    public function addSyncButton(array $row, string $href, string $label, string $title, string $icon, string $attributes): string
    {
        if (!isset($row['syncWithGoogle']))
        {
            return Image::getHtml('bundles/contaogooglerecommendation/icons/sync_disabled.svg', $label);
        }

        return '<a href="'.Backend::addToUrl($href.'&amp;id='.$row['id']).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }
}
