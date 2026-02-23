<?php

namespace App\Command;

use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsCommand(
    name: 'app:seed-events',
    description: 'Seeds the database with sample events for demonstration purposes',
)]
class SeedEventsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $eventsData = [
            [
                'titre' => 'Concert de Jazz au Clair de Lune',
                'description' => 'Une soirée inoubliable avec les meilleurs musiciens de jazz de la région. Venez découvrir des mélodies envoûtantes sous les étoiles.',
                'lieu' => 'Théâtre de Carthage',
                'categorie' => 'Musique',
                'prix' => '45.00',
                'capacite' => 200,
                'days_offset' => 5,
                'hour' => 19,
            ],
            [
                'titre' => 'Hackathon Web 2026',
                'description' => '48 heures de coding intensif pour créer le futur du web. Tous les développeurs fullstack sont les bienvenus pour relever le défi.',
                'lieu' => 'Technopole El Ghazala',
                'categorie' => 'Technologie',
                'prix' => '0.00',
                'capacite' => 50,
                'days_offset' => 12,
                'hour' => 9,
            ],
            [
                'titre' => 'Atelier de Peinture Impressionniste',
                'description' => 'Apprenez les techniques des maîtres de l\'impressionnisme dans cet atelier pratique ouvert à tous les niveaux.',
                'lieu' => 'Galerie d\'Art de Sidi Bou Saïd',
                'categorie' => 'Art',
                'prix' => '30.00',
                'capacite' => 15,
                'days_offset' => 2,
                'hour' => 14,
            ],
            [
                'titre' => 'Masterclass IA & Business',
                'description' => 'Comprendre comment l\'intelligence artificielle transforme le monde des affaires aujourd\'hui. Une formation intensive pour les leaders.',
                'lieu' => 'Hôtel Laico Tunis',
                'categorie' => 'Formation',
                'prix' => '150.00',
                'capacite' => 100,
                'days_offset' => 20,
                'hour' => 10,
            ],
            [
                'titre' => 'Tournoi de Football de Quartier',
                'description' => 'Un grand tournoi convivial pour célébrer le sport et l\'esprit d\'équipe. Matchs rapides et ambiance festive garantis.',
                'lieu' => 'Stade Municipal de l\'Ariana',
                'categorie' => 'Sport',
                'prix' => '10.00',
                'capacite' => 80,
                'days_offset' => -3, // Past event
                'hour' => 17,
            ],
        ];

        foreach ($eventsData as $data) {
            $event = new Event();
            $event->setTitre($data['titre']);
            $event->setDescription($data['description']);
            $event->setLieu($data['lieu']);
            $event->setCategorie($data['categorie']);
            $event->setPrix($data['prix']);
            $event->setCapacite($data['capacite']);
            $event->setStatut('publié');

            $startDate = new \DateTime();
            $startDate->modify($data['days_offset'] . ' days');
            $startDate->setTime($data['hour'], 0);
            $event->setDateDebut($startDate);

            $endDate = clone $startDate;
            $endDate->modify('+2 hours');
            $event->setDateFin($endDate);

            // Assuming there's a setSlug method or it's handled automatically
            // If not, we can just set it manually for seeding
            if (method_exists($event, 'setSlug')) {
                $event->setSlug($this->slugger->slug($data['titre'])->lower());
            }

            $this->entityManager->persist($event);
        }

        $this->entityManager->flush();

        $io->success('Successfully added ' . count($eventsData) . ' sample events to the database.');

        return Command::SUCCESS;
    }
}
