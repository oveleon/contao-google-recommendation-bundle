<?php

declare(strict_types=1);

// Cron jobs
$GLOBALS['TL_CRON']['hourly'][] = array('\\Oveleon\\ContaoGoogleRecommendationBundle\\GooglePlacesApi', 'run');