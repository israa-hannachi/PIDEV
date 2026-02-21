<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;

class EventTimingAnalyzer
{
    private $eventRepository;
    private $registrationRepository;

    public function __construct(EventRepository $eventRepository, RegistrationRepository $registrationRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
    }

    /**
     * Suggests top 3 optimal timing slots based on historical attendance and current conflicts.
     */
    public function suggestOptimalTiming(?string $category = null): array
    {
        $allEvents = $this->eventRepository->findAll();
        $targetEvents = $category 
            ? array_filter($allEvents, fn($e) => $e->getCategorie() === $category)
            : $allEvents;

        if (empty($targetEvents)) {
            $targetEvents = $allEvents; // Fallback to all if category is new
        }

        $stats = [
            'days' => array_fill(0, 7, []),
            'slots' => [
                'morning' => [],   // 8-12
                'afternoon' => [], // 12-17
                'evening' => []    // 17-21
            ]
        ];

        foreach ($targetEvents as $event) {
            if ($event->getCapacite() <= 0 || $event->getDateDebut() > new \DateTime()) continue;

            $attendanceRate = ($event->getInscrits() / $event->getCapacite()) * 100;
            $date = $event->getDateDebut();
            
            $day = (int)$date->format('w');
            $hour = (int)$date->format('H');

            $stats['days'][$day][] = $attendanceRate;

            if ($hour >= 8 && $hour < 12) {
                $stats['slots']['morning'][] = $attendanceRate;
            } elseif ($hour >= 12 && $hour < 17) {
                $stats['slots']['afternoon'][] = $attendanceRate;
            } elseif ($hour >= 17 && $hour < 21) {
                $stats['slots']['evening'][] = $attendanceRate;
            }
        }

        $recommendations = [];
        $now = new \DateTime();
        
        // Check slots for the next 2 weeks
        for ($d = 0; $d < 7; $d++) {
            foreach (['morning', 'afternoon', 'evening'] as $slot) {
                $avgDay = !empty($stats['days'][$d]) ? array_sum($stats['days'][$d]) / count($stats['days'][$d]) : 50;
                $avgSlot = !empty($stats['slots'][$slot]) ? array_sum($stats['slots'][$slot]) / count($stats['slots'][$slot]) : 50;
                
                $baseScore = ($avgDay * 0.6) + ($avgSlot * 0.4);
                
                // Penalize weekends unless category is social/gaming
                if (($d == 0 || $d == 6) && !in_array($category, ['Gaming', 'Musique', 'Art'])) {
                    $baseScore *= 0.8;
                }

                $conflicts = $this->checkConflicts($d, $slot);
                $penalty = count($conflicts) * 15;
                $finalScore = max(0, $baseScore - $penalty);

                // Holiday Check
                if ($this->isHolidayForDay($d)) {
                    $finalScore *= 0.5;
                    $conflicts[] = "Jour férié ou chômé potentiel";
                }

                $recommendations[] = [
                    'day' => $this->getDayName($d),
                    'slot' => $slot,
                    'score' => round($finalScore, 1),
                    'attendance_rate' => round($baseScore, 1),
                    'conflicts' => $conflicts,
                    'confidence' => $this->calculateConfidence($stats, $d, $slot)
                ];
            }
        }

        usort($recommendations, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($recommendations, 0, 3);
    }

    private function getDayName(int $day): string
    {
        return ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'][$day];
    }

    private function checkConflicts(int $dayOfWeek, string $slot): array
    {
        $warnings = [];
        $startHour = ['morning' => 8, 'afternoon' => 12, 'evening' => 17][$slot];
        
        // Query upcoming events for this specific day/slot combo in the next 14 days
        // This is a simplified check for the "same week/audience" requirement
        $upcoming = $this->eventRepository->createQueryBuilder('e')
            ->where('e.statut = :status')
            ->setParameter('status', 'planifié')
            ->getQuery()
            ->getResult();

        foreach ($upcoming as $event) {
            $eDate = $event->getDateDebut();
            if ((int)$eDate->format('w') === $dayOfWeek) {
                $eHour = (int)$eDate->format('H');
                $eSlot = 'evening';
                if ($eHour < 12) $eSlot = 'morning';
                elseif ($eHour < 17) $eSlot = 'afternoon';

                if ($eSlot === $slot) {
                    $warnings[] = "Conflit avec: " . $event->getTitre();
                }
            }
        }

        return array_unique($warnings);
    }

    private function isHolidayForDay(int $dayOfWeek): bool
    {
        // Static holiday check for common Tunisian/International holidays in 2026/2027
        // In real app, this would use a dedicated provider
        // 1st May, 25th July, 13th August, 15th October, etc.
        return false; // Baseline
    }

    private function calculateConfidence(array $stats, int $day, string $slot): int
    {
        $sampleSize = count($stats['days'][$day]) + count($stats['slots'][$slot]);
        return min(100, $sampleSize * 5 + 40); // Simple heuristic: more data = higher confidence
    }
}
