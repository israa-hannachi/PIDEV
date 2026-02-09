<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatut() !== 'ACTIF') {
            // Symfony will catch this and display a "Disabled" message
            throw new DisabledException('Your account is not active. Please contact the administrator.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // No checks needed after authentication for now
    }
}
