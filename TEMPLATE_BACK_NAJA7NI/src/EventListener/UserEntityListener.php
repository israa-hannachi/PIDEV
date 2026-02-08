<?php

namespace App\EventListener;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManagerInterface;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: User::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: User::class)]
#[AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: User::class)]
class UserEntityListener
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function postPersist(User $user, PostPersistEventArgs $event): void
    {
        $this->createNotification(
            'Nouveau compte',
            sprintf('Le compte %s a été créé avec succès.', $user->getEmail()),
            'success'
        );
    }

    public function postUpdate(User $user, PostUpdateEventArgs $event): void
    {
        $this->createNotification(
            'Compte modifié',
            sprintf('Le compte %s a été mis à jour.', $user->getEmail()),
            'info'
        );
    }

    public function postRemove(User $user, PostRemoveEventArgs $event): void
    {
        // For postRemove, the user object is already scheduled for removal, 
        // we just log the event.
        $this->createNotification(
            'Compte supprimé',
            sprintf('Le compte %s a été supprimé.', $user->getEmail()),
            'danger'
        );
    }

    private function createNotification(string $title, string $message, string $type): void
    {
        $notification = new Notification();
        $notification->setTitle($title);
        $notification->setMessage($message);
        $notification->setType($type);
        
        $this->em->persist($notification);
        $this->em->flush();
    }
}
