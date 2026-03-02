<?php

namespace App\Controller;

use App\Entity\Meet;
use App\Form\MeetType;
use App\Repository\MeetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/admin/meets')]
class AdminMeetController extends AbstractController
{
    #[Route('/', name: 'app_admin_meet_index', methods: ['GET'])]
    public function index(MeetRepository $meetRepository): Response
    {
        return $this->render('meet/index_admin.html.twig', [
            'meets' => $meetRepository->findBy([], ['dateDebut' => 'DESC']),
        ]);
    }

    #[Route('/data', name: 'app_admin_meet_data', methods: ['GET'])]
    public function data(Request $request, MeetRepository $meetRepository, CsrfTokenManagerInterface $csrfTokenManager): JsonResponse
    {
        $q = $request->query->get('q');
        $teacher = $request->query->get('teacher');
        $status = $request->query->get('status');
        $fromRaw = $request->query->get('from');
        $toRaw = $request->query->get('to');
        $sort = $request->query->get('sort');
        $dir = $request->query->get('dir');

        $from = null;
        if (is_string($fromRaw) && trim($fromRaw) !== '') {
            try {
                $from = new \DateTimeImmutable(trim($fromRaw));
            } catch (\Throwable $e) {
                $from = null;
            }
        }
        $to = null;
        if (is_string($toRaw) && trim($toRaw) !== '') {
            try {
                $to = new \DateTimeImmutable(trim($toRaw));
            } catch (\Throwable $e) {
                $to = null;
            }
        }

        $items = [];
        $meets = $meetRepository->searchCalendarAjax($q, $teacher, $status, $sort, $dir, $from, $to);
        foreach ($meets as $meet) {
            if (!$meet instanceof Meet) {
                continue;
            }

            $p = $meet->getParticipant();
            $dateDebut = $meet->getDateDebut();
            $dateFin = $meet->getDateFin();

            $items[] = [
                'id' => $meet->getId(),
                'titre' => $meet->getTitre(),
                'profNom' => $p ? $p->getNom() : null,
                'profPrenom' => $p ? $p->getPrenom() : null,
                'dateDebut' => $dateDebut ? $dateDebut->format('d/m/Y H:i') : null,
                'dateFinTime' => $dateFin ? $dateFin->format('H:i') : null,
                'participantsCount' => $meet->getParticipants()->count(),
                'showUrl' => $this->generateUrl('app_admin_meet_show', ['id' => $meet->getId()]),
                'editUrl' => $this->generateUrl('app_admin_meet_edit', ['id' => $meet->getId()]),
                'deleteUrl' => $this->generateUrl('app_admin_meet_delete', ['id' => $meet->getId()]),
                'deleteToken' => $csrfTokenManager->getToken('delete' . $meet->getId())->getValue(),
            ];
        }

        return new JsonResponse([
            'items' => $items,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_meet_show', methods: ['GET'])]
    public function show(Meet $meet): Response
    {
        return $this->render('meet/show_admin.html.twig', [
            'meet' => $meet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_meet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Meet $meet, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MeetType::class, $meet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Réunion modifiée avec succès!');

            return $this->redirectToRoute('app_admin_meet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('meet/edit_admin.html.twig', [
            'meet' => $meet,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_meet_delete', methods: ['POST'])]
    public function delete(Request $request, Meet $meet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meet->getId(), $request->request->get('_token'))) {
            $entityManager->remove($meet);
            $entityManager->flush();
            $this->addFlash('success', 'Réunion supprimée avec succès!');
        } else {
            $this->addFlash('error', 'Token CSRF invalide!');
        }

        return $this->redirectToRoute('app_admin_meet_index', [], Response::HTTP_SEE_OTHER);
    }
}
