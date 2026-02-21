<?php

namespace App\Service;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\AIModelAdjustmentRepository;

class EventSuccessPredictor
{
    private $timingAnalyzer;

    public function __construct(
        EventRepository $eventRepository, 
        AIModelAdjustmentRepository $adjustmentRepository,
        EventTimingAnalyzer $timingAnalyzer
    ) {
        $this->eventRepository = $eventRepository;
        $this->adjustmentRepository = $adjustmentRepository;
        $this->timingAnalyzer = $timingAnalyzer;
    }

    /**
     * Calculates a success score (0-100) for an event using 5 weighted factors.
     */
    public function predictSuccess(Event $event): array
    {
        $category = $event->getCategorie();
        
        $factors = [
            'history' => $this->calculateHistoryFactor($event),     // 30%
            'timing' => $this->calculateTimingFactor($event),       // 25%
            'audience' => $this->calculateAudienceFactor($event),   // 20%
            'competition' => $this->calculateCompetitionFactor($event), // 15%
            'seasonal' => $this->calculateSeasonalFactor($event)    // 10%
        ];

        // Apply learned adjustments from AIModelAdjustment
        $adjustments = $this->adjustmentRepository->findBy(['isActive' => true]);
        foreach ($adjustments as $adj) {
            if (isset($factors[$adj->getFactorType()])) {
                $factors[$adj->getFactorType()] *= $adj->getAdjustmentMultiplier();
                $factors[$adj->getFactorType()] = min(100, max(0, $factors[$adj->getFactorType()]));
            }
        }

        $totalScore = ($factors['history'] * 0.30) + 
                     ($factors['timing'] * 0.25) + 
                     ($factors['audience'] * 0.20) + 
                     ($factors['competition'] * 0.15) + 
                     ($factors['seasonal'] * 0.10);

        $totalScore = min(100, max(0, $totalScore));

        return [
            'total_score' => round($totalScore, 1),
            'factors' => $factors,
            'suggestions' => $this->generateSuggestions($event, $factors)
        ];
    }

    private function calculateHistoryFactor(Event $event): float
    {
        $pastEvents = $this->eventRepository->findBy(['categorie' => $event->getCategorie()]);
        $rates = [];
        
        foreach ($pastEvents as $past) {
            if ($past->getCapacite() > 0 && $past->getDateDebut() < new \DateTime()) {
                $rates[] = ($past->getInscrits() / $past->getCapacite()) * 100;
            }
        }

        if (empty($rates)) return 60.0; // Optimistic baseline for new categories

        return array_sum($rates) / count($rates);
    }

    private function calculateTimingFactor(Event $event): float
    {
        $suggestions = $this->timingAnalyzer->suggestOptimalTiming($event->getCategorie());
        if (empty($suggestions)) return 70.0;

        $eventDay = $this->getDayName((int)$event->getDateDebut()->format('w'));
        $eventHour = (int)$event->getDateDebut()->format('H');
        $eventSlot = 'evening';
        if ($eventHour < 12) $eventSlot = 'morning';
        elseif ($eventHour < 17) $eventSlot = 'afternoon';

        // Find how close current slot is to the best slot
        $bestScore = $suggestions[0]['score'];
        
        foreach ($suggestions as $sug) {
            if ($sug['day'] === $eventDay && $sug['slot'] === $eventSlot) {
                return ($sug['score'] / max(1, $bestScore)) * 100;
            }
        }

        return ($suggestions[2]['score'] / max(1, $bestScore)) * 70; // Poor timing penalty
    }

    private function calculateAudienceFactor(Event $event): float
    {
        // Integration with User interest count would go here
        // Using a mock calculation based on capacity vs platform volume
        $targetCapacity = $event->getCapacite() ?: 50;
        if ($targetCapacity < 20) return 90.0;
        if ($targetCapacity < 100) return 75.0;
        return 60.0;
    }

    private function calculateCompetitionFactor(Event $event): float
    {
        $start = clone $event->getDateDebut();
        $end = clone $event->getDateDebut();
        $start->modify('-7 days');
        $end->modify('+7 days');

        $competing = $this->eventRepository->createQueryBuilder('e')
            ->where('e.dateDebut BETWEEN :start AND :end')
            ->andWhere('e.id != :id')
            ->andWhere('e.statut = :status')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->setParameter('id', $event->getId() ?: 0)
            ->setParameter('status', 'planifié')
            ->getQuery()
            ->getResult();

        $penalty = count($competing) * 12;
        return max(0, 100 - $penalty);
    }

    private function calculateSeasonalFactor(Event $event): float
    {
        $month = (int)$event->getDateDebut()->format('n');
        $weights = [
            1 => 80, 2 => 85, 3 => 95, 4 => 85, 5 => 80, 6 => 60,
            7 => 40, 8 => 45, 9 => 95, 10 => 90, 11 => 85, 12 => 70
        ];
        return $weights[$month] ?? 70;
    }

    private function generateSuggestions(Event $event, array $factors): array
    {
        $suggestions = [];
        
        if ($factors['timing'] < 75) {
            $best = $this->timingAnalyzer->suggestOptimalTiming($event->getCategorie())[0] ?? null;
            if ($best) {
                $suggestions[] = "Déplacez l'événement au {$best['day']} ({$best['slot']}) pour augmenter le score de " . round(100 - $factors['timing']) . "%.";
            }
        }

        if ($factors['competition'] < 60) {
            $suggestions[] = "Trop de concurrence cette semaine-là. Envisagez de décaler de +/- 10 jours.";
        }

        if ($factors['history'] < 50) {
            $suggestions[] = "La catégorie {$event->getCategorie()} a historiquement un faible taux de conversion. Augmentez le budget marketing.";
        }

        if ($factors['audience'] < 70) {
            $suggestions[] = "La capacité demandée est élevée pour l'audience actuelle. Réduisez à 30-40 places pour un 'Sold Out' garanti.";
        }

        return $suggestions;
    }

    private function getDayName(int $day): string
    {
        return ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'][$day];
    }
}
