<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle;

use Contao\Frontend;
use Contao\System;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Input;
use Contao\Message;
use Oveleon\ContaoRecommendationBundle\RecommendationModel;
use Oveleon\ContaoRecommendationBundle\RecommendationArchiveModel;
use Psr\Log\LogLevel;
use Symfony\Component\HttpClient\HttpClient;

/**
 * Updates google reviews by Google Places API
 *
 * @author Fabian Ekert <fabian@oveleon.de>
 * @author Sebastian Zoglowek <https://github.com/zoglo>
 */
class GooglePlacesApi extends Frontend
{
    public function getGoogleReviews(?array $arrIds = null)
    {
		// Check if method is called by cronjob or not
		$blnCron = false;
		
        if(null === $arrIds)
        {
			$recTable = RecommendationArchiveModel::getTable();
			
            $objRecommendationArchives = RecommendationArchiveModel::findBy([
				$recTable . ".syncWithGoogle=?"
            ],[1]);
			
	        $blnCron = true;
        }
        
        $objRecommendationArchives = RecommendationArchiveModel::findMultipleByIds($arrIds);
        
        if (null === $objRecommendationArchives)
        {
            return;
        }

		foreach($objRecommendationArchives as $objRecommendationArchive)
        {
            //$strSyncUrl = 'https://maps.googleapis.com/maps/api/place/details/json?language='.$objRecommendationArchive->syncLanguage.'&place_id='.$objRecommendationArchive->googlePlaceId.'&fields=reviews&key='.$objRecommendationArchive->googleApiToken;
            $strSyncUrl = 'http://dev.contao49.local/files/theme/googleReviews.json';
			
			$client = HttpClient::create();
	        $arrContent = $client->request('POST', $strSyncUrl)->toArray();
	        $objContent = (object) $arrContent;
			
            if ($objContent && $objContent->status !== 'OK')
            {
	            $logger = System::getContainer()->get('monolog.logger.contao');
	            $logger->log(
					LogLevel::ERROR,
					'Recommendations for Archive with ID ' . $objRecommendationArchive->id . ' could not be synced - Reason: '. ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.') ,
					array('contao' => new ContaoContext(__METHOD__, TL_ERROR))
	            );
	
				// Display an error if api call was not successful
	            if(!$blnCron)
	            {
		            Message::addError(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncFailed'], Input::get('id'), ($objContent->error_message ?? $objContent->status ?? 'Connection with Google Api could not be established.')));
	            }
				
				continue;
            }

            if ($objContent && $objContent->result && (is_array($arrReviews = $objContent->result->reviews) ?? null))
            {
	            $time = time();
				
	            $objRecommendations = RecommendationModel::findByPid($objRecommendationArchive->id);
				

                foreach ($arrReviews as $review)
                {
                    // Skip if author url or text is empty or record already exists
	                // ToDo: check records exists
                    if (!$review->author_url || !$review->text || $this->recordExists($objRecommendations, $review->author_url))
                    {
                        continue;
                    }
	
	                // Prepare the record
	                $arrData = array
	                (
		                'tstamp'          => $time,
		                'pid'             => $objRecommendationArchive->id,
		                'author'          => $review->author_name,
		                'date'            => $review->time,
		                'time'            => $review->time,
		                'text'            => '<p>' . $review->text . '</p>',
		                'rating'          => $review->rating,
						'imageUrl'        => $review->profile_photo_url,
						'googleAuthorUrl' => $review->author_url,
		                'published'       => 1
	                );

                    $objRecommendation = new RecommendationModel();
                    $objRecommendation->setRow($arrData)->save();
                }
	
				// Sync happened successfully
	            if(!$blnCron) {
		            Message::addInfo(sprintf($GLOBALS['TL_LANG']['tl_recommendation']['archiveSyncSuccess'], Input::get('id')));
	            }
            }
        }
    }

    public function syncWithGoogle()
    {
        $this->getGoogleReviews([Input::get('id')]);
        $this->redirect($this->getReferer());
    }

    /**
     * Check if a record exists.
     *
     * @param RecommendationModel $objRecommendations
     * @param string              $authorUrl
     *
     * @return boolean
     */
    protected function recordExists($objRecommendations, $authorUrl): bool
    {
		//ToDo: Update fetcheach
        if (null === $objRecommendations)
            return false;
		
		$arrUrls = $objRecommendations->fetchEach('googleAuthorUrl');
		
		return in_array($authorUrl, $arrUrls);
    }
}
