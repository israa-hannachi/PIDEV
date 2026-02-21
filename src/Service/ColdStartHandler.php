<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\RegistrationRepository;

class ColdStartHandler
{
    private EventRecommendationEngine $recommendationEngine;
    private RegistrationRepository $registrationRepository;

    public function __construct(
        EventRecommendationEngine $recommendationEngine,
        RegistrationRepository $registrationRepository
    ) {
        $this->recommendationEngine = $recommendationEngine;
        $this->registrationRepository = $registrationRepository;
    }

    public function getRecommendedEvents(User $user, int $limit = 10): array
    {
        $registrationCount = $this->registrationRepository->count([
            'votre_email' => $user->getEmail()
        ]);

        if ($registrationCount >= 3) {
            return $this->recommendationEngine->getRecommendationsForUser($user, $limit);
        }

        // For users with < 3 registrations, blend logic
        // Simplified: use the engine but acknowledge it might lean on profile/popularity more
        return $this->recommendationEngine->getRecommendationsForUser($user, $limit);
    }
}
