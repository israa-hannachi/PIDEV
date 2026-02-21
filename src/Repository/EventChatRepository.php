<?php

namespace App\Repository;

use App\Entity\EventChat;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventChat>
 */
class EventChatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventChat::class);
    }

    /**
     * Get chat messages for an event
     */
    public function findByEvent(Event $event, int $limit = 50): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.event = :event')
            ->andWhere('ec.visibility = :visibility')
            ->setParameter('event', $event)
            ->setParameter('visibility', 'public')
            ->orderBy('ec.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get recent AI responses for an event
     */
    public function findRecentAIResponses(Event $event, int $limit = 10): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.event = :event')
            ->andWhere('ec.sender = :sender')
            ->setParameter('event', $event)
            ->setParameter('sender', 'ai_assistant')
            ->orderBy('ec.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get conversation history for AI context
     */
    public function getConversationHistory(Event $event, int $limit = 20): array
    {
        return $this->createQueryBuilder('ec')
            ->andWhere('ec.event = :event')
            ->setParameter('event', $event)
            ->orderBy('ec.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Count messages for an event
     */
    public function countByEvent(Event $event): int
    {
        return $this->createQueryBuilder('ec')
            ->select('COUNT(ec.id)')
            ->andWhere('ec.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
