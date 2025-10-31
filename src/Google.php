<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle;

enum Google: string
{
   case PLACES_URI   = 'https://maps.googleapis.com/maps/api/place/details';
   case MAPS_URI     = 'https://www.google.com/maps';
   case CONTRIBUTION = '/contrib';
   case PLACE        = '/place';
}
