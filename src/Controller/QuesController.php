<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\QuizService; // <-- AJOUTE CETTE LIGNE

final class QuesController extends AbstractController
{
#[Route('/ques', name: 'app_ques')]
public function index(Request $request, QuizService $quizService): Response
{
    $mapping = [
        'finance' => 9,
        'communication' => 9,
        'strategie' => 17,
        'gl' => 18,
        'mobile' => 18,
        'java' => 18,
        'bd' => 18,
        'ml' => 18,
        'maths' => 19
    ];

    $selected = $request->query->get('category');
    $type = $request->query->get('type', 'multiple');
    $lang = $request->query->get('lang', 'en');
    $questions = [];

    if ($selected) {
        [$matiere, $niveau] = explode('-', $selected);
        $category = $mapping[$matiere] ?? null;
        $difficulty = $niveau ?? null;

        // Open Trivia DB (toujours en anglais)
        $questions = $quizService->getQuestions(10, $category, $difficulty, $type);
    }

    return $this->render('ques/index.html.twig', [
        'questions' => $questions['results'] ?? [],
        'selected' => $selected,
        'type' => $type,
        'lang' => $lang,
    ]);
}



}
