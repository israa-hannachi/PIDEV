<?php


// src/Controller/Api/ChatbotController.php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\ChatbotService;

#[Route('/api/chatbot')]
class ChatbotController extends AbstractController
{
    #[Route('/', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Chatbot API endpoint',
            'endpoints' => [
                'POST /api/chatbot/send' => 'Send a message to the chatbot'
            ]
        ]);
    }

    #[Route('/front/forum', methods: ['GET'])]
    public function chatInterface(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('frontoffice/chatbot.html.twig');
    }

    #[Route('/send', methods: ['POST'])]
    public function send(Request $request, ChatbotService $chatbot): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';

        if (!$userMessage) {
            return $this->json(['error' => 'Message manquant'], 400);
        }

        try {
            $response = $chatbot->getResponse($userMessage);
        } catch (\Throwable $e) {
            return $this->json(['error' => 'Service indisponible'], 500);
        }

        return $this->json([
            'user' => $userMessage,
            'bot' => $response
        ]);
    }
}
