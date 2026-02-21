<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\User;
use App\Repository\RecommendationCacheRepository;
use App\Service\UserBehaviorTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecommendationController extends AbstractController
{
    #[Route('/recommendations', name: 'app_front_recommendations')]
    public function index(RecommendationCacheRepository $cacheRepository): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $recommendations = $cacheRepository->findBy(['user' => $user], ['matchScore' => 'DESC'], 6);

        return $this->render('front/recommendations/index.html.twig', [
            'recommendations' => $recommendations,
        ]);
    }

    #[Route('/api/track-view/{id}', name: 'app_api_track_view', methods: ['POST'])]
    public function trackView(Event $event, Request $request, UserBehaviorTracker $tracker): JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $duration = (int) $request->request->get('duration', 0);
            $tracker->trackEventView($user, $event, $duration);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/not-interested/{id}', name: 'app_api_not_interested', methods: ['POST'])]
    public function notInterested(Event $event, UserBehaviorTracker $tracker): JsonResponse
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $tracker->trackNotInterested($user, $event);
        }

        return new JsonResponse(['success' => true]);
    }
}
