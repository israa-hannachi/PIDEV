<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MediaPipeDemoController extends AbstractController
{
    public function faceLandmarker(): Response
    {
        return $this->render('mediapipe/face_landmarker_demo.html.twig');
    }
}
