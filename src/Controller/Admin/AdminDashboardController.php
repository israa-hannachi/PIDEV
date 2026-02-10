<?php

namespace App\Controller\Admin;

use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use App\Repository\SponsorRepository;
use App\Repository\CategorieRepository;
use App\Repository\ModuleRepository;
use App\Repository\CoursRepository;
use App\Repository\GameRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository,
        SponsorRepository $sponsorRepository,
        CategorieRepository $categorieRepository,
        ModuleRepository $moduleRepository,
        CoursRepository $coursRepository,
        GameRepository $gameRepository,
        NotificationService $notificationService
    ): Response {
        // Events Stats
        $events = $eventRepository->findAll();
        $registrations = $registrationRepository->findAll();
        $sponsors = $sponsorRepository->findAll();
        
        $totalRevenue = 0;
        foreach ($registrations as $reg) {
            if ($reg->getPaiementStatut() === 'payé' || $reg->getStatut() === 'confirmé') {
                $totalRevenue += (float)$reg->getMontantPaye();
            }
        }

        // Learning Stats
        $categories = $categorieRepository->findAll();
        $modules = $moduleRepository->findAll();
        $cours = $coursRepository->findAll();

        // Games Stats
        $games = $gameRepository->findAll();
        $avgScore = 0;
        if (count($games) > 0) {
            $totalScore = 0;
            $countWithScore = 0;
            foreach ($games as $game) {
                if ($game->getAvgScore() !== null) {
                    $totalScore += $game->getAvgScore();
                    $countWithScore++;
                }
            }
            $avgScore = $countWithScore > 0 ? $totalScore / $countWithScore : 0;
        }

        return $this->render('admin/dashboard/index.html.twig', [
            // Events
            'total_events' => count($events),
            'total_registrations' => count($registrations),
            'total_revenue' => $totalRevenue,
            
            // Learning
            'total_categories' => count($categories),
            'total_modules' => count($modules),
            'total_cours' => count($cours),
            
            // Games
            'total_games' => count($games),
            'global_avg_score' => round($avgScore, 2),
            
            // Notifications
            'admin_notifications' => $notificationService->getAdminNotifications(),
        ]);
    }
}
