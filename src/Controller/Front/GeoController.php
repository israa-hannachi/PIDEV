<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GeoController extends AbstractController
{
    #[Route('/api/geolocate', name: 'app_api_geolocate', methods: ['GET'])]
    public function geolocate(Request $request): JsonResponse
    {
        $apiKey = $_ENV['IPGEO_API_KEY'] ?? getenv('IPGEO_API_KEY');

        if (!$apiKey) {
            return new JsonResponse(['error' => 'IPGeolocation API key not configured'], 500);
        }

        // Allow passing an explicit IP via query string ?ip=1.2.3.4
        $ip = $request->query->get('ip');
        $url = 'https://api.ipgeolocation.io/v2/ipgeo?apiKey=' . urlencode($apiKey);
        if ($ip) {
            $url .= '&ip=' . urlencode($ip);
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($url, false, $context);

        if ($response === false) {
            return new JsonResponse(['error' => 'Failed to fetch geolocation data'], 502);
        }

        $data = json_decode($response, true);
        if ($data === null) {
            return new JsonResponse(['error' => 'Invalid response from geolocation service'], 502);
        }

        // Return only relevant fields to keep payload small
        $result = [
            'ip' => $data['ip'] ?? null,
            'city' => $data['city'] ?? null,
            'state_prov' => $data['state_prov'] ?? null,
            'country' => $data['country_name'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'continent' => $data['continent_name'] ?? null,
        ];

        return new JsonResponse($result);
    }
}
