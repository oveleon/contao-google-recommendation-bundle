<?php

declare(strict_types=1);

// Cron jobs
$GLOBALS['TL_CRON']['minutely'][] = array('\\Oveleon\\ContaoGoogleRecommendationBundle\\GooglePlacesApi', 'run');