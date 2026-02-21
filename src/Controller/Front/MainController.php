<?php
// src/Controller/Front/MainController.php

namespace App\Controller\Front;

use App\Repository\EventRepository;
use App\Repository\SponsorRepository;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(
        EventRepository $eventRepository, 
        SponsorRepository $sponsorRepository, 
        \App\Repository\RegistrationRepository $registrationRepository,
        \App\Repository\RecommendationCacheRepository $recommendationCacheRepository,
        \App\Service\EventRecommendationEngine $recommendationEngine
    ): Response
    {
        $recommendations = [];
        $collab_recommendations = [];

        if ($this->getUser()) {
            $cacheItems = $recommendationCacheRepository->findBy(['user' => $this->getUser(), 'isValid' => true], ['matchScore' => 'DESC'], 6);
            if (empty($cacheItems)) {
                $recs = $recommendationEngine->getRecommendationsForUser($this->getUser(), 6);
                foreach ($recs as $rec) {
                    $recommendations[] = [
                        'event' => $rec['event'],
                        'score' => $rec['score'],
                        'explanation' => $rec['explanation']
                    ];
                }
            } else {
                foreach ($cacheItems as $item) {
                    $recommendations[] = [
                        'event' => $item->getEvent(),
                        'score' => $item->getMatchScore(),
                        'explanation' => current($item->getExplanations()) ?: "Recommandé pour vous."
                    ];
                }
            }

            // Split into Profile/History vs Collaborative
            $filteredRecs = [];
            foreach ($recommendations as $rec) {
                if (str_contains(mb_strtolower($rec['explanation']), mb_strtolower("participants ayant le même profil"))) {
                    $collab_recommendations[] = $rec;
                } else {
                    $filteredRecs[] = $rec;
                }
            }
            $recommendations = $filteredRecs;

            // Get registrations for home view buttons
            $userRegistrations = $registrationRepository->findBy([
                'visitorEmail' => $this->getUser()->getEmail(),
                'statut' => ['en_attente', 'confirmé', 'inscrit'],
            ]);
            $userRegisteredEventIds = [];
            foreach ($userRegistrations as $reg) {
                $userRegisteredEventIds[$reg->getEvenement()->getId()] = [
                    'id' => $reg->getId(),
                    'status' => $reg->getStatut()
                ];
            }
        } else {
            $userRegisteredEventIds = [];
        }

        return $this->render('front/events/home.html.twig', [
            'sponsors' => $sponsorRepository->findBy(['statut' => 'actif']),
            'recent_events' => $eventRepository->findBy([], ['dateDebut' => 'DESC'], 6),
            'featured_events' => $eventRepository->findBy(['statut' => 'planifié'], ['inscrits' => 'DESC'], 3),
            'recommendations' => $recommendations,
            'collab_recommendations' => $collab_recommendations,
            'user_registered_event_ids' => $userRegisteredEventIds,
        ]);
    }

    #[Route('/log-interaction', name: 'app_front_event_log_interaction', methods: ['POST'])]
    public function logInteraction(
        Request $request, 
        EventRepository $eventRepository,
        \App\Service\UserBehaviorTracker $behaviorTracker
    ): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $eventId = $data['eventId'] ?? null;
        $type = $data['type'] ?? null;

        if (!$eventId || !$type) {
            return new JsonResponse(['error' => 'Missing parameters'], 400);
        }

        $event = $eventRepository->find($eventId);
        if (!$event) {
            return new JsonResponse(['error' => 'Event not found'], 404);
        }

        // We specifically want to handle 'not_interested' right now
        if ($type === 'not_interested') {
            $behaviorTracker->logNotInterested($user, $event);
        }

        return new JsonResponse(['success' => true]);
    }

    #[Route('/events', name: 'app_front_events')]
    public function events(
        EventRepository $eventRepository, 
        \App\Repository\RegistrationRepository $registrationRepository,
        \App\Repository\RecommendationCacheRepository $recommendationCacheRepository,
        \App\Service\EventRecommendationEngine $recommendationEngine
    ): Response
    {
        $events = $eventRepository->findBy([], ['dateDebut' => 'ASC']);

        // Get current user's registrations
        $userRegistrations = [];
        $userRegisteredEventIds = [];
        if ($this->getUser()) {
            $userRegistrations = $registrationRepository->findBy([
                'visitorEmail' => $this->getUser()->getEmail(),
                'statut' => ['en_attente', 'confirmé', 'inscrit'],
            ]);
            foreach ($userRegistrations as $reg) {
                $userRegisteredEventIds[$reg->getEvenement()->getId()] = [
                    'id' => $reg->getId(),
                    'status' => $reg->getStatut()
                ];
            }
        }

        // Build calendar events JSON
        $calendarEvents = [];
        foreach ($events as $event) {
            $isRegistered = isset($userRegisteredEventIds[$event->getId()]);
            $calendarEvents[] = [
                'id' => $event->getId(),
                'title' => $event->getTitre(),
                'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $event->getDateFin()->format('Y-m-d\TH:i:s'),
                'color' => $isRegistered ? '#10b981' : '#6366f1',
                'extendedProps' => [
                    'location' => $event->getLieu(),
                    'category' => $event->getCategorie(),
                    'price' => $event->getPrix(),
                    'capacity' => $event->getCapacite(),
                    'registered' => $event->getInscrits(),
                    'isUserRegistered' => $isRegistered,
                    'registrationId' => $isRegistered ? $userRegisteredEventIds[$event->getId()] : null,
                ],
            ];
        }

        $recommendations = [];
        $collabRecommendations = [];

        if ($this->getUser()) {
            $cacheItems = $recommendationCacheRepository->findBy(['user' => $this->getUser(), 'isValid' => true], ['matchScore' => 'DESC'], 10);
            
            if (empty($cacheItems)) {
                $recs = $recommendationEngine->getRecommendationsForUser($this->getUser(), 10);
                foreach ($recs as $rec) {
                    $recommendations[] = [
                        'event' => $rec['event'],
                        'score' => $rec['score'],
                        'explanation' => $rec['explanation']
                    ];
                }
            } else {
                foreach ($cacheItems as $item) {
                    $recommendations[] = [
                        'event' => $item->getEvent(),
                        'score' => $item->getMatchScore(),
                        'explanation' => current($item->getExplanations()) ?: "Recommandé pour vous."
                    ];
                }
            }

            $filteredRecs = [];
            foreach ($recommendations as $rec) {
                if (str_contains(mb_strtolower($rec['explanation']), mb_strtolower("participants ayant le même profil"))) {
                    $collabRecommendations[] = $rec;
                } else {
                    $filteredRecs[] = $rec;
                }
            }
            $recommendations = $filteredRecs;
        }

        return $this->render('front/events/index.html.twig', [
            'events' => $events,
            'sponsors' => [],
            'new_events_count' => 0,
            'recommendations' => array_slice($recommendations, 0, 3),
            'collab_recommendations' => array_slice($collabRecommendations, 0, 3),
            'calendar_events_json' => json_encode($calendarEvents),
            'user_registered_event_ids' => $userRegisteredEventIds,
        ]);
    }

    #[Route('/categories', name: 'app_front_categories')]
    public function categories(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findBy([], ['dateDebut' => 'ASC']);
        $categories = [];
        foreach ($events as $event) {
            $cat = $event->getCategorie();
            if ($cat && !in_array($cat, $categories)) {
                $categories[] = $cat;
            }
        }

        return $this->render('front/events/categories.html.twig', [
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    #[Route('/meet', name: 'app_front_meet')]
    public function meet(): Response
    {
        return $this->render('front/events/meet.html.twig');
    }

    #[Route('/jeux', name: 'app_front_jeux')]
    public function jeux(): Response
    {
        return $this->render('front/events/jeux.html.twig');
    }
}

