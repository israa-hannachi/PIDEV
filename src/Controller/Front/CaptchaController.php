<?php

namespace App\Controller\Front;

use App\Service\CaptchaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/front')]
class CaptchaController extends AbstractController
{
    #[Route('/captcha/generate', name: 'captcha_generate')]
    public function generate(CaptchaService $captchaService): JsonResponse
    {
        $code = $captchaService->generateCode('registration_captcha');
        
        return new JsonResponse(['code' => $code]);
    }
}
