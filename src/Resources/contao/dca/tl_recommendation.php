<?php
/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

$GLOBALS['TL_DCA']['tl_recommendation']['list']['global_operations']['startSync'] = array
(
    'href'                => 'key=startSync',
	'class'				  => '',
    'icon'                => 'sync.svg',
	'attributes'          => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['tl_recommendation']['syncConfirm'] ?? null) . '\'))return false;Backend.getScrollOffset()"',
);

$GLOBALS['TL_DCA']['tl_recommendation']['fields']['googleAuthorUrl'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_recommendation']['googleAuthorUrl'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('maxlength'=>255, 'tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);

// Extend the default palette
Contao\CoreBundle\DataContainer\PaletteManipulator::create()
    ->addLegend('google_legend', 'expert_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_BEFORE)
    ->addField(array('googleAuthorUrl'), 'google_legend', Contao\CoreBundle\DataContainer\PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_recommendation')
;
