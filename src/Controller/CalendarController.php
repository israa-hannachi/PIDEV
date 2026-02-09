<?php

namespace App\Controller;

use App\Repository\MeetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/calendar')]
class CalendarController extends AbstractController
{
    #[Route('/', name: 'app_calendar')]
    public function index(MeetRepository $meetRepository): Response
    {
        // Récupérer toutes les réunions
        $meets = $meetRepository->findAll();
        
        // Formater les données pour le calendrier
        $formattedMeets = [];
        foreach ($meets as $meet) {
            $formattedMeets[] = [
                'id' => $meet->getId(),
                'titre' => $meet->getTitre(),
                'description' => $meet->getDescription(),
                'dateDebut' => $meet->getDateDebut()->format('Y-m-d\TH:i:s'),
                'dateFin' => $meet->getDateFin()->format('Y-m-d\TH:i:s'),
                'lienMeet' => $meet->getLienMeet(),
                'participant' => [
                    'id' => $meet->getParticipant()->getId(),
                    'nom' => $meet->getParticipant()->getNom(),
                    'prenom' => $meet->getParticipant()->getPrenom(),
                    'role' => $meet->getParticipant()->getRole()
                ],
                'createdAt' => $meet->getCreatedAt()->format('Y-m-d\TH:i:s')
            ];
        }
        
        return $this->render('meet/calendar.html.twig', [
            'meets' => $formattedMeets
        ]);
    }

    #[Route('/data', name: 'app_calendar_data', methods: ['GET'])]
    public function data(Request $request, MeetRepository $meetRepository): JsonResponse
    {
        $course = $request->query->get('course');
        $teacher = $request->query->get('teacher');
        $status = $request->query->get('status');
        $sort = $request->query->get('sort');
        $dir = $request->query->get('dir');

        $meets = $meetRepository->searchCalendarAjax($course, $teacher, $status, $sort, $dir);

        $formatted = [];
        foreach ($meets as $meet) {
            $formatted[] = [
                'id' => $meet->getId(),
                'titre' => $meet->getTitre(),
                'description' => $meet->getDescription(),
                'dateDebut' => $meet->getDateDebut()->format('Y-m-d\\TH:i:s'),
                'dateFin' => $meet->getDateFin()->format('Y-m-d\\TH:i:s'),
                'lienMeet' => $meet->getLienMeet(),
                'participant' => [
                    'id' => $meet->getParticipant()->getId(),
                    'nom' => $meet->getParticipant()->getNom(),
                    'prenom' => $meet->getParticipant()->getPrenom(),
                    'role' => $meet->getParticipant()->getRole(),
                ],
                'createdAt' => $meet->getCreatedAt()->format('Y-m-d\\TH:i:s'),
            ];
        }

        return $this->json([
            'items' => $formatted,
            'total' => count($formatted),
        ]);
    }
}
