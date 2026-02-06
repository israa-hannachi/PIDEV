<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/game')]
class GameController extends AbstractController
{
    #[Route('/', name: 'admin_game_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/game/index.html.twig', [
            'title' => 'Games'
        ]);
    }
}
