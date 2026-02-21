<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\EventPoster;
use App\Repository\EventPosterRepository;
use App\Service\AIService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/event-poster', name: 'event_poster_')]
class EventPosterController extends AbstractController
{
    public function __construct(
        private EventPosterRepository $posterRepository,
        private AIService $aiService,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Generate AI poster for event
     */
    #[Route('/{id}/generate', name: 'generate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function generatePoster(Event $event, Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $style = $data['style'] ?? 'modern professional';
            $prompt = $data['prompt'] ?? null;

            // Check if user is event organizer
            if ($event->getOrganizerEmail() !== $this->getUser()->getEmail()) {
                return $this->json([
                    'success' => false,
                    'error' => 'Only event organizer can generate posters',
                ], Response::HTTP_FORBIDDEN);
            }

            // Use custom prompt or generate from event details
            if (!$prompt) {
                $prompt = $event->getDescription();
                if (strlen($prompt) > 500) {
                    $prompt = substr($prompt, 0, 500) . '...';
                }
            }

            // Generate image
            $imageResult = $this->aiService->generateEventPoster(
                $event->getTitre(),
                $prompt,
                $style
            );

            if (!$imageResult['success']) {
                return $this->json([
                    'success' => false,
                    'error' => $imageResult['error'],
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            // Deactivate previous posters
            $existingPosters = $this->posterRepository->findByEvent($event);
            foreach ($existingPosters as $poster) {
                $poster->setIsActive(false);
            }

            // Save new poster
            $eventPoster = new EventPoster();
            $eventPoster->setEvent($event)
                ->setImageUrl($imageResult['image_url'])
                ->setPrompt($prompt)
                ->setStyle($style)
                ->setGeneratedBy($this->getUser()->getEmail())
                ->setMetadata([
                    'generated_by_user' => $this->getUser()->getFirstName() . ' ' . $this->getUser()->getLastName(),
                    'generated_at_iso' => (new \DateTime())->format('c'),
                    'model' => 'dall-e-3',
                ]);

            $this->em->persist($eventPoster);
            $event->setPoster($eventPoster);
            $this->em->flush();

            return $this->json([
                'success' => true,
                'poster' => [
                    'id' => $eventPoster->getId(),
                    'imageUrl' => $eventPoster->getImageUrl(),
                    'style' => $eventPoster->getStyle(),
                    'generatedAt' => $eventPoster->getGeneratedAt()->format('c'),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => 'Error generating poster: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get active poster for event
     */
    #[Route('/{id}/active', name: 'active', methods: ['GET'])]
    public function getActivePoster(Event $event): JsonResponse
    {
        $poster = $this->posterRepository->findActiveByEvent($event);

        if (!$poster) {
            return $this->json([
                'success' => false,
                'error' => 'No active poster found',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'poster' => [
                'id' => $poster->getId(),
                'imageUrl' => $poster->getImageUrl(),
                'style' => $poster->getStyle(),
                'generatedAt' => $poster->getGeneratedAt()->format('c'),
                'generatedBy' => $poster->getGeneratedBy(),
            ],
        ]);
    }

    /**
     * Get all posters for event
     */
    #[Route('/{id}/versions', name: 'versions', methods: ['GET'])]
    public function getPosterVersions(Event $event): JsonResponse
    {
        $posters = $this->posterRepository->findByEvent($event);
        
        $data = array_map(function (EventPoster $poster) {
            return [
                'id' => $poster->getId(),
                'imageUrl' => $poster->getImageUrl(),
                'style' => $poster->getStyle(),
                'isActive' => $poster->isActive(),
                'generatedAt' => $poster->getGeneratedAt()->format('c'),
                'downloadCount' => $poster->getDownloadCount(),
            ];
        }, $posters);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'posters' => $data,
        ]);
    }

    /**
     * Activate a specific poster
     */
    #[Route('/poster/{id}/activate', name: 'activate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function activatePoster(EventPoster $poster): JsonResponse
    {
        // Check ownership
        if ($poster->getEvent()->getOrganizerEmail() !== $this->getUser()->getEmail()) {
            return $this->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        // Deactivate others
        foreach ($this->posterRepository->findByEvent($poster->getEvent()) as $p) {
            $p->setIsActive(false);
        }

        $poster->setIsActive(true);
        $poster->getEvent()->setPoster($poster);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Poster activated',
        ]);
    }

    /**
     * Download poster image
     */
    #[Route('/poster/{id}/download', name: 'download', methods: ['GET'])]
    public function downloadPoster(EventPoster $poster, Request $request): Response
    {
        // Increment download count
        $poster->incrementDownloadCount();
        $this->em->flush();

        // Redirect to image URL or fetch and return
        $imageUrl = $poster->getImageUrl();
        
        try {
            // Try to fetch the image
            $imageContent = file_get_contents($imageUrl);
            
            return new Response(
                $imageContent,
                Response::HTTP_OK,
                [
                    'Content-Type' => 'image/png',
                    'Content-Disposition' => 'attachment; filename="' . $poster->getEvent()->getTitre() . '.png"',
                    'Content-Length' => strlen($imageContent),
                ]
            );
        } catch (\Exception $e) {
            // Fallback: redirect to image URL
            return $this->redirect($imageUrl);
        }
    }

    /**
     * Delete poster version
     */
    #[Route('/poster/{id}/delete', name: 'delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deletePoster(EventPoster $poster): JsonResponse
    {
        $event = $poster->getEvent();
        
        // Check ownership
        if ($event->getOrganizerEmail() !== $this->getUser()->getEmail()) {
            return $this->json([
                'success' => false,
                'error' => 'Unauthorized',
            ], Response::HTTP_FORBIDDEN);
        }

        // Don't delete if it's the active poster
        if ($poster->isActive()) {
            return $this->json([
                'success' => false,
                'error' => 'Cannot delete active poster',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($poster);
        $this->em->flush();

        return $this->json([
            'success' => true,
            'message' => 'Poster deleted',
        ]);
    }

    /**
     * Get poster generation styles
     */
    #[Route('/styles', name: 'styles', methods: ['GET'])]
    public function getStyles(): JsonResponse
    {
        $styles = [
            'modern professional' => 'Modern & Professional',
            'minimalist' => 'Minimalist',
            'vibrant colorful' => 'Vibrant & Colorful',
            'elegant sophisticated' => 'Elegant & Sophisticated',
            'playful creative' => 'Playful & Creative',
            'dark mysterious' => 'Dark & Mysterious',
            'bright cheerful' => 'Bright & Cheerful',
            'retro vintage' => 'Retro & Vintage',
            'futuristic' => 'Futuristic',
            'artistic hand-drawn' => 'Artistic Hand-drawn',
        ];

        return $this->json([
            'success' => true,
            'styles' => $styles,
        ]);
    }

    /**
     * Get poster generation statistics
     */
    #[Route('/{id}/stats', name: 'stats', methods: ['GET'])]
    public function getStats(Event $event): JsonResponse
    {
        $posterCount = $this->posterRepository->countByEvent($event);
        $activePoster = $this->posterRepository->findActiveByEvent($event);
        $allPosters = $this->posterRepository->findByEvent($event);
        
        $totalDownloads = array_sum(array_map(fn($p) => $p->getDownloadCount(), $allPosters));

        return $this->json([
            'success' => true,
            'stats' => [
                'totalPosters' => $posterCount,
                'activePoster' => $activePoster ? [
                    'id' => $activePoster->getId(),
                    'style' => $activePoster->getStyle(),
                    'generatedAt' => $activePoster->getGeneratedAt()->format('c'),
                ] : null,
                'totalDownloads' => $totalDownloads,
            ],
        ]);
    }
}
