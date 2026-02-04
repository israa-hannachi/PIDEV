<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    #[Route('/chat', name: 'app_front_chat')]
    public function chat(): Response
    {
        return $this->render('front/chat.html.twig');
    }

    #[Route('/api/chat', name: 'app_front_api_chat', methods: ['POST'])]
    public function apiChat(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userMessage = $data['message'] ?? '';

        if (empty($userMessage)) {
            return new JsonResponse(['error' => 'No message provided'], 400);
        }

        try {
            $apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
            
            // If no valid API key, provide demo responses
            if (!$apiKey) {
                return $this->getDemoResponse($userMessage);
            }

            return $this->callGeminiAPI($userMessage, $apiKey);

        } catch (\Exception $e) {
            return new JsonResponse([
                'response' => 'Sorry, I encountered an error: ' . $e->getMessage(),
                'isDemo' => true
            ]);
        }
    }

    private function callGeminiAPI(string $userMessage, string $apiKey): JsonResponse
    {
        $postData = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $userMessage]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 1024,
            ]
        ]);

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . urlencode($apiKey);

        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => [
                    'Content-Type: application/json',
                ],
                'content' => $postData,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return $this->getDemoResponse($userMessage);
        }

        $body = json_decode($response, true);
        
        // Check if response has candidates with content
        if (isset($body['candidates'][0]['content']['parts'][0]['text'])) {
            $responseText = $body['candidates'][0]['content']['parts'][0]['text'];
            return new JsonResponse([
                'response' => $responseText,
                'isDemo' => false
            ]);
        }

        // Handle API errors
        if (isset($body['error'])) {
            return $this->getDemoResponse($userMessage);
        }

        // Fall back to demo if API fails
        return $this->getDemoResponse($userMessage);
    }

    private function getDemoResponse(string $userMessage): JsonResponse
    {
        $responses = [
            'event' => 'Events are gatherings where people connect and share experiences. We manage registrations, sponsors, and all event details through our platform.',
            'register' => 'To register for an event, click on any event in our catalog and fill out the registration form with your details.',
            'sponsor' => 'Sponsors help support events! We have different sponsorship tiers: gold, silver, bronze, and partner levels.',
            'ticket' => 'Event tickets are managed through our registration system. Each event has its own capacity and pricing.',
            'price' => 'Event prices vary depending on the type and duration. Check individual events for pricing details.',
            'date' => 'You can filter events by date in our catalog. Most upcoming events are listed with their start and end dates.',
            'location' => 'Events have various locations. Check the event details to see where it will be held.',
            'capacity' => 'Each event has a maximum capacity. You can see the current registration numbers and availability.',
            'help' => 'I can help you with information about events, registration, sponsors, tickets, and more. What would you like to know?',
            'hi' => 'Hello! Welcome to our Event Management platform. How can I help you today?',
            'hello' => 'Hello! Welcome to our Event Management platform. How can I help you today?',
        ];

        $lowerMessage = strtolower($userMessage);
        $response = 'I\'m here to help! You can ask me about events, registration, sponsors, tickets, dates, locations, and more. What would you like to know?';

        foreach ($responses as $keyword => $msg) {
            if (strpos($lowerMessage, $keyword) !== false) {
                $response = $msg;
                break;
            }
        }

        return new JsonResponse([
            'response' => $response,
            'isDemo' => true
        ]);
    }
}
