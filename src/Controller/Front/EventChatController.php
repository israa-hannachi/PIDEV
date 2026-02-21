<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\EventChat;
use App\Repository\EventChatRepository;
use App\Service\AIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/event-chat', name: 'event_chat_')]
class EventChatController extends AbstractController
{
    public function __construct(
        private EventChatRepository $chatRepository,
        private AIService $aiService,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Get event chat history
     */
    #[Route('/{id}/messages', name: 'messages', methods: ['GET'])]
    public function getMessages(Event $event, Request $request): JsonResponse
    {
        $limit = $request->query->getInt('limit', 50);
        $messages = $this->chatRepository->findByEvent($event, $limit);

        $data = array_map(function (EventChat $msg) {
            return [
                'id' => $msg->getId(),
                'sender' => $msg->getSender(),
                'senderName' => $msg->getSenderDisplayName(),
                'message' => $msg->getMessage(),
                'createdAt' => $msg->getCreatedAt()->format('c'),
                'isAI' => $msg->isFromAI(),
                'likes' => $msg->getLikes(),
                'visibility' => $msg->getVisibility(),
            ];
        }, $messages);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => array_reverse($data), // Return in chronological order
        ]);
    }

    /**
     * Send message to event chat (with optional AI response)
     */
    #[Route('/{id}/send', name: 'send', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function sendMessage(Event $event, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $message = trim($data['message'] ?? '');
            $includeAI = $data['includeAI'] ?? true;

            if (empty($message)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Message cannot be empty',
                ], Response::HTTP_BAD_REQUEST);
            }

            if (strlen($message) > 2000) {
                return $this->json([
                    'success' => false,
                    'error' => 'Message is too long (max 2000 characters)',
                ], Response::HTTP_BAD_REQUEST);
            }

            // Save user message
            $userMessage = new EventChat();
            $userMessage->setEvent($event)
                ->setUser($this->getUser())
                ->setSender('user')
                ->setMessage($message)
                ->setVisibility('public');

            $this->em->persist($userMessage);
            $this->em->flush();

            $response = [
                'success' => true,
                'userMessage' => [
                    'id' => $userMessage->getId(),
                    'message' => $userMessage->getMessage(),
                    'createdAt' => $userMessage->getCreatedAt()->format('c'),
                ],
            ];

            // Generate AI response if requested
            if ($includeAI) {
                // Get conversation context
                $conversationHistory = $this->chatRepository->getConversationHistory($event, 10);
                $history = array_map(function (EventChat $msg) {
                    return [
                        'role' => $msg->isFromAI() ? 'assistant' : 'user',
                        'content' => $msg->getMessage(),
                    ];
                }, $conversationHistory);

                // Get event context for AI
                $eventContext = "Event: {$event->getTitre()}\n";
                $eventContext .= "Description: " . substr($event->getDescription(), 0, 500) . "\n";
                $eventContext .= "Date: {$event->getDateDebut()->format('Y-m-d H:i')}\n";
                $eventContext .= "Location: {$event->getLieu()}";

                // Generate AI response
                $aiResult = $this->aiService->generateChatResponse($message, $eventContext, $history);

                if ($aiResult['success']) {
                    $aiMessage = new EventChat();
                    $aiMessage->setEvent($event)
                        ->setSender('ai_assistant')
                        ->setMessage($aiResult['text'])
                        ->setVisibility('public')
                        ->setIsAiGenerated(true)
                        ->setMetadata([
                            'model' => 'gpt-3.5-turbo',
                            'generated_at' => (new \DateTime())->format('c'),
                        ]);

                    $this->em->persist($aiMessage);
                    $this->em->flush();

                    $response['aiMessage'] = [
                        'id' => $aiMessage->getId(),
                        'message' => $aiMessage->getMessage(),
                        'createdAt' => $aiMessage->getCreatedAt()->format('c'),
                    ];
                } else {
                    $response['aiError'] = $aiResult['error'];
                }
            }

            return $this->json($response);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error processing message: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get AI-only responses for tutoring
     */
    #[Route('/{id}/tutoring', name: 'tutoring', methods: ['POST'])]
    public function getTutoringResponse(Event $event, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $question = trim($data['question'] ?? '');
            $level = $data['level'] ?? 'intermediate';

            if (empty($question)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Question cannot be empty',
                ], Response::HTTP_BAD_REQUEST);
            }

            $result = $this->aiService->generateTutoringResponse($question, $event->getTitre(), $level);

            if (!$result['success']) {
                return $this->json([
                    'success' => false,
                    'error' => $result['error'],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Optionally save to event chat
            if ($data['saveToChat'] ?? false) {
                $userMsg = new EventChat();
                $userMsg->setEvent($event)
                    ->setUser($this->getUser())
                    ->setSender('user')
                    ->setMessage('[Tutoring] ' . $question)
                    ->setVisibility('public');

                $aiMsg = new EventChat();
                $aiMsg->setEvent($event)
                    ->setSender('ai_assistant')
                    ->setMessage($result['text'])
                    ->setVisibility('public')
                    ->setIsAiGenerated(true)
                    ->setMetadata(['type' => 'tutoring']);

                $this->em->persist($userMsg);
                $this->em->persist($aiMsg);
                $this->em->flush();
            }

            return $this->json([
                'success' => true,
                'response' => $result['text'],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Tutoring service error',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Like a message
     */
    #[Route('/message/{id}/like', name: 'like_message', methods: ['POST'])]
    public function likeMessage(EventChat $message): JsonResponse
    {
        $message->addLike();
        $this->em->flush();

        return $this->json([
            'success' => true,
            'likes' => $message->getLikes(),
        ]);
    }

    /**
     * Get event chat statistics
     */
    #[Route('/{id}/stats', name: 'stats', methods: ['GET'])]
    public function getStats(Event $event): JsonResponse
    {
        $totalMessages = $this->chatRepository->countByEvent($event);
        $aiMessages = $this->chatRepository->findRecentAIResponses($event, 100);
        
        $aiCount = count($aiMessages);
        $userCount = $totalMessages - $aiCount;

        return $this->json([
            'success' => true,
            'stats' => [
                'totalMessages' => $totalMessages,
                'userMessages' => $userCount,
                'aiMessages' => $aiCount,
                'userPercentage' => $totalMessages > 0 ? round($userCount / $totalMessages * 100, 2) : 0,
                'aiPercentage' => $totalMessages > 0 ? round($aiCount / $totalMessages * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Clear event chat history (admin only)
     */
    #[Route('/{id}/clear', name: 'clear', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function clearChat(Event $event): JsonResponse
    {
        $messages = $this->chatRepository->findByEvent($event, 1000);
        
        foreach ($messages as $message) {
            $this->em->remove($message);
        }
        
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Event chat cleared',
        ]);
    }
}
