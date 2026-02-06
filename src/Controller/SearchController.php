<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Entity\Cours;
use App\Entity\Module;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/search')]
final class SearchController extends AbstractController
{
    #[Route('', name: 'app_search', methods: ['GET'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $type = (string) $request->query->get('type', 'all');
        $sort = (string) $request->query->get('sort', 'relevance');

        $categories = [];
        $modules = [];
        $cours = [];

        if ($q !== '') {
            $like = '%' . $q . '%';

            $orderByField = null;
            $orderByDir = 'ASC';
            if ($sort === 'alpha' || $sort === 'alpha_asc') {
                $orderByField = 'nom';
                $orderByDir = 'ASC';
            } elseif ($sort === 'alpha_desc') {
                $orderByField = 'nom';
                $orderByDir = 'DESC';
            } elseif ($sort === 'date' || $sort === 'date_desc') {
                $orderByField = 'dateCreation';
                $orderByDir = 'DESC';
            }

            if ($type === 'all' || $type === 'categorie') {
                $qb = $entityManager->getRepository(Categorie::class)->createQueryBuilder('c')
                    ->andWhere('c.nom LIKE :q OR c.description LIKE :q')
                    ->setParameter('q', $like)
                    ->setMaxResults(50);

                if ($orderByField) {
                    $qb->orderBy('c.' . $orderByField, $orderByDir);
                }

                $categories = $qb->getQuery()->getResult();
            }

            if ($type === 'all' || $type === 'module') {
                $qb = $entityManager->getRepository(Module::class)->createQueryBuilder('m')
                    ->andWhere('m.titre LIKE :q OR m.description LIKE :q')
                    ->setParameter('q', $like)
                    ->setMaxResults(50);

                if ($sort === 'alpha' || $sort === 'alpha_asc') {
                    $qb->orderBy('m.titre', 'ASC');
                } elseif ($sort === 'alpha_desc') {
                    $qb->orderBy('m.titre', 'DESC');
                } elseif ($sort === 'date' || $sort === 'date_desc') {
                    $qb->orderBy('m.dateCreation', 'DESC');
                }

                $modules = $qb->getQuery()->getResult();
            }

            if ($type === 'all' || $type === 'cours') {
                $qb = $entityManager->getRepository(Cours::class)->createQueryBuilder('co')
                    ->andWhere('co.titre LIKE :q OR co.description LIKE :q OR co.contenu LIKE :q OR co.fichierContenu LIKE :q')
                    ->setParameter('q', $like)
                    ->setMaxResults(50);

                if ($sort === 'alpha' || $sort === 'alpha_asc') {
                    $qb->orderBy('co.titre', 'ASC');
                } elseif ($sort === 'alpha_desc') {
                    $qb->orderBy('co.titre', 'DESC');
                } elseif ($sort === 'date' || $sort === 'date_desc') {
                    $qb->orderBy('co.dateCreation', 'DESC');
                }

                $cours = $qb->getQuery()->getResult();
            }
        }

        return $this->render('search/index.html.twig', [
            'q' => $q,
            'type' => $type,
            'sort' => $sort,
            'categories' => $categories,
            'modules' => $modules,
            'cours' => $cours,
        ]);
    }
}
