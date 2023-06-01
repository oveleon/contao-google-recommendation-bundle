<?php

declare(strict_types=1);

use Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi;

// Back end modules
$GLOBALS['BE_MOD']['content']['recommendation']['startSync']       = [GooglePlacesApi::class, 'syncWithGoogle'];
$GLOBALS['BE_MOD']['content']['recommendation']['syncAllArchives'] = [GooglePlacesApi::class, 'syncAllArchives'];
