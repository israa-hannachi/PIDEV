<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameQuestion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
final class GameFrontController extends AbstractController
{
    #[Route('/front/game/list', name: 'game_front_list')]
    public function list(EntityManagerInterface $em): Response
    {
        // Récupérer tous les jeux
        $games = $em->getRepository(Game::class)->findAll();

        // Envoyer à la vue Twig
        return $this->render('frontoffice/game/list.html.twig', [
            'games' => $games,
        ]);
    }

#[Route('/front/game/{id}/play', name: 'game_play')]
public function play(Game $game, Request $request, EntityManagerInterface $em): Response
{
    $questions = $em->getRepository(GameQuestion::class)->findBy(['game' => $game]);
    $score = null;

    if ($request->isMethod('POST')) {
        $score = 0;

        foreach ($questions as $question) {
            $userAnswer = $request->request->get('q' . $question->getId());
            if ($userAnswer !== null && $userAnswer === $question->getCorrectAnswer()) {
                $score++;
            }
        }

        // 🔹 Mettre à jour les champs simples
        $game->setLastScore($score);

        if ($game->getAvgScore() === null) {
            $game->setAvgScore($score);
        } else {
            // version simple : moyenne entre l'ancienne valeur et le nouveau score
            $newAvg = ($game->getAvgScore() + $score) / 2;
            $game->setAvgScore($newAvg);
        }

        $em->persist($game);
        $em->flush();
    }

    return $this->render('frontoffice/game/play.html.twig', [
        'game' => $game,
        'questions' => $questions,
        'score' => $score,
    ]);
}




}
