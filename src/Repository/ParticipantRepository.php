<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Participant>
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function searchAjax(?string $q, ?string $role, ?string $sort, ?string $dir): array
    {
        $qb = $this->createQueryBuilder('p');

        $q = $q !== null ? trim($q) : '';
        if ($q !== '') {
            $qb
                ->andWhere('LOWER(p.nom) LIKE :q OR LOWER(p.prenom) LIKE :q OR LOWER(p.email) LIKE :q')
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        $role = $role !== null ? trim($role) : '';
        if ($role !== '') {
            $qb
                ->andWhere('p.role = :role')
                ->setParameter('role', $role);
        }

        $sort = $sort !== null ? trim($sort) : '';
        $dir = strtoupper(trim((string) $dir)) === 'DESC' ? 'DESC' : 'ASC';

        $sortField = 'p.createdAt';
        if ($sort === 'nom') {
            $sortField = 'p.nom';
        } elseif ($sort === 'email') {
            $sortField = 'p.email';
        } elseif ($sort === 'role') {
            $sortField = 'p.role';
        }

        $qb->orderBy($sortField, $dir);

        return $qb->getQuery()->getResult();
    }

    //    /**
    //     * @return Participant[] Returns an array of Participant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Participant
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
