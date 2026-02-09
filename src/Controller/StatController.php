<?php

namespace App\Controller;

use App\Entity\Game;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StatController extends AbstractController
{
  #[Route('/statistique', name: 'statistique')]
public function index(EntityManagerInterface $em): Response
{
    $games = $em->getRepository(Game::class)->findAll();

    $labels = [];
    $scores = [];
    $attempts = [];

    $bestGame = null;
    $worstGame = null;
    $mostAttempted = null;
    $leastAttempted = null;

    foreach ($games as $game) {
        if ($game->getAvgScore() !== null && $game->getAttemptNumber() !== null) {
            $labels[] = $game->getTitre();
            $scores[] = $game->getAvgScore();
            $attempts[] = $game->getAttemptNumber();

            // Analyse intelligente
            if ($bestGame === null || $game->getAvgScore() > $bestGame->getAvgScore()) {
                $bestGame = $game;
            }
            if ($worstGame === null || $game->getAvgScore() < $worstGame->getAvgScore()) {
                $worstGame = $game;
            }
            if ($mostAttempted === null || $game->getAttemptNumber() > $mostAttempted->getAttemptNumber()) {
                $mostAttempted = $game;
            }
            if ($leastAttempted === null || $game->getAttemptNumber() < $leastAttempted->getAttemptNumber()) {
                $leastAttempted = $game;
            }
        }
    }

    return $this->render('stat/index.html.twig', [
        'labels' => $labels,
        'scores' => $scores,
        'attempts' => $attempts,
        'bestGame' => $bestGame,
        'worstGame' => $worstGame,
        'mostAttempted' => $mostAttempted,
        'leastAttempted' => $leastAttempted,
    ]);
}
}
