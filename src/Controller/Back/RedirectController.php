<?php

namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    #[Route('/back', name: 'back_dashboard_shortcut')]
    public function index(): Response
    {
        return $this->redirectToRoute('back_event_index');
    }
}
