<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_recommendation']['fields']['googleAuthorUrl'] = [
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => ['maxlength'=>255, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''"
];

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('google_legend', 'expert_legend', PaletteManipulator::POSITION_BEFORE)
    ->addField(array('googleAuthorUrl'), 'google_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation')
;
