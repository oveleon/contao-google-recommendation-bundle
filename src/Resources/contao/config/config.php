<?php

declare(strict_types=1);

// Back end modules
$GLOBALS['BE_MOD']['content']['recommendation']['startSync'] = array('Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi', 'syncWithGoogle');

// Cron jobs
$GLOBALS['TL_CRON']['hourly'][] = array('Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi', 'run');