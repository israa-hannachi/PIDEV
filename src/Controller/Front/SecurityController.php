<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\ForgotPasswordType;
use App\Form\ResetPasswordType;
use App\Service\PasswordResetService;
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

    #[Route('/front/forgot-password', name: 'front_forgot_password', methods: ['GET', 'POST'])]
    public function forgotPassword(Request $request, PasswordResetService $passwordResetService): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            
            if ($passwordResetService->generateAndSendResetCode($email)) {
                $this->addFlash('success', 'A password reset code has been sent to your email address.');
                return $this->redirectToRoute('front_reset_password', ['email' => $email]);
            } else {
                $this->addFlash('error', 'No account found with this email address.');
            }
        }

        return $this->render('front/security/forgot_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/front/reset-password', name: 'front_reset_password', methods: ['GET', 'POST'])]
    public function resetPassword(Request $request, PasswordResetService $passwordResetService): Response
    {
        $email = $request->query->get('email') ?? $request->request->get('email');
        
        if (!$email) {
            $this->addFlash('error', 'Email address is required.');
            return $this->redirectToRoute('front_forgot_password');
        }

        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $code = $form->get('code')->getData();
            $newPassword = $form->get('password')->getData();
            
            if ($passwordResetService->resetPassword($email, $code, $newPassword)) {
                $this->addFlash('success', 'Your password has been reset successfully. Please login with your new password.');
                return $this->redirectToRoute('front_login');
            } else {
                $this->addFlash('error', 'Invalid or expired reset code. Please request a new one.');
            }
        }

        return $this->render('front/security/reset_password.html.twig', [
            'form' => $form->createView(),
            'email' => $email
        ]);
    }
}
