<?php

namespace App\Controller\Front;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class TwoFactorController extends AbstractController
{
    #[Route('/front/security/2fa/enable', name: 'front_2fa_enable')]
    public function enable(
        GoogleAuthenticatorInterface $googleAuthenticator, 
        EntityManagerInterface $entityManager
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Generate secret if not already set
        if (!$user->getGoogleAuthenticatorSecret()) {
            $secret = $googleAuthenticator->generateSecret();
            $user->setGoogleAuthenticatorSecret($secret);
            $entityManager->flush();
        }

        // Generate QR code
        $qrCodeContent = $googleAuthenticator->getQRContent($user);
        
        $qrCode = new QrCode(
            data: $qrCodeContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(0, 0, 0),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);
        $qrCodeDataUri = $result->getDataUri();

        return $this->render('front/security/2fa_enable.html.twig', [
            'qrCodeDataUri' => $qrCodeDataUri,
            'secret' => $user->getGoogleAuthenticatorSecret(),
        ]);
    }

    #[Route('/front/security/2fa/disable', name: 'front_2fa_disable', methods: ['POST', 'GET'])]
    public function disable(EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        // Remove 2FA secret
        $user->setGoogleAuthenticatorSecret(null);
        $entityManager->flush();

        $this->addFlash('success', 'Two-Factor Authentication has been disabled.');

        return $this->redirectToRoute('app_front_profile');
    }
}
