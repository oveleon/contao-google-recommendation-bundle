<?php

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\EventListener\Recommendation;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\FrontendTemplate;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsHook('onBeforeParseRecommendation')]
class OnBeforeParseRecommendationListener
{
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function __invoke(
        FrontendTemplate $template,
        array $row,
        array &$additionalData
    ): void
    {
        if ($template->googleReviewUrl)
        {
            $additionalData[] = [
                'class' => 'google_url',
                'value' => vsprintf('%s <a class="c_link %s" title="%s" href="%s" target="_blank" >%s</a></div>', [
                    $this->translator->trans('tl_recommendation.publishedOn', [], 'contao_default'),
                    $this->translator->trans('tl_recommendation.googleReviewClass', [], 'contao_default'),
                    $this->translator->trans('tl_recommendation.by', [], 'contao_default') . $template->author,
                    $template->googleReviewUrl,
                    $this->translator->trans('tl_recommendation.google', [], 'contao_default')
                ])
            ];
        }
    }
}
