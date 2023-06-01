<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\System;
use Oveleon\ContaoGoogleRecommendationBundle\EventListener\DataContainer\RecommendationArchiveListener;

// Load language files
System::loadLanguageFile('tl_recommendation_languages');

// Add global operations
$GLOBALS['TL_DCA']['tl_recommendation_archive']['list']['global_operations']['syncAllArchives'] = [
	'href'                    => 'key=syncAllArchives',
	'icon'                    => 'sync.svg',
	'attributes'              => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_recommendation_archive']['syncAllConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
];

// Add operations
$GLOBALS['TL_DCA']['tl_recommendation_archive']['list']['operations']['startSync'] = [
	'href'                    => 'key=startSync',
	'icon'                    => 'sync.svg',
	'attributes'              => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_recommendation_archive']['syncConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
	'button_callback'         => [RecommendationArchiveListener::class, 'addSyncButton'],
];

// Add subpalettes
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncWithGoogle'] = [
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => ['submitOnChange'=>true],
    'sql'                     => "char(1) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googleApiToken'] = [
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => ['doNotCopy'=>true, 'mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googlePlaceId'] = [
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => ['doNotCopy'=>true, 'mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'],
    'sql'                     => "varchar(255) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncLanguage'] = [
    'exclude'                 => true,
    'inputType'               => 'select',
	'options_callback' => static function ()
	{
		return array_keys($GLOBALS['TL_LANG']['tl_recommendation_languages']);
	},
	'reference'				  => &$GLOBALS['TL_LANG']['tl_recommendation_languages'],
    'eval'                    => ['doNotCopy'=>true, 'includeBlankOption'=>true, 'chosen'=>true,'tl_class'=>'w50'],
    'sql'                     => "varchar(5) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_recommendation_archive']['palettes']['__selector__'][]    = 'syncWithGoogle';
$GLOBALS['TL_DCA']['tl_recommendation_archive']['subpalettes']['syncWithGoogle'] = 'googleApiToken,googlePlaceId,syncLanguage';

// Extend the default palette
PaletteManipulator::create()
    ->addLegend('google_legend', 'protected_legend')
    ->addField(['syncWithGoogle'], 'google_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation_archive')
;
