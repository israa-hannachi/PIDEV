<?php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Service\EventTimingAnalyzer;
use App\Service\EventSuccessPredictor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/ai')]
class AIIntelligenceController extends AbstractController
{
    #[Route('/suggest-timing', name: 'back_ai_suggest_timing', methods: ['POST'])]
    public function suggestTiming(Request $request, EventTimingAnalyzer $timingAnalyzer): JsonResponse
    {
        $category = $request->request->get('category');
        $suggestions = $timingAnalyzer->suggestOptimalTiming($category);

        return new JsonResponse($suggestions);
    }

    #[Route('/predict-success', name: 'back_ai_predict_success', methods: ['POST'])]
    public function predictSuccess(Request $request, EventSuccessPredictor $successPredictor): JsonResponse
    {
        $data = $request->request->all();
        
        // Create a transient Event entity for prediction
        $event = new Event();
        $event->setTitre($data['titre'] ?? 'Untitled');
        $event->setCategorie($data['category'] ?? 'Autre');
        $event->setCapacite((int)($data['capacite'] ?? 50));
        $event->setPrix((float)($data['prix'] ?? 0));
        
        if (!empty($data['dateDebut'])) {
            try {
                $event->setDateDebut(new \DateTime($data['dateDebut']));
            } catch (\Exception $e) {
                $event->setDateDebut(new \DateTime());
            }
        } else {
            $event->setDateDebut(new \DateTime());
        }

        $prediction = $successPredictor->predictSuccess($event);

        return new JsonResponse($prediction);
    }
}
