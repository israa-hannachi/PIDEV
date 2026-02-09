<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ForumsController extends AbstractController
{
    #[Route('/forums', name: 'app_forums')]
    public function index(): Response
    {
        return $this->render('forums/index.html.twig', [
            'controller_name' => 'ForumsController',
        ]);
    }
}
