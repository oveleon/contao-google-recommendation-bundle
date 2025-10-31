<?php

declare(strict_types=1);

/*
 * This file is part of Oveleon Google Recommendation Bundle.
 *
 * (c) https://www.oveleon.de/
 */

namespace Oveleon\ContaoGoogleRecommendationBundle\Command;

use Oveleon\ContaoGoogleRecommendationBundle\GooglePlacesApi;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'recommendation:google-sync', description: 'Syncs the google recommendations')]
class SyncGoogleReviewsCommand extends Command
{
    public function __construct(
        private readonly GooglePlacesApi $googlePlacesApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::OPTIONAL, 'The id of the archive that should be synced.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = null;

        if (null !== ($id = $input->getArgument('id'))) {
            $ids = [$id];
        }

        $this->googlePlacesApi->getGoogleReviews($ids);

        return Command::SUCCESS;
    }
}
