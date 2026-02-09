<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MeetController extends AbstractController
{
    #[Route('/meet', name: 'app_meet')]
    public function index(): Response
    {
        return $this->render('meet/index.html.twig', [
            'controller_name' => 'MeetController',
        ]);
    }
}
