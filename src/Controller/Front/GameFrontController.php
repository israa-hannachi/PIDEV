<?php

namespace App\Controller\Front;


use App\Entity\Game;
use App\Entity\GameQuestion;
use App\Service\QuizService;
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
        return $this->render('front/game/list.html.twig', [
            'games' => $games,
        ]);
    }

#[Route('/front/game/{id}/play', name: 'game_play')]

public function play(Game $game, Request $request, EntityManagerInterface $em, QuizService $quizService): Response
{
    $questions = $em->getRepository(GameQuestion::class)->findBy(['game' => $game]);
    $score = null;
$feedback = null;
    if ($request->isMethod('POST')) {
        $score = 0;
        

        foreach ($questions as $question) {
            $userAnswer = $request->request->get('q' . $question->getId());

            if ($userAnswer !== null) {
                // Vérification selon le type de question
                if ($game->getType() === "libre") {
                    // 👉 Ici on utilise le NLP
                    $similarityScore = $quizService->compareWithNLP($userAnswer, $question->getCorrectAnswer());

                    if ($similarityScore > 0.8) {
                        $score += 10;
                        $feedback = "Bonne réponse ! Tu as bien compris la notion.";
                    } elseif ($similarityScore > 0.5) {
                        $score += 5;
                        $feedback = "Ta réponse est proche. ";
                    } else {
                        $score += 0;
                        $feedback = "Réponse incorrecte. ";
                    }
                } else {
                    // 👉 Logique classique (QCM, Vrai/Faux, etc.)
                    if ($userAnswer === $question->getCorrectAnswer()) {
                        $score += 10;
                    }
                }
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

    return $this->render('front/game/play.html.twig', [
        'game' => $game,
        'questions' => $questions,
        'score' => $score,
        'feedback' => $feedback,
    ]);
}





}
