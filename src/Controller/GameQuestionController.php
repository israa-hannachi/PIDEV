<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\GameQuestion;
use App\Form\GameQuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// ⚠️ Import correct pour les attributs
use Symfony\Component\Routing\Attribute\Route;

class GameQuestionController extends AbstractController
{
    #[Route('back/gamequestion', name: 'gamequestion_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $questions = $em->getRepository(GameQuestion::class)->findAll();
        return $this->render('backoffice/game_question/index.html.twig', [
            'questions' => $questions,
        ]);
    }

    #[Route('/gamequestion/new', name: 'gamequestion_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $question = new GameQuestion();
        

        $form = $this->createForm(GameQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($question);
            $em->flush();
            return $this->redirectToRoute('game_index');
        }

        return $this->render('backoffice/game_question/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('back/gamequestion/{id}/edit', name: 'gamequestion_edit')]
    public function edit(GameQuestion $question, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GameQuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('game_index');
        }

        return $this->render('backoffice/game_question/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('back/gamequestion/{id}/delete', name: 'gamequestion_delete')]
    public function delete(GameQuestion $question, EntityManagerInterface $em): Response
    {
        $em->remove($question);
        $em->flush();
        return $this->redirectToRoute('game_index');
    }
}
