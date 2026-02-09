<?php
require_once __DIR__.'/vendor/autoload_runtime.php';

use App\Entity\User;
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

return function (array $context) {
    $kernel = new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
    $kernel->boot();
    $container = $kernel->getContainer();
    $entityManager = $container->get('doctrine')->getManager();

    echo "Testing User entity operations...\n";

    // 1. Create a test user
    $user = new User();
    $user->setNom("Test");
    $user->setPrenom("User");
    $user->setEmail("test_" . time() . "@example.com");
    $user->setMotDePasse("password123");
    $user->setRole("ADMIN");
    $user->setStatut("ACTIF");

    $entityManager->persist($user);
    $entityManager->flush();

    echo "User persisted with ID: " . $user->getId() . "\n";

    // 2. Fetch the user back
    $repository = $entityManager->getRepository(User::class);
    $fetchedUser = $repository->find($user->getId());

    if ($fetchedUser && $fetchedUser->getNom() === "Test") {
        echo "Verification SUCCESS: User correctly saved and retrieved.\n";
    } else {
        echo "Verification FAILED: Could not retrieve user or data mismatch.\n";
        exit(1);
    }

    return 0;
};
