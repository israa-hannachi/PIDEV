<?php

namespace App\Controller\Admin;

use App\Entity\ReclamationCours;
use App\Repository\ReclamationCoursRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reclamations')]
final class ReclamationCoursAdminController extends AbstractController
{
    #[Route('', name: 'app_admin_reclamation_index', methods: ['GET'])]
    public function index(ReclamationCoursRepository $repository): Response
    {
        $items = $repository->createQueryBuilder('r')
            ->leftJoin('r.cours', 'c')
            ->addSelect('c')
            ->orderBy('r.resolved', 'ASC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $this->render('admin/reclamation/index.html.twig', [
            'reclamations' => $items,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_reclamation_show', methods: ['GET'])]
    public function show(?ReclamationCours $reclamation): Response
    {
        if ($reclamation === null) {
            return $this->redirectToRoute('app_admin_reclamation_index');
        }

        return $this->render('admin/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/resolve', name: 'app_admin_reclamation_resolve', methods: ['POST'])]
    public function resolve(Request $request, ?ReclamationCours $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($reclamation === null) {
            return $this->redirectToRoute('app_admin_reclamation_index');
        }

        $token = (string) $request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('reclamation_resolve' . $reclamation->getId(), $token)) {
            throw $this->createAccessDeniedException('CSRF token invalide.');
        }

        $reclamation->setResolved(true);
        $reclamation->setResolvedAt(new \DateTime());
        $entityManager->flush();

        $this->addFlash('success', 'Réclamation marquée comme traitée.');
        return $this->redirectToRoute('app_admin_reclamation_show', ['id' => $reclamation->getId()]);
    }
}
