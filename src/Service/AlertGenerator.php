<?php

namespace App\Service;

use App\Repository\EventRepository;
use App\Repository\RegistrationRepository;

class AlertGenerator
{
    private $eventRepository;
    private $registrationRepository;

    public function __construct(EventRepository $eventRepository, RegistrationRepository $registrationRepository)
    {
        $this->eventRepository = $eventRepository;
        $this->registrationRepository = $registrationRepository;
    }

    /**
     * Generates critical alerts for current events.
     */
    public function generateAlerts(): array
    {
        $alerts = [];
        $activeEvents = $this->eventRepository->findBy(['statut' => 'planifié']);
        $now = new \DateTime();

        foreach ($activeEvents as $event) {
            $daysToEvent = $now->diff($event->getDateDebut())->days;
            $fillRate = ($event->getInscrits() / $event->getCapacite()) * 100;

            // Alert 1: Low capacity near event
            if ($daysToEvent <= 14 && $fillRate < 50) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Faible taux de remplissage',
                    'message' => "L'événement \"{$event->getTitre()}\" est dans {$daysToEvent} jours mais n'est rempli qu'à " . round($fillRate) . "%.",
                    'event_id' => $event->getId()
                ];
            }

            // Alert 2: High capacity suggesting new session
            if ($fillRate >= 90) {
                $alerts[] = [
                    'type' => 'success',
                    'title' => 'Capacité presque atteinte',
                    'message' => "L'événement \"{$event->getTitre()}\" est à " . round($fillRate) . "%. Pensez à ouvrir une nouvelle session.",
                    'event_id' => $event->getId()
                ];
            }

            // Alert 3: Stagnant registrations
            $lastReg = $this->registrationRepository->findOneBy(
                ['evenement' => $event],
                ['dateInscription' => 'DESC']
            );

            if ($lastReg) {
                $daysSinceLast = $now->diff($lastReg->getDateInscription())->days;
                if ($daysSinceLast >= 7) {
                    $alerts[] = [
                        'type' => 'info',
                        'title' => 'Stagnation des inscriptions',
                        'message' => "Aucune nouvelle inscription pour \"{$event->getTitre()}\" depuis 1 semaine.",
                        'event_id' => $event->getId()
                    ];
                }
            } elseif ($now->diff($event->getDateCreation())->days >= 7) {
                 $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Aucune inscription',
                    'message' => "L'événement \"{$event->getTitre()}\" a été créé il y a 7 jours mais n'a aucune inscription.",
                    'event_id' => $event->getId()
                ];
            }
        }

        return $alerts;
    }
}
