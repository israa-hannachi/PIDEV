<?php

namespace App\Command;

use App\Entity\RecommendationCache;
use App\Repository\UserRepository;
use App\Service\EventRecommendationEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai:generate-recommendations',
    description: 'Pre-calculates event recommendations for all users',
)]
class GenerateRecommendationsCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private EventRecommendationEngine $recommendationEngine;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EventRecommendationEngine $recommendationEngine
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->recommendationEngine = $recommendationEngine;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        // Clear old recommendations
        $this->entityManager->createQuery('DELETE FROM App\Entity\RecommendationCache')->execute();

        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $recommendations = $this->recommendationEngine->getRecommendationsForUser($user, 20);

            foreach ($recommendations as $rec) {
                $cache = new RecommendationCache();
                $cache->setUser($user);
                $cache->setEvent($rec['event']);
                $cache->setMatchScore($rec['score']);
                $cache->setExplanations($rec['explanations']);
                $cache->setComputedAt(new \DateTimeImmutable());
                
                $this->entityManager->persist($cache);
            }
        }

        $this->entityManager->flush();
        $io->success('Recommendations pre-calculated successfully.');

        return Command::SUCCESS;
    }
}
