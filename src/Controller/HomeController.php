<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
        ]);
    }

    public function notifications(NotificationRepository $repository): Response
    {
        $notifications = $repository->findLatest(5);
        $count = count($notifications);

        return $this->render('partials/_notifications.html.twig', [
            'notifications' => $notifications,
            'count' => $count
        ]);
    }
}
