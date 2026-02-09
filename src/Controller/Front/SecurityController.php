<?php

namespace App\Controller\Front;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/front/login', name: 'front_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('front/security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/front/register', name: 'front_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, \App\Service\CaptchaService $captchaService): Response
    {
        if ($request->isMethod('POST')) {
            try {
                $user = new User();
                $email = $request->request->get('email');
                
                // Verify captcha
                $captchaCode = $request->request->get('captcha');
                if (!$captchaService->verifyCode($captchaCode, 'registration_captcha')) {
                    $this->addFlash('error', 'Invalid security code. Please try again.');
                    return $this->render('front/security/register.html.twig');
                }
                
                // Check if user already exists
                if ($entityManager->getRepository(User::class)->findOneBy(['email' => $email])) {
                    $this->addFlash('error', 'Email already exists.');
                    return $this->render('front/security/register.html.twig');
                }

                $user->setEmail($email);
                $user->setRole($request->request->get('role', 'Student')); // Default to Student if empty
                $user->setFirstName($request->request->get('firstName'));
                $user->setLastName($request->request->get('lastName'));
                
                // Hash the password
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $request->request->get('password')
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('front_login', ['last_username' => $email]);
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Registration failed: ' . $e->getMessage());
                return $this->render('front/security/register.html.twig');
            }
        }

        return $this->render('front/security/register.html.twig');
    }

    #[Route('/front/logout', name: 'front_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
