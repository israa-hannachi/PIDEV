<?php

namespace App\Repository;

use App\Entity\Meet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Meet>
 */
class MeetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meet::class);
    }

    public function search(?string $course, ?string $teacher): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.participant', 'p')
            ->addSelect('p')
            ->orderBy('m.dateDebut', 'ASC');

        $course = $course !== null ? trim($course) : '';
        if ($course !== '') {
            $qb
                ->andWhere('LOWER(m.titre) LIKE :course')
                ->setParameter('course', '%' . mb_strtolower($course) . '%');
        }

        $teacher = $teacher !== null ? trim($teacher) : '';
        if ($teacher !== '') {
            $qb
                ->andWhere('LOWER(p.nom) LIKE :teacher OR LOWER(p.prenom) LIKE :teacher')
                ->setParameter('teacher', '%' . mb_strtolower($teacher) . '%');
        }

        return $qb->getQuery()->getResult();
    }

    public function searchCalendarAjax(?string $course, ?string $teacher, ?string $status, ?string $sort, ?string $dir): array
    {
        $qb = $this->createQueryBuilder('m')
            ->leftJoin('m.participant', 'p')
            ->addSelect('p');

        $course = $course !== null ? trim($course) : '';
        if ($course !== '') {
            $qb
                ->andWhere('LOWER(m.titre) LIKE :course')
                ->setParameter('course', '%' . mb_strtolower($course) . '%');
        }

        $teacher = $teacher !== null ? trim($teacher) : '';
        if ($teacher !== '') {
            $qb
                ->andWhere('LOWER(p.nom) LIKE :teacher OR LOWER(p.prenom) LIKE :teacher')
                ->setParameter('teacher', '%' . mb_strtolower($teacher) . '%');
        }

        $status = $status !== null ? trim($status) : '';
        if ($status !== '') {
            $now = new \DateTimeImmutable();
            if ($status === 'upcoming') {
                $qb->andWhere('m.dateDebut > :now')->setParameter('now', $now);
            } elseif ($status === 'current') {
                $qb->andWhere('m.dateDebut <= :now AND m.dateFin >= :now')->setParameter('now', $now);
            } elseif ($status === 'completed') {
                $qb->andWhere('m.dateFin < :now')->setParameter('now', $now);
            }
        }

        $sort = $sort !== null ? trim($sort) : '';
        $dir = strtoupper(trim((string) $dir)) === 'DESC' ? 'DESC' : 'ASC';
        $sortField = 'm.dateDebut';
        if ($sort === 'titre') {
            $sortField = 'm.titre';
        } elseif ($sort === 'prof') {
            $sortField = 'p.nom';
        } elseif ($sort === 'dateFin') {
            $sortField = 'm.dateFin';
        }

        $qb->orderBy($sortField, $dir);

        return $qb->getQuery()->getResult();
    }

//    /**
//     * @return Meet[] Returns an array of Meet objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('m.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Meet
//    {
//        return $this->createQueryBuilder('m')
//            ->andWhere('m.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
