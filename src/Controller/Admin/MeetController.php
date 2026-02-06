<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/meet')]
class MeetController extends AbstractController
{
    #[Route('/', name: 'admin_meet_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/meet/index.html.twig', [
            'title' => 'Meet'
        ]);
    }
}
