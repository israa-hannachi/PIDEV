<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FaceAuthController extends AbstractController
{
    #[Route('/face-auth/login', name: 'face_auth_login')]
    public function login(): Response
    {
        // If already logged in, redirect to dashboard
        if ($this->getUser()) {
            return $this->redirectToRoute('app_front');
        }

        return $this->render('security/face_auth_login.html.twig');
    }

    #[Route('/face-auth/register', name: 'face_auth_register')]
    #[IsGranted('ROLE_USER')]
    public function register(): Response
    {
        return $this->render('security/face_auth_register.html.twig');
    }

    #[Route('/face-auth/save-descriptor', name: 'face_auth_save', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function saveDescriptor(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['descriptor']) || !is_array($data['descriptor'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid face descriptor data'
            ], 400);
        }

        /** @var User $user */
        $user = $this->getUser();
        
        // Store the face descriptor as JSON
        $user->setBiometricDescriptor(json_encode($data['descriptor']));
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Face registered successfully'
        ]);
    }

    #[Route('/face-auth/verify', name: 'face_auth_verify', methods: ['POST'])]
    public function verify(
        Request $request,
        UserRepository $userRepository,
        Security $security
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['descriptor'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Email and face descriptor are required'
            ], 400);
        }

        $user = $userRepository->findOneBy(['email' => $data['email']]);
        
        if (!$user || !$user->getBiometricDescriptor()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User not found or face not registered'
            ], 404);
        }

        $storedDescriptor = json_decode($user->getBiometricDescriptor(), true);
        $providedDescriptor = $data['descriptor'];

        // Calculate Euclidean distance between descriptors
        $distance = $this->calculateDistance($storedDescriptor, $providedDescriptor);
        
        // Threshold for face recognition (adjust as needed)
        $threshold = 0.6;

        if ($distance < $threshold) {
            // Face matched - log the user in
            $security->login($user, 'form_login', 'main');
            
            return new JsonResponse([
                'success' => true,
                'message' => 'Face verified successfully',
                'redirect' => $this->generateUrl('app_front')
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Face verification failed',
            'distance' => $distance
        ], 401);
    }

    private function calculateDistance(array $descriptor1, array $descriptor2): float
    {
        if (count($descriptor1) !== count($descriptor2)) {
            return PHP_FLOAT_MAX;
        }

        $sum = 0;
        for ($i = 0; $i < count($descriptor1); $i++) {
            $diff = $descriptor1[$i] - $descriptor2[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }
}
