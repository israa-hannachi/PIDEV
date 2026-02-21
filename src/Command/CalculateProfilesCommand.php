<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\UserPreferenceProfile;
use App\Repository\UserRepository;
use App\Repository\UserEventInteractionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ai:calculate-profiles',
    description: 'Aggregates user interactions into preference profiles',
)]
class CalculateProfilesCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserEventInteractionRepository $interactionRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserEventInteractionRepository $interactionRepository
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->interactionRepository = $interactionRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            $profile = $user->getUserPreferenceProfile();
            if (!$profile) {
                $profile = new UserPreferenceProfile();
                $profile->setUser($user);
                $this->entityManager->persist($profile);
            }

            $interactions = $this->interactionRepository->findBy(['user' => $user]);
            
            $categories = [];
            foreach ($interactions as $interaction) {
                $event = $interaction->getEvent();
                $cat = $event->getCategorie();
                $categories[$cat] = ($categories[$cat] ?? 0) + 1;
            }

            arsort($categories);
            $profile->setPreferredCategories(array_keys(array_slice($categories, 0, 5)));
            $profile->setLastComputedAt(new \DateTimeImmutable());
        }

        $this->entityManager.flush();
        $io->success('User profiles updated successfully.');

        return Command::SUCCESS;
    }
}
