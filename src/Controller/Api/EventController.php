<?php

namespace App\Controller\Api;

use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/api/nearby-events', name: 'app_api_nearby_events', methods: ['GET'])]
    public function nearbyEvents(Request $request, EventRepository $eventRepository): JsonResponse
    {
        $latitude = $request->query->get('latitude');
        $longitude = $request->query->get('longitude');
        $radius = $request->query->get('radius', 50); // in kilometers

        if (!$latitude || !$longitude) {
            return $this->json([
                'error' => 'Missing latitude or longitude parameters'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        try {
            $latitude = (float) $latitude;
            $longitude = (float) $longitude;
            $radius = (float) $radius;
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Invalid latitude, longitude or radius format'
            ], JsonResponse::HTTP_BAD_REQUEST);
        }

        // Get all events with coordinates
        $allEvents = $eventRepository->findAll();
        $nearbyEvents = [];

        foreach ($allEvents as $event) {
            if ($event->getLatitude() && $event->getLongitude()) {
                $eventLat = (float) $event->getLatitude();
                $eventLon = (float) $event->getLongitude();

                // Haversine formula to calculate distance
                $distance = $this->haversineDistance(
                    $latitude,
                    $longitude,
                    $eventLat,
                    $eventLon
                );

                if ($distance <= $radius) {
                    $nearbyEvents[] = [
                        'id' => $event->getId(),
                        'titre' => $event->getTitre(),
                        'description' => $event->getDescription(),
                        'dateDebut' => $event->getDateDebut()?->format('Y-m-d H:i:s'),
                        'dateFin' => $event->getDateFin()?->format('Y-m-d H:i:s'),
                        'lieu' => $event->getLieu(),
                        'latitude' => $event->getLatitude(),
                        'longitude' => $event->getLongitude(),
                        'capacite' => $event->getCapacite(),
                        'inscrits' => $event->getInscrits(),
                        'categorie' => $event->getCategorie(),
                        'prix' => $event->getPrix(),
                        'image' => $event->getImage(),
                        'statut' => $event->getStatut(),
                        'distance_km' => round($distance, 2)
                    ];
                }
            }
        }

        // Sort by distance
        usort($nearbyEvents, function($a, $b) {
            return $a['distance_km'] <=> $b['distance_km'];
        });

        return $this->json([
            'latitude' => $latitude,
            'longitude' => $longitude,
            'radius' => $radius,
            'count' => count($nearbyEvents),
            'events' => $nearbyEvents
        ]);
    }

    /**
     * Calculate distance between two coordinates using Haversine formula
     * Returns distance in kilometers
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * asin(sqrt($a));
        $distance = $earthRadius * $c;

        return $distance;
    }
}
