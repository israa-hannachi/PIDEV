<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai:cleanup',
    description: 'Cleans up old AI data and logs.',
)]
class AiCleanupCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $threeMonthsAgo = (new \DateTime())->modify('-3 months');

        // 1. Remove old invalid cache
        $this->entityManager->createQuery('DELETE FROM App\Entity\RecommendationCache c WHERE c.isValid = false AND c.computedAt < :date')
            ->setParameter('date', $threeMonthsAgo)
            ->execute();

        // 2. Remove old interactions (keep registrations, delete anonymous views)
        $this->entityManager->createQuery('DELETE FROM App\Entity\UserEventInteraction i WHERE i.interactionType = :view AND i.timestamp < :date')
            ->setParameter('view', 'view')
            ->setParameter('date', $threeMonthsAgo)
            ->execute();

        $io->success('Cleanup completed.');

        return Command::SUCCESS;
    }
}
