<?php
/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */
	
// Load language files
Contao\System::loadLanguageFile('tl_recommendation_languages');

// Add global operations
$GLOBALS['TL_DCA']['tl_recommendation_archive']['list']['global_operations']['syncAllArchives'] = array
(
	'href'                => 'key=syncAllArchives',
	'icon'                => 'sync.svg',
	'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_recommendation_archive']['syncAllConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
);

// Add operations
$GLOBALS['TL_DCA']['tl_recommendation_archive']['list']['operations']['startSync'] = array
(
	'href'                => 'key=startSync',
	'icon'                => 'sync.svg',
	'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_recommendation_archive']['syncConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
	'button_callback'     => array('tl_recommendation_archive_google', 'addSyncButton'),
);

// Add subpalettes
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncWithGoogle'] = array
(
    'exclude'                 => true,
    'inputType'               => 'checkbox',
    'eval'                    => array('submitOnChange'=>true),
    'sql'                     => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googleApiToken'] = array
(
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('doNotCopy'=>true, 'mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['googlePlaceId'] = array
(
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('doNotCopy'=>true, 'mandatory'=>true, 'maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_recommendation_archive']['fields']['syncLanguage'] = array
(
    'exclude'                 => true,
    'inputType'               => 'select',
	'options_callback' => static function ()
	{
		return array_keys($GLOBALS['TL_LANG']['tl_recommendation_languages']);
	},
	'reference'				  => &$GLOBALS['TL_LANG']['tl_recommendation_languages'],
    'eval'                    => array('doNotCopy'=>true, 'includeBlankOption'=>true, 'chosen'=>true,'tl_class'=>'w50'),
    'sql'                     => "varchar(5) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_recommendation_archive']['palettes']['__selector__'][] = 'syncWithGoogle';
$GLOBALS['TL_DCA']['tl_recommendation_archive']['subpalettes']['syncWithGoogle'] = 'googleApiToken,googlePlaceId,syncLanguage';

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('google_legend', 'protected_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_AFTER)
    ->addField(array('syncWithGoogle'), 'google_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation_archive')
;

class tl_recommendation_archive_google extends Contao\Backend
{
	/**
	 * Returns the google sync button
	 *
	 * @param array  $row
	 * @param string $href
	 * @param string $label
	 * @param string $title
	 * @param string $icon
	 * @param string $attributes
	 *
	 * @return string
	 */
	public function addSyncButton($row, $href, $label, $title, $icon, $attributes)
	{
		if (!$row['syncWithGoogle'])
		{
			$icon = 'bundles/contaogooglerecommendation/icons/sync_disabled.svg';
			return Contao\Image::getHtml($icon, $label);
		}
		
		return '<a href="'.$this->addToUrl($href.'&amp;id='.$row['id']).'" title="'.Contao\StringUtil::specialchars($title).'"'.$attributes.'>'.Contao\Image::getHtml($icon, $label).'</a> ';
	}
}
