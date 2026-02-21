#!/usr/bin/env php
<?php
// Create sample events for testing

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

require __DIR__.'/vendor/autoload_runtime.php';

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? false));
$container = $kernel->getContainer();
$em = $container->get(EntityManagerInterface::class);

$now = new DateTime();
$startDate = (clone $now)->modify('+1 day');
$endDate = (clone $startDate)->modify('+2 hours');

$events = [
    [
        'titre' => 'Introduction to PHP',
        'description' => 'Learn the basics of PHP programming from scratch. This course covers variables, functions, and object-oriented programming.',
        'dateDebut' => (clone $startDate)->modify('+2 days'),
        'dateFin' => (clone $startDate)->modify('+2 days +3 hours'),
        'capacite' => 50,
        'lieu' => 'Room A, Tech Center',
        'categorie' => 'Programming',
        'prix' => 0.00,
        'statut' => 'planifié',
    ],
    [
        'titre' => 'Web Development with Symfony',
        'description' => 'Master modern web development using the Symfony framework. Build production-ready applications.',
        'dateDebut' => (clone $startDate)->modify('+3 days'),
        'dateFin' => (clone $startDate)->modify('+3 days +4 hours'),
        'capacite' => 30,
        'lieu' => 'Room B, Tech Center',
        'categorie' => 'Web Development',
        'prix' => 49.99,
        'statut' => 'planifié',
    ],
    [
        'titre' => 'Advanced React Patterns',
        'description' => 'Deep dive into advanced React patterns and best practices for scalable applications.',
        'dateDebut' => (clone $startDate)->modify('+4 days'),
        'dateFin' => (clone $startDate)->modify('+4 days +3 hours'),
        'capacite' => 25,
        'lieu' => 'Room C, Tech Center',
        'categorie' => 'Frontend',
        'prix' => 39.99,
        'statut' => 'planifié',
    ],
    [
        'titre' => 'Database Design Fundamentals',
        'description' => 'Learn how to design efficient and scalable databases. Covers relational, NoSQL, and cloud databases.',
        'dateDebut' => (clone $startDate)->modify('+5 days'),
        'dateFin' => (clone $startDate)->modify('+5 days +3 hours'),
        'capacite' => 40,
        'lieu' => 'Room D, Tech Center',
        'categorie' => 'Database',
        'prix' => 29.99,
        'statut' => 'planifié',
    ],
    [
        'titre' => 'Machine Learning Basics',
        'description' => 'Introduction to machine learning concepts and practical implementation with Python.',
        'dateDebut' => (clone $startDate)->modify('+6 days'),
        'dateFin' => (clone $startDate)->modify('+6 days +4 hours'),
        'capacite' => 35,
        'lieu' => 'Room E, Tech Center',
        'categorie' => 'AI & Machine Learning',
        'prix' => 59.99,
        'statut' => 'planifié',
    ],
];

foreach ($events as $eventData) {
    $event = new Event();
    $event->setTitre($eventData['titre']);
    $event->setDescription($eventData['description']);
    $event->setDateDebut($eventData['dateDebut']);
    $event->setDateFin($eventData['dateFin']);
    $event->setCapacite($eventData['capacite']);
    $event->setInscrits(0);
    $event->setLieu($eventData['lieu']);
    $event->setCategorie($eventData['categorie']);
    $event->setPrix($eventData['prix']);
    $event->setStatut($eventData['statut']);
    $event->setDateCreation(new DateTime());
    $event->setTimeZone('UTC');
    
    $em->persist($event);
    echo "Creating event: {$event->getTitre()}\n";
}

$em->flush();
echo "\n✅ Successfully created " . count($events) . " sample events!\n";
