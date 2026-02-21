<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\UserEventInteraction;
use Doctrine\ORM\EntityManagerInterface;

class UserBehaviorTracker
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function trackEventView(User $user, Event $event, int $duration = 0): void
    {
        $this->createInteraction($user, $event, 'view', $duration);
    }

    public function trackRegistration(User $user, Event $event): void
    {
        $this->createInteraction($user, $event, 'registration');
    }

    public function trackCancellation(User $user, Event $event): void
    {
        $this->createInteraction($user, $event, 'cancellation');
    }

    public function trackFavorite(User $user, Event $event): void
    {
        $this->createInteraction($user, $event, 'favorite');
    }

    public function trackAttendance(User $user, Event $event): void
    {
        $this->createInteraction($user, $event, 'attendance');
    }

    public function trackNotInterested(User $user, Event $event): void
    {
        $this->createInteraction($user, $event, 'not_interested');
    }

    private function createInteraction(User $user, Event $event, string $type, int $duration = 0): void
    {
        $interaction = new UserEventInteraction();
        $interaction->setUser($user);
        $interaction->setEvent($event);
        $interaction->setInteractionType($type);
        $interaction->setDuration($duration);
        $interaction->setTimestamp(new \DateTimeImmutable());

        $this->entityManager->persist($interaction);
        $this->entityManager->flush();
    }
}
