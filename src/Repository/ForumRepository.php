<?php
// src/Repository/ForumRepository.php

namespace App\Repository;

use App\Entity\Forum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ForumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Forum::class);
    }

    // 🔹 Pagination
    public function findAllPaginated(int $page = 1, int $limit = 10): array
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->orderBy('f.created_at', 'DESC') // Tri par date décroissante
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    // 🔹 Compter le nombre total de forums
    public function countAll(): int
    {
        return (int) $this->createQueryBuilder('f')
            ->select('COUNT(f.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    // 🔹 Recherche par titre (insensible à la casse)
    public function findByTitle(string $titre): array
    {
        return $this->createQueryBuilder('f')
            ->where('LOWER(f.titre) LIKE LOWER(:titre)')
            ->setParameter('titre', '%' . $titre . '%')
            ->orderBy('f.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔹 Tri par date de création (ascendant)
    public function findAllOrderByDateAsc(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.created_at', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // 🔹 Tri par date de création (descendant)
    public function findAllOrderByDateDesc(): array
    {
        return $this->createQueryBuilder('f')
            ->orderBy('f.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    // 🔹 Recherche par ID (find() existe déjà)
    public function findById(int $id): ?Forum
    {
        return $this->find($id);
    }
}