<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class AIService
{
    // Provider configurations
    private const PROVIDERS = [
        'openai' => [
            'api_url' => 'https://api.openai.com/v1',
            'chat_endpoint' => '/chat/completions',
            'image_endpoint' => '/images/generations',
        ],
        'clave' => [
            'api_url' => 'http://localhost:3000',
            'chat_endpoint' => '/api/chat',
            'image_endpoint' => '/api/image',
        ],
    ];

    private string $provider;
    private string $apiKey;
    private ?string $claveSessionId;

    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
        private PythonBridgeService $pythonBridge,
        string $aiProvider = 'clave',
        string $aiApiKey = '',
        ?string $claveSessionId = null
    ) {
        $this->provider = $aiProvider;
        $this->apiKey = $aiApiKey;
        $this->claveSessionId = $claveSessionId;
    }

    /**
     * Improve event metadata natively in PHP (replaces Python script)
     */
    public function improveEventMetadata(string $description): array
    {
        $descriptionLower = mb_strtolower($description);

        $knowledgeBase = [
            'Musique' => ['concert', 'festival', 'musique', 'live', 'orchestre', 'chanson', 'pianiste', 'guitare', 'rock', 'jazz', 'music', 'band', 'gig', 'singer'],
            'Sport' => ['tournoi', 'match', 'championnat', 'football', 'marathon', 'course', 'yoga', 'fitness', 'entraînement', 'tournament', 'championship', 'workout', 'training', 'race'],
            'Technologie' => ['coding', 'hackathon', 'ia', 'web', 'dev', 'developer', 'développeur', 'programmation', 'logiciel', 'software', 'intelligence artificielle', 'tech', 'conférence', 'atelier', 'programming', 'artificial intelligence', 'conference', 'it', 'digital'],
            'Formation' => ['cours', 'formation', 'webinaire', 'masterclass', 'apprentissage', 'workshop', 'séminaire', 'course', 'training', 'webinar', 'learning', 'seminar', 'enseigner', 'étudier'],
            'Networking' => ['networking', 'rencontre', 'professionnel', 'conversation', 'connection', 'peers', 'meetup', 'social', 'gathering', 'cocktail', 'peer', 'talk', 'discuss'],
            'Art' => ['exposition', 'peinture', 'sculpture', 'galerie', 'vernissage', 'théâtre', 'cinéma', 'exhibition', 'painting', 'gallery', 'theatre', 'movie', 'cinema', 'art'],
            'Gaming' => ['esport', 'tournoi', 'jeu', 'console', 'multijoueur', 'streaming', 'gaming', 'e-sport', 'tournament', 'game', 'multiplayer', 'stream']
        ];

        // 1. Difficulty Detection
        $suggestedDifficulty = "Tous niveaux";
        if (str_contains($descriptionLower, 'expert') || str_contains($descriptionLower, 'avancé') || str_contains($descriptionLower, 'senior')) $suggestedDifficulty = "Avancé";
        elseif (str_contains($descriptionLower, 'intermédiaire') || str_contains($descriptionLower, 'moyen')) $suggestedDifficulty = "Intermédiaire";
        elseif (str_contains($descriptionLower, 'débutant') || str_contains($descriptionLower, 'initiation') || str_contains($descriptionLower, 'junior')) $suggestedDifficulty = "Débutant";

        // 2. Category Matching
        $suggestedCategory = 'Autre';
        $maxScore = 0;
        foreach ($knowledgeBase as $cat => $keywords) {
            $score = 0;
            foreach ($keywords as $kw) {
                if (str_contains($descriptionLower, $kw)) $score++;
            }
            if ($score > $maxScore) {
                $maxScore = $score;
                $suggestedCategory = $cat;
            }
        }

        // 3. Time Estimation
        $suggestedTime = "18:00";
        $morning = ['matin', 'petit-déjeuner', 'early', 'morning', 'breakfast', 'prie', '08h', '09h', '10h'];
        $noon = ['midi', 'déjeuner', 'lunch', 'noon', 'afternoon', 'lunch time', '12h', '13h', '14h', '12:00', '13:00'];
        $evening = ['soir', 'nuit', 'night', 'fête', 'soirée', 'evening', 'cocktail', 'dark', 'dinner', 'apéro', '18h', '19h', '20h'];

        foreach ($morning as $w) { if (str_contains($descriptionLower, $w)) { $suggestedTime = "09:00"; break; } }
        if ($suggestedTime === "18:00") {
            foreach ($noon as $w) { if (str_contains($descriptionLower, $w)) { $suggestedTime = "12:30"; break; } }
        }
        if ($suggestedTime === "18:00") {
            foreach ($evening as $w) { if (str_contains($descriptionLower, $w)) { $suggestedTime = "19:00"; break; } }
        }

        // 4. Price & Capacity Suggestions
        $suggestedPrix = "20.00";
        $suggestedCapacite = 50;
        
        if (str_contains($descriptionLower, 'gratuit') || str_contains($descriptionLower, 'free') || str_contains($descriptionLower, 'hackathon')) $suggestedPrix = "0.00";
        if (str_contains($descriptionLower, 'vip') || str_contains($descriptionLower, 'luxe') || str_contains($descriptionLower, 'gala')) $suggestedPrix = "150.00";
        
        if (str_contains($descriptionLower, 'petit') || str_contains($descriptionLower, 'intimiste')) $suggestedCapacite = 15;
        if (str_contains($descriptionLower, 'grand') || str_contains($descriptionLower, 'stade') || str_contains($descriptionLower, 'festival')) $suggestedCapacite = 500;

        // 5. Location Suggestions
        $suggestedLieu = "Tunis, Tunisie";
        if (str_contains($descriptionLower, 'plage') || str_contains($descriptionLower, 'mer')) $suggestedLieu = "Hammamet, Tunisie";
        if (str_contains($descriptionLower, 'culture') || str_contains($descriptionLower, 'histoire') || str_contains($descriptionLower, 'monument')) $suggestedLieu = "Carthage, Tunisie";
        if (str_contains($descriptionLower, 'dev') || str_contains($descriptionLower, 'tech') || str_contains($descriptionLower, 'hackathon') || str_contains($descriptionLower, 'code')) $suggestedLieu = "Technopole El Ghazala, Tunisie";
        
        // Final title logic if still empty
        if ($suggestedTitre === "" && $suggestedCategory !== 'Autre') {
            $suggestedTitre = $suggestedCategory . " à " . explode(',', $suggestedLieu)[0];
        }

        // 6. Tags & Hooks
        $tags = ['IA', 'Communauté', 'Naja7ni'];
        if ($suggestedCategory !== 'Autre') $tags[] = $suggestedCategory;
        
        $hooks = [
            'Musique' => "Vivez une expérience sonore unique avec nos artistes talentueux.",
            'Sport' => "Dépassez vos limites lors de cet événement sportif de haut niveau.",
            'Technologie' => "Découvrez les dernières innovations qui façonnent notre futur.",
            'Formation' => "Boostez vos compétences grâce à nos formateurs experts.",
            'Networking' => "Élargissez votre cercle professionnel et créez des opportunités.",
            'Art' => "Laissez-vous inspirer par la créativité et la beauté de l'art."
        ];
        $marketingHook = $hooks[$suggestedCategory] ?? "Ne manquez pas cet événement exceptionnel !";
        
        // Calculate end time (default +2 hours or longer for specific events)
        $duration = 2;
        if ($suggestedCategory === 'Technologie' && str_contains($descriptionLower, 'hackathon')) $duration = 48;
        if ($suggestedCategory === 'Formation') $duration = 4;
        
        $endTime = date('H:i', strtotime($suggestedTime . " +$duration hours"));

        return [
            'titre' => $suggestedTitre ?: ($suggestedCategory . ' Event'),
            'category' => $suggestedCategory,
            'difficulty' => $suggestedDifficulty,
            'suggested_time' => $suggestedTime,
            'suggested_endtime' => $endTime,
            'suggested_prix' => $suggestedPrix,
            'suggested_capacite' => $suggestedCapacite,
            'suggested_lieu' => $suggestedLieu,
            'tags' => $tags,
            'marketing_hook' => $marketingHook
        ];
    }

    /**
     * Generate chat response for event discussions
     * 
     * @param string $message User message
     * @param string $eventContext Event information for context
     * @param array $conversationHistory Previous messages
     * 
     * @return array Result with keys: success, text, error
     */
    public function generateChatResponse(
        string $message,
        string $eventContext = '',
        array $conversationHistory = []
    ): array {
        try {
            if ($this->provider === 'clave') {
                return $this->callClaveChat($message, $conversationHistory);
            } elseif ($this->provider === 'openai') {
                return $this->callOpenAIChat($message, $eventContext, $conversationHistory);
            } else {
                return [
                    'success' => false,
                    'text' => null,
                    'error' => 'Unknown AI provider: ' . $this->provider,
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('AI Chat Generation Error', [
                'provider' => $this->provider,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'text' => null,
                'error' => 'Failed to generate response: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate event poster image
     * 
     * @param string $eventTitle Event title
     * @param string $eventDescription Event description
     * @param string $style Art style for image
     * 
     * @return array Result with keys: success, image_url, error
     */
    public function generateEventPoster(
        string $eventTitle,
        string $eventDescription,
        string $style = 'modern professional'
    ): array {
        try {
            if ($this->provider === 'clave') {
                return $this->callClaveImage($eventTitle, $eventDescription, $style);
            } elseif ($this->provider === 'openai') {
                return $this->callOpenAIImage($eventTitle, $eventDescription, $style);
            } else {
                return [
                    'success' => false,
                    'image_url' => null,
                    'error' => 'Unknown AI provider',
                ];
            }
        } catch (\Exception $e) {
            $this->logger->error('AI Image Generation Error', [
                'provider' => $this->provider,
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'image_url' => null,
                'error' => 'Failed to generate image: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Generate course tutoring response
     * 
     * @param string $question Student question
     * @param string $courseContext Course name/topic
     * @param string $level Education level
     * 
     * @return array Result with keys: success, text, explanation, examples
     */
    public function generateTutoringResponse(
        string $question,
        string $courseContext = '',
        string $level = 'intermediate'
    ): array {
        try {
            $systemPrompt = "You are an expert educational tutor helping students learn. ";
            $systemPrompt .= "Provide clear explanations with examples. ";
            $systemPrompt .= "Education level: {$level}. ";
            if ($courseContext) {
                $systemPrompt .= "Course context: {$courseContext}. ";
            }

            if ($this->provider === 'clave') {
                return $this->callClaveChat(
                    "Question: {$question}\n\nPlease provide:\n1. Clear explanation\n2. 2-3 practical examples\n3. Key takeaways",
                    []
                );
            } elseif ($this->provider === 'openai') {
                return $this->callOpenAIChat(
                    $question,
                    $courseContext,
                    [],
                    $systemPrompt
                );
            }

            return [
                'success' => false,
                'text' => null,
                'error' => 'Provider not configured for tutoring',
            ];
        } catch (\Exception $e) {
            $this->logger->error('AI Tutoring Generation Error', [
                'exception' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'text' => null,
                'error' => 'Tutoring service unavailable',
            ];
        }
    }

    /**
     * Call Clave API for chat
     */
    private function callClaveChat(string $message, array $conversationHistory): array
    {
        $payload = [
            'message' => $message,
            'model' => 'gpt-3.5',
        ];

        if ($this->claveSessionId) {
            $payload['x-session-id'] = $this->claveSessionId;
        }

        if (!empty($conversationHistory)) {
            $payload['conversation_history'] = $conversationHistory;
        }

        $response = $this->httpClient->request('POST', 
            self::PROVIDERS['clave']['api_url'] . self::PROVIDERS['clave']['chat_endpoint'],
            [
                'json' => $payload,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'timeout' => 30,
            ]
        );

        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200) {
            return [
                'success' => false,
                'text' => null,
                'error' => "Clave API error: HTTP {$statusCode}",
            ];
        }

        $data = $response->toArray();
        
        return [
            'success' => true,
            'text' => $data['text'] ?? $data['response'] ?? 'No response',
            'error' => null,
        ];
    }

    /**
     * Call OpenAI API for chat
     */
    private function callOpenAIChat(
        string $message,
        string $context = '',
        array $conversationHistory = [],
        string $systemPrompt = ''
    ): array {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'text' => null,
                'error' => 'OpenAI API key not configured',
            ];
        }

        $messages = [];

        // Add system message
        $messages[] = [
            'role' => 'system',
            'content' => $systemPrompt ?: 'You are a helpful educational assistant.',
        ];

        // Add context if provided
        if ($context) {
            $messages[] = [
                'role' => 'system',
                'content' => "Additional context: {$context}",
            ];
        }

        // Add conversation history
        foreach ($conversationHistory as $msg) {
            $messages[] = [
                'role' => $msg['role'] ?? 'user',
                'content' => $msg['content'] ?? $msg['message'] ?? '',
            ];
        }

        // Add current message
        $messages[] = [
            'role' => 'user',
            'content' => $message,
        ];

        $payload = [
            'model' => 'gpt-3.5-turbo',
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ];

        try {
            $response = $this->httpClient->request('POST',
                self::PROVIDERS['openai']['api_url'] . self::PROVIDERS['openai']['chat_endpoint'],
                [
                    'json' => $payload,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 30,
                ]
            );

            $data = $response->toArray();
            
            if (!isset($data['choices'][0]['message']['content'])) {
                return [
                    'success' => false,
                    'text' => null,
                    'error' => 'Invalid OpenAI response format',
                ];
            }

            return [
                'success' => true,
                'text' => $data['choices'][0]['message']['content'],
                'error' => null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'text' => null,
                'error' => 'OpenAI API error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Call Clave API for image generation
     */
    private function callClaveImage(string $title, string $description, string $style): array
    {
        $prompt = "Create a professional event poster for: {$title}.\n";
        $prompt .= "Event description: {$description}\n";
        $prompt .= "Style: {$style}";

        $payload = [
            'prompt' => $prompt,
            'style' => $style,
        ];

        if ($this->claveSessionId) {
            $payload['x-session-id'] = $this->claveSessionId;
        }

        try {
            $response = $this->httpClient->request('POST',
                self::PROVIDERS['clave']['api_url'] . self::PROVIDERS['clave']['image_endpoint'],
                [
                    'json' => $payload,
                    'headers' => [
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 60,
                ]
            );

            $data = $response->toArray();

            if (isset($data['url'])) {
                return [
                    'success' => true,
                    'image_url' => $data['url'],
                    'error' => null,
                ];
            } elseif (isset($data['image_url'])) {
                return [
                    'success' => true,
                    'image_url' => $data['image_url'],
                    'error' => null,
                ];
            } else {
                return [
                    'success' => false,
                    'image_url' => null,
                    'error' => 'No image URL in response',
                ];
            }
        } catch (\Exception $e) {
            return [
                'success' => false,
                'image_url' => null,
                'error' => 'Image generation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Call OpenAI API for image generation
     */
    private function callOpenAIImage(string $title, string $description, string $style): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'image_url' => null,
                'error' => 'OpenAI API key not configured',
            ];
        }

        $prompt = "Create a professional, attractive event poster for '{$title}'. ";
        $prompt .= "Description: {$description}. ";
        $prompt .= "Style: {$style}. ";
        $prompt .= "The image should be suitable for sharing on social media and event platforms. ";
        $prompt .= "Include space for event details and maintain professional appearance.";

        $payload = [
            'model' => 'dall-e-3',
            'prompt' => $prompt,
            'n' => 1,
            'size' => '1024x1024',
            'quality' => 'standard',
        ];

        try {
            $response = $this->httpClient->request('POST',
                self::PROVIDERS['openai']['api_url'] . self::PROVIDERS['openai']['image_endpoint'],
                [
                    'json' => $payload,
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                    ],
                    'timeout' => 60,
                ]
            );

            $data = $response->toArray();

            if (isset($data['data'][0]['url'])) {
                return [
                    'success' => true,
                    'image_url' => $data['data'][0]['url'],
                    'error' => null,
                ];
            }

            return [
                'success' => false,
                'image_url' => null,
                'error' => 'Invalid OpenAI image response',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'image_url' => null,
                'error' => 'OpenAI image generation failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get available providers
     */
    public static function getAvailableProviders(): array
    {
        return array_keys(self::PROVIDERS);
    }

    /**
     * Get provider configuration
     */
    public static function getProviderConfig(string $provider): ?array
    {
        return self::PROVIDERS[$provider] ?? null;
    }

    /**
     * Test connection to AI provider
     */
    public function testConnection(): bool
    {
        try {
            $result = $this->generateChatResponse('Hello, can you respond with "Connection successful"?');
            return $result['success'];
        } catch (\Exception $e) {
            $this->logger->error('AI Service connection test failed: ' . $e->getMessage());
            return false;
        }
    }
}
