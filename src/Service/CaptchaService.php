<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CaptchaService
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function generateCode(string $key = 'captcha'): string
    {
        $code = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6);
        $session = $this->requestStack->getSession();
        $session->set($key, $code);
        return $code;
    }

    public function verifyCode(string $userInput, string $key = 'captcha'): bool
    {
        $session = $this->requestStack->getSession();
        $storedCode = $session->get($key);
        $session->remove($key); // Remove after verification
        
        return $storedCode && strtoupper($userInput) === $storedCode;
    }

    public function generateImage(string $code): string
    {
        $width = 150;
        $height = 50;
        
        $image = imagecreatetruecolor($width, $height);
        $bgColor = imagecolorallocate($image, 255, 255, 255);
        $textColor = imagecolorallocate($image, 0, 0, 0);
        $lineColor = imagecolorallocate($image, 200, 200, 200);
        
        imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
        
        // Add noise lines
        for ($i = 0; $i < 5; $i++) {
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        
        // Add text
        imagestring($image, 5, 40, 15, $code, $textColor);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return $imageData;
    }
}
