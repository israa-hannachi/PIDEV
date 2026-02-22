<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private HttpClientInterface $client;
    private string $hfToken;

    public function __construct(HttpClientInterface $client, string $hfToken)
    {
        $this->client = $client;
        $this->hfToken = $hfToken; // injecté via services.yaml ou .env
    }

    public function getResponse(string $message): string
    {
        $normalized = strtolower(trim($message));

        // Réponses simples basées sur des mots-clés
        if (preg_match('/\b(bonjour|salut|hello|hi|coucou)\b/', $normalized)) {
            return "Bonjour ! Comment puis-je vous aider aujourd'hui ?";
        }

        if (preg_match('/\b(aide|help|comment|puis|peux)\b/', $normalized)) {
            return "Je suis là pour vous aider ! Posez-moi vos questions.";
        }

        if (preg_match('/\b(merci|thanks|thank)\b/', $normalized)) {
            return "De rien ! Je suis heureux de pouvoir vous aider.";
        }

        if (preg_match('/\b(au revoir|bye|goodbye|à plus)\b/', $normalized)) {
            return "Au revoir ! Passez une excellente journée !";
        }

        // Sinon, appel à Hugging Face
        try {
            $response = $this->client->request('POST', 'https://router.huggingface.co/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->hfToken,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => 'Qwen/Qwen2.5-7B-Instruct:together',
                    'messages' => [
                        ['role' => 'user', 'content' => $message],
                    ],
                ],
            ]);

            $data = $response->toArray();

            if (isset($data['choices'][0]['message']['content'])) {
                return $data['choices'][0]['message']['content'];
            }

            return "Je comprends votre message, mais je n'ai pas pu obtenir de réponse du modèle.";
        } catch (\Throwable $e) {
            return "Une erreur est survenue lors de la communication avec Hugging Face : " . $e->getMessage();
        }
    }
}
