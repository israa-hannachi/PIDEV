<?php

namespace App\Repository;

use App\Entity\EmailQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailQueue>
 */
class EmailQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EmailQueue::class);
    }

    public function findPendingEmails(int $limit = 10): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.attempts < :maxAttempts')
            ->setParameter('status', 'pending')
            ->setParameter('maxAttempts', 3)
            ->orderBy('e.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(string $status): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getStatistics(): array
    {
        $statuses = ['pending', 'sent', 'failed'];
        $stats = [];
        
        foreach ($statuses as $status) {
            $stats[$status] = $this->countByStatus($status);
        }
        
        $stats['total'] = array_sum($stats);
        
        return $stats;
    }

    public function findRecentEmails(int $limit = 50): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
