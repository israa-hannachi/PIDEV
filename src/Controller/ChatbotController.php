<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    #[Route('/forum/chatbot', name: 'app_forum_chatbot', methods: ['GET'])]
    public function chatbot(): Response
    {
        // Solution FINALE : afficher directement le HTML sans redirection
        $htmlContent = file_get_contents(__DIR__ . '/../../public/chatbot.html');
        return new Response($htmlContent);
    }
}
