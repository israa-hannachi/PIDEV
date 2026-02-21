<?php

namespace App\Controller\Back;

use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use App\Repository\SponsorRepository;
use App\Repository\AIPredictionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'back_dashboard')]
    public function index(
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        SponsorRepository $sponsorRepository,
        \App\Repository\AIPredictionRepository $predictionRepository,
        \App\Service\AlertGenerator $alertGenerator,
        \App\Service\EventTimingAnalyzer $timingAnalyzer,
        \App\Service\EventSuccessPredictor $successPredictor
    ): Response {
        $events = $eventRepository->findAll();
        $registrations = $registrationRepository->findAll();
        $sponsors = $sponsorRepository->findAll();

        // 1. Calculations
        $totalRevenue = 0;
        $monthlyAttendance = array_fill(1, 12, 0); // Jan-Dec
        $now = new \DateTime();
        $lastYear = (int)$now->format('Y') - 1;
        $bestMonth = ['month' => 0, 'total' => 0];

        foreach ($events as $event) {
            if ($event->getPrix() > 0) {
                $totalRevenue += (float)$event->getPrix() * $event->getInscrits();
            }
            
            $month = (int)$event->getDateDebut()->format('n');
            if ($event->getDateDebut()->format('Y') == $now->format('Y')) {
                $monthlyAttendance[$month] += $event->getInscrits();
            }
        }

        // Find best month
        foreach ($monthlyAttendance as $m => $total) {
            if ($total > $bestMonth['total']) {
                $bestMonth = ['month' => $m, 'total' => $total];
            }
        }

        // 2. AI Alerts & Timing
        $alerts = $alertGenerator->generateAlerts();
        $timingSuggestions = $timingAnalyzer->suggestOptimalTiming();

        // 3. Predictions for upcoming events
        $upcomingEvents = $eventRepository->findBy(['statut' => 'planifié'], ['dateDebut' => 'ASC'], 5);
        $eventPredictions = [];
        foreach ($upcomingEvents as $ue) {
            $eventPredictions[] = [
                'event' => $ue,
                'prediction' => $successPredictor->predictSuccess($ue)
            ];
        }

        return $this->render('back/dashboard/index.html.twig', [
            'total_events' => count($events),
            'total_registrations' => count($registrations),
            'total_revenue' => $totalRevenue,
            'avg_attendance' => count($events) > 0 ? array_sum($monthlyAttendance) / count($events) : 0,
            'best_month' => $bestMonth,
            'alerts' => $alerts,
            'timing_suggestions' => $timingSuggestions,
            'event_predictions' => $eventPredictions,
            'chart_data' => array_values($monthlyAttendance),
            'recent_sponsors' => $sponsorRepository->findBy([], ['id' => 'DESC'], 5),
        ]);
    }

    #[Route('/notifications/clear', name: 'back_clear_notifications')]
    public function clearNotifications(\App\Service\NotificationService $notificationService): Response
    {
        $notificationService->clearAdminNotifications();
        $this->addFlash('success', 'Notifications effacées.');
        return $this->redirectToRoute('back_dashboard');
    }
}