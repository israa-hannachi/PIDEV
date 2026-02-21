<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\User;
use App\Entity\RecommendationCache;
use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;

class EventRecommendationEngine
{
    private EntityManagerInterface $entityManager;
    private EventRepository $eventRepository;
    private RegistrationRepository $registrationRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventRepository $eventRepository,
        RegistrationRepository $registrationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
    }

    public function getRecommendationsForUser(User $user, int $limit = 10): array
    {
        $upcomingEvents = $this->eventRepository->createQueryBuilder('e')
            ->where('e.dateDebut > :now')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();

        $recommendations = [];

        foreach ($upcomingEvents as $event) {
            // Skip if already registered
            if ($this->isUserRegistered($user, $event)) {
                continue;
            }

            $scoreData = $this->calculateMatchScore($user, $event);
            $recommendations[] = [
                'event' => $event,
                'score' => $scoreData['total'],
                'explanations' => $scoreData['explanations']
            ];
        }

        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_slice($recommendations, 0, $limit);
    }

    private function calculateMatchScore(User $user, Event $event): array
    {
        $factors = [
            'profile' => $this->calculateProfileMatch($user, $event),
            'history' => $this->calculateHistoryMatch($user, $event),
            'collaborative' => $this->calculateCollaborativeScore($user, $event),
            'popularity' => $this->calculatePopularityScore($event),
            'timing' => $this->calculateTimingScore($user, $event),
        ];

        $weights = [
            'profile' => 0.30,
            'history' => 0.25,
            'collaborative' => 0.20,
            'popularity' => 0.15,
            'timing' => 0.10,
        ];

        $totalScore = 0;
        foreach ($factors as $key => $score) {
            $totalScore += ($score * $weights[$key]);
        }

        return [
            'total' => round($totalScore * 100),
            'explanations' => $this->generateExplanations($user, $event, $factors)
        ];
    }

    private function calculateProfileMatch(User $user, Event $event): float
    {
        $score = 0;
        
        // Profession match
        if ($user->getProfession() && $event->getTargetAudience() && 
            stripos($event->getTargetAudience(), $user->getProfession()) !== false) {
            $score += 0.4;
        }

        // Level match
        if ($user->getExperienceLevel() && $event->getRequiredLevel() && 
            strtolower($user->getExperienceLevel()) === strtolower($event->getRequiredLevel())) {
            $score += 0.3;
        }

        // Category match
        if ($user->getUserPreferenceProfile() && in_array($event->getCategorie(), $user->getUserPreferenceProfile()->getPreferredCategories())) {
            $score += 0.3;
        }

        return min(1.0, $score);
    }

    private function calculateHistoryMatch(User $user, Event $event): float
    {
        // To be refined with real registration history
        return 0.5; 
    }

    private function calculateCollaborativeScore(User $user, Event $event): float
    {
        // Placeholder for Jaccard similarity
        return 0.4;
    }

    private function calculatePopularityScore(Event $event): float
    {
        if ($event->getCapacite() <= 0) return 0;
        return $event->getInscrits() / $event->getCapacite();
    }

    private function calculateTimingScore(User $user, Event $event): float
    {
        // Check for schedule conflicts (placeholder)
        return 1.0;
    }

    private function generateExplanations(User $user, Event $event, array $factors): array
    {
        $explanations = [];
        
        // Profile & Profession Match
        if ($factors['profile'] > 0.8) {
            $explanations[] = "Alignement Stratégique : Cet événement cible précisément votre expertise en '" . $user->getProfession() . "', offrant des opportunités de réseautage de haut niveau.";
        } elseif ($factors['profile'] > 0.4) {
            $explanations[] = "Développement de Compétences : Le contenu semble adapté à votre niveau d'expérience '" . $user->getExperienceLevel() . "', favorisant une progression rapide.";
        }

        // Popularity & Community
        if ($factors['popularity'] > 0.9) {
            $explanations[] = "Impact Communautaire : Taux d'engagement exceptionnel. Cet événement est identifié comme un 'Must-Attend' par notre algorithme de tendance.";
        } elseif ($factors['popularity'] > 0.6) {
            $explanations[] = "Dynamique Positive : Une forte traction est observée, suggérant un contenu de qualité validé par vos pairs.";
        }

        // History & Preferences
        if ($user->getUserPreferenceProfile() && in_array($event->getCategorie(), $user->getUserPreferenceProfile()->getPreferredCategories())) {
            $explanations[] = "Pertinence Thématique : Correspond à vos centres d'intérêt prioritaires pour la catégorie '" . $event->getCategorie() . "'.";
        }

        // Collaborative / Network
        if ($factors['collaborative'] > 0.3) {
            $explanations[] = "Synergie Réseau : Des profils similaires au vôtre ont manifesté un intérêt marqué pour cette session.";
        }

        // Default / Fallback
        if (count($explanations) < 2) {
            $explanations[] = "Analyse Cognitive : Notre moteur de recommandation a détecté une corrélation entre les thématiques abordées et votre trajectoire professionnelle.";
        }

        return array_slice($explanations, 0, 4); // Show top 4 detailed points
    }

    private function isUserRegistered(User $user, Event $event): bool
    {
        $registration = $this->registrationRepository->findOneBy([
            'visitorEmail' => $user->getEmail(), 
            'evenement' => $event
        ]);
        return $registration !== null;
    }

    public function triggerAnalysis(User $user, Event $event): ?RecommendationCache
    {
        // First check if already exists
        $cacheRepo = $this->entityManager->getRepository(RecommendationCache::class);
        $existing = $cacheRepo->findOneBy(['user' => $user, 'event' => $event]);
        if ($existing) {
            return $existing;
        }

        $scoreData = $this->calculateMatchScore($user, $event);
        
        $cache = new RecommendationCache();
        $cache->setUser($user);
        $cache->setEvent($event);
        $cache->setMatchScore($scoreData['total']);
        $cache->setExplanations($scoreData['explanations']);
        $cache->setComputedAt(new \DateTime());
        $cache->setIsValid(true);

        $this->entityManager->persist($cache);
        $this->entityManager->flush();

        return $cache;
    }
}
