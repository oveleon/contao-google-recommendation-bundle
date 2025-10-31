<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_recommendation_settings']['fields']['recommendation_sync_interval'] = [
    'default' => 86400,
    'inputType' => 'select',
    'options' => [86400, 3600, 604800, 2592000, 31536000],
    'reference' => &$GLOBALS['TL_LANG']['tl_recommendation_settings']['sync_interval'],
    'eval' => ['mandatory'=>true, 'tl_class'=>'w50'],
];

PaletteManipulator::create()
    ->addField('recommendation_sync_interval', 'recommendation_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation_settings')
;
