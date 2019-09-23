<?php
/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncWithGoogle'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_recommendation_archive']['syncWithGoogle'],
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true, 'tl_class'=>'m12'),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googleApiToken'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_recommendation_archive']['googleApiToken'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googlePlaceId'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_recommendation_archive']['googlePlaceId'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_recommendation_archive']['palettes']['__selector__'][] = 'syncWithGoogle';
$GLOBALS['TL_DCA']['tl_recommendation_archive']['subpalettes']['syncWithGoogle'] = 'googleApiToken,googlePlaceId';

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('google_legend', 'protected_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(array('syncWithGoogle'), 'google_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation_archive')
;