<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaPipeDemoController extends AbstractController
{
    #[Route('/mediapipe/face-landmarker', name: 'app_mediapipe_face_landmarker')]
    public function faceLandmarker(): Response
    {
        return $this->render('mediapipe/face_landmarker_demo.html.twig');
    }
}
