<?php
/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle;

use Oveleon\ContaoRecommendationBundle\RecommendationArchiveModel;
use Oveleon\ContaoRecommendationBundle\RecommendationModel;

/**
 * Updates google recommendation records by Google Places API
 *
 * @author Fabian Ekert <fabian@oveleon.de>
 */
class GooglePlacesApi extends \Frontend
{
    public function run()
    {
        $objRecommendationArchive = RecommendationArchiveModel::findBySyncWithGoogle(1);

        if ($objRecommendationArchive === null)
        {
            return;
        }

        while ($objRecommendationArchive->next())
        {
            $strSyncUrl = 'https://maps.googleapis.com/maps/api/place/details/json?place_id='.$objRecommendationArchive->googlePlaceId.'&fields=rating,user_ratings_total,review&key='.$objRecommendationArchive->googleApiToken;

            $arrContent = json_decode($this->getFileContent($strSyncUrl));

            if ($arrContent && $arrContent->result && is_array($arrContent->result->reviews))
            {
                $objRecommendations = RecommendationModel::findByPid($objRecommendationArchive->id);

                foreach ($arrContent->result->reviews as $review)
                {
                    if ($this->recordExists($objRecommendations, $review->author_url) && !$review->text)
                    {
                        continue;
                    }

                    $objRecommendation = new RecommendationModel();
                    $objRecommendation->pid = $objRecommendationArchive->id;
                    $objRecommendation->author = $review->author_name;
                    $objRecommendation->tstamp = time();
                    $objRecommendation->date = $review->time;
                    $objRecommendation->time = $review->time;
                    $objRecommendation->rating = $review->rating;
                    $objRecommendation->text = '<p>'.$review->text.'</p>';
                    $objRecommendation->imageUrl = $review->profile_photo_url;
                    $objRecommendation->googleAuthorUrl = $review->author_url;
                    $objRecommendation->published = 1;

                    $objRecommendation->save();
                }
            }
        }
    }

    protected function getFileContent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * Check if a record exists.
     *
     * @param RecommendationModel $objRecommendations
     * @param string              $authorUrl
     *
     * @return boolean
     */
    protected function recordExists($objRecommendations, $authorUrl)
    {
        if ($objRecommendations === null)
        {
            return false;
        }

        while ($objRecommendations->next())
        {
            if ($objRecommendations->googleAuthorUrl === $authorUrl)
            {
                $objRecommendations->reset();

                return true;
            }
        }

        $objRecommendations->reset();

        return false;
    }
}
