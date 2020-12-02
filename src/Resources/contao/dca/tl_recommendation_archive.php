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
    'eval'                    => array('submitOnChange'=>true),
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
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncLanguage'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_recommendation_archive']['syncLanguage'],
    'exclude'                 => true,
    'inputType'               => 'select',
    'options'                 => array('af', 'sq', 'am', 'ar', 'hy', 'az', 'hy', 'az', 'eu', 'be', 'bn', 'bs', 'bg', 'my', 'ca', 'zh', 'zh-CN', 'zh-HK', 'zh-TW', 'hr', 'cs', 'da', 'nl', 'en', 'en-AU', 'en-GB', 'et', 'fa', 'fi', 'fil', 'fr', 'fr-CA', 'gl', 'ka', 'de', 'el', 'gu', 'iw', 'hi', 'hu', 'is', 'id', 'it', 'ja', 'kn', 'kk', 'km', 'ko', 'ky', 'lo', 'lv', 'lt', 'mk', 'ms', 'ml', 'mr', 'mn', 'ne', 'no', 'pl', 'pt', 'pt-BR', 'pt-PT', 'pa', 'ro', 'ru', 'sr', 'si', 'sk', 'sl', 'es', 'es-419', 'sw', 'sv', 'ta', 'te', 'th', 'tr', 'uk', 'ur', 'uz', 'vi', 'zu'),
    'eval'                    => array('mandatory'=>true, 'tl_class'=>'w50'),
    'sql'                     => "varchar(8) NOT NULL default 'en'"
);

$GLOBALS['TL_DCA']['tl_recommendation_archive']['palettes']['__selector__'][] = 'syncWithGoogle';
$GLOBALS['TL_DCA']['tl_recommendation_archive']['subpalettes']['syncWithGoogle'] = 'googleApiToken,googlePlaceId,syncLanguage';

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('google_legend', 'protected_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(array('syncWithGoogle'), 'google_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation_archive')
;