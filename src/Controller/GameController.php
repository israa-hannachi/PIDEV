<?php

namespace App\Controller;

use App\Entity\Game;
use App\Form\GameType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
// ⚠️ Import correct pour les attributs PHP 8+
use Symfony\Component\Routing\Attribute\Route;

class GameController extends AbstractController
{
    #[Route('back/game', name: 'game_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $games = $em->getRepository(Game::class)->findAll();
        return $this->render('backoffice/game/index.html.twig', [
            'games' => $games,
        ]);
    }
#[Route('back/game/new', name: 'game_new')]
     public function new(Request $request, EntityManagerInterface $em): Response
    {
        $game = new Game();
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($game);
            $em->flush();
            return $this->redirectToRoute('game_index');
        }

        return $this->render('backoffice/game/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
  #[Route('back/game/{id}/edit', name: 'game_edit')]
    public function edit(Game $game, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(GameType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('game_index');
        }

        return $this->render('backoffice/game/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/game/{id}/delete', name: 'game_delete')]
    public function delete(Game $game, EntityManagerInterface $em): Response
    {
        $em->remove($game);
        $em->flush();
        return $this->redirectToRoute('game_index');
    }
}
