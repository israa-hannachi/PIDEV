<?php

namespace App\Controller\Admin;

use App\Repository\CategorieRepository;
use App\Repository\CoursRepository;
use App\Repository\ModuleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/search')]
final class AdminSearchController extends AbstractController
{
    #[Route('', name: 'app_admin_search', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository, CategorieRepository $categorieRepository, ModuleRepository $moduleRepository): Response
    {
        $q = trim((string) $request->query->get('q', ''));
        $categorieId = $request->query->get('categorie');
        $moduleId = $request->query->get('module');
        $actif = $request->query->get('actif');
        $includeContenu = (bool) $request->query->get('include_contenu', false);
        $sort = (string) $request->query->get('sort', 'date_desc');

        $qb = $coursRepository->createQueryBuilder('co')
            ->leftJoin('co.module', 'm')
            ->addSelect('m')
            ->leftJoin('m.categorie', 'c')
            ->addSelect('c');

        if ($q !== '') {
            $where = '(LOWER(co.titre) LIKE :q OR LOWER(co.description) LIKE :q';
            if ($includeContenu) {
                $where .= ' OR LOWER(co.contenu) LIKE :q';
            }
            $where .= ')';

            $qb->andWhere($where)
                ->setParameter('q', '%' . mb_strtolower($q) . '%');
        }

        if ($categorieId !== null && $categorieId !== '') {
            $qb->andWhere('c.id = :categorieId')->setParameter('categorieId', (int) $categorieId);
        }

        if ($moduleId !== null && $moduleId !== '') {
            $qb->andWhere('m.id = :moduleId')->setParameter('moduleId', (int) $moduleId);
        }

        if ($actif === '1' || $actif === '0') {
            $qb->andWhere('co.actif = :actif')->setParameter('actif', $actif === '1');
        }

        if ($sort === 'alpha_asc') {
            $qb->orderBy('co.titre', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('co.titre', 'DESC');
        } else {
            $qb->orderBy('co.dateCreation', 'DESC');
        }

        return $this->render('admin/search/index.html.twig', [
            'q' => $q,
            'categorie_id' => $categorieId,
            'module_id' => $moduleId,
            'actif' => $actif,
            'include_contenu' => $includeContenu,
            'sort' => $sort,
            'cours' => $qb->getQuery()->getResult(),
            'categories' => $categorieRepository->createQueryBuilder('c')->orderBy('c.nom', 'ASC')->getQuery()->getResult(),
            'modules' => $moduleRepository->createQueryBuilder('m')->orderBy('m.titre', 'ASC')->getQuery()->getResult(),
        ]);
    }
}
