<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class TemplateBackDemoController extends AbstractController
{
    #[Route('/template-back-demo', name: 'app_template_back_demo', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('template_back/demo.html.twig');
    }
}
