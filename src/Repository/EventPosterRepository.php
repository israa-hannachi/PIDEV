<?php

namespace App\Repository;

use App\Entity\EventPoster;
use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EventPoster>
 */
class EventPosterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EventPoster::class);
    }

    /**
     * Find active poster for event
     */
    public function findActiveByEvent(Event $event): ?EventPoster
    {
        return $this->createQueryBuilder('ep')
            ->andWhere('ep.event = :event')
            ->andWhere('ep.isActive = :active')
            ->setParameter('event', $event)
            ->setParameter('active', true)
            ->orderBy('ep.generatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all versions for an event
     */
    public function findByEvent(Event $event): array
    {
        return $this->createQueryBuilder('ep')
            ->andWhere('ep.event = :event')
            ->setParameter('event', $event)
            ->orderBy('ep.generatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count posters generated for an event
     */
    public function countByEvent(Event $event): int
    {
        return $this->createQueryBuilder('ep')
            ->select('COUNT(ep.id)')
            ->andWhere('ep.event = :event')
            ->setParameter('event', $event)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
