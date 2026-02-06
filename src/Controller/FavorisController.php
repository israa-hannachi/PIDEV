<?php

namespace App\Controller;

use App\Entity\Cours;
use App\Repository\CoursRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/favoris')]
final class FavorisController extends AbstractController
{
    #[Route(name: 'app_favoris_index', methods: ['GET'])]
    public function index(Request $request, CoursRepository $coursRepository): Response
    {
        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $sort = (string) $request->query->get('sort', 'date_desc');

        if ($favorisIds === []) {
            return $this->render('favoris/index.html.twig', [
                'cours' => [],
                'favoris_ids' => [],
                'sort' => $sort,
            ]);
        }

        $qb = $coursRepository->createQueryBuilder('co')
            ->andWhere('co.id IN (:ids)')
            ->setParameter('ids', $favorisIds);

        if ($sort === 'alpha_asc') {
            $qb->orderBy('co.titre', 'ASC');
        } elseif ($sort === 'alpha_desc') {
            $qb->orderBy('co.titre', 'DESC');
        } else {
            $qb->orderBy('co.dateCreation', 'DESC');
        }

        return $this->render('favoris/index.html.twig', [
            'cours' => $qb->getQuery()->getResult(),
            'favoris_ids' => $favorisIds,
            'sort' => $sort,
        ]);
    }

    #[Route('/toggle/{id}', name: 'app_favoris_toggle', methods: ['POST'])]
    public function toggle(Request $request, Cours $cour): Response
    {
        if (!$this->isCsrfTokenValid('toggle_favori' . $cour->getId(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $session = $request->getSession();
        $favorisIds = (array) $session->get('favoris_ids', []);
        $favorisIds = array_values(array_unique(array_map('intval', $favorisIds)));

        $id = (int) $cour->getId();
        $idx = array_search($id, $favorisIds, true);
        if ($idx === false) {
            $favorisIds[] = $id;
        } else {
            unset($favorisIds[$idx]);
            $favorisIds = array_values($favorisIds);
        }

        $session->set('favoris_ids', $favorisIds);

        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_favoris_index');
    }
}
