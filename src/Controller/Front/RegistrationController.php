<?php
// src/Controller/Front/RegistrationController.php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\Registration;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\NotificationService;

class RegistrationController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'app_front_event_register', methods: ['GET', 'POST'])]
    public function register(
        Event $event, 
        Request $request, 
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        \App\Service\PaymeeService $paymeeService,
        \App\Service\PdfService $pdfService
    ): Response {
        if ($event->getInscrits() >= $event->getCapacite()) {
            $this->addFlash('error', 'Cet événement est complet.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        $registration = new Registration();
        $registration->setEvenement($event);
        
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($registration);

            if ((float) $event->getPrix() > 0 && $registration->getPaymentMethod() === 'paymee') {
                // Event is paid via Paymee - set to pending and create Paymee payment
                $registration->setStatut('en_attente');
                $entityManager->flush();

                try {
                    $returnUrl = $this->generateUrl('app_front_payment_cancel', ['id' => $registration->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
                    $callbackUrl = $this->generateUrl('app_front_payment_success', ['id' => $registration->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
                    
                    // Split name for V2
                    $nameParts = explode(' ', $registration->getVisitorName(), 2);
                    $firstName = $nameParts[0] ?? 'Client';
                    $lastName = $nameParts[1] ?? 'Naja7ni';

                    $paymentData = $paymeeService->createPayment(
                        (float) $event->getPrix(),
                        sprintf('NAJA7NI-EVT%d-REG%d', $event->getId(), $registration->getId()),
                        $firstName,
                        $lastName,
                        $registration->getVisitorEmail(),
                        '+21622222222', // Standard Paymee Sandbox Phone Number
                        $callbackUrl,
                        'Naja7ni - Inscription: ' . $event->getTitre()
                    );

                    if (isset($paymentData['status']) && $paymentData['status'] === true && isset($paymentData['data']['token'])) {
                        // Store token and redirect user to Paymee Gateway
                        $registration->setPaymentToken($paymentData['data']['token']);
                        $entityManager->flush();
                        
                        $this->addFlash('paymee_token', $paymentData['data']['token']);
                        $this->addFlash('registration_id_payment', $registration->getId());
                        return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
                    }

                    // Debugging: Log the problematic response
                    error_log("Paymee Payment Creation Failed (Registration): " . json_encode($paymentData));
                    $this->addFlash('error', 'Impossible de créer le paiement Paymee. Détails: ' . ($paymentData['message'] ?? 'Erreur inconnue de l\'API'));
                } catch (\Exception $e) {
                    error_log("Paymee Connection Error (Registration): " . $e->getMessage());
                    $this->addFlash('error', 'Erreur de paiement: ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            } elseif ((float) $event->getPrix() > 0 && $registration->getPaymentMethod() === 'espece') {
                // Event is paid via Cash - set to pending but no Paymee
                // We should increment inscrits to block the spot for the user
                $registration->setStatut('en_attente');
                $event->setInscrits($event->getInscrits() + 1);
                $entityManager->flush();
                
                // Store in session for guest users to see ticket button
                $request->getSession()->set('last_registration_id_' . $event->getId(), $registration->getId());
                
                $this->addFlash('success', 'Votre inscription est enregistrée ! Veuillez régler le montant de ' . $event->getPrix() . ' DT sur place.');
                $this->addFlash('registration_id', $registration->getId());
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }

            // Free Event or direct confirmation
            $registration->setStatut('confirmé');
            $event->setInscrits($event->getInscrits() + 1);
            $entityManager->flush();

            // Store in session for guest users to see ticket button
            $request->getSession()->set('last_registration_id_' . $event->getId(), $registration->getId());

            // Send admin notifications
            $notificationService->notifyNewRegistration($registration);
            $notificationService->checkCapacityWarning($event);

            $this->addFlash('success', 'Inscription réussie ! Au plaisir de vous voir à l\'événement.');
            $this->addFlash('registration_id', $registration->getId());
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/events/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/registration/{id}/ticket', name: 'app_front_registration_ticket')]
    public function downloadTicket(Registration $registration, \App\Service\PdfService $pdfService): Response
    {
        try {
            $pdfContent = $pdfService->generateTicketPdf($registration);
            $filename = $pdfService->getTicketFilename($registration);

            return new Response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
            return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
        }
    }

    #[Route('/registration/{id}/view', name: 'app_front_registration_ticket_view')]
    public function viewTicket(Registration $registration): Response
    {
        return $this->render('front/events/ticket_view.html.twig', [
            'registration' => $registration,
            'event' => $registration->getEvenement(),
        ]);
    }

    #[Route('/registration/{id}/cancel', name: 'app_front_registration_cancel', methods: ['POST'])]
    public function cancelRegistration(
        Registration $registration,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('cancel_registration_' . $registration->getId(), $request->request->get('_token'))) {
            $event = $registration->getEvenement();
            
            $providedName = trim((string)$request->request->get('visitorName'));
            $providedEmail = trim((string)$request->request->get('visitorEmail'));
            
            // Case-insensitive and trimmed comparison for security and UX
            if (strtolower($providedName) === strtolower(trim($registration->getVisitorName())) && 
                strtolower($providedEmail) === strtolower(trim($registration->getVisitorEmail()))) {
                
                $registration->setStatut('annulé');
                $event->setInscrits(max(0, $event->getInscrits() - 1));
                $entityManager->flush();

                $this->addFlash('success', 'Votre inscription a été annulée avec succès. Au plaisir de vous revoir !');
            } else {
                $this->addFlash('error', 'Les informations saisies (Nom ou Email) ne correspondent pas à cette inscription. Veuillez vérifier vos informations.');
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }
        }

        return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
    }
    #[Route('/payment/{id}/success', name: 'app_front_payment_success', methods: ['GET'])]
    public function paymentSuccess(
        Registration $registration,
        Request $request,
        \App\Service\PaymeeService $paymeeService,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService
    ): Response {
        $paymentToken = $request->query->get('payment_token');

        if (!$paymentToken) {
            $this->addFlash('error', 'Token de paiement manquant.');
            return $this->redirectToRoute('app_front_events');
        }

        try {
            $status = $paymeeService->checkPaymentStatus($paymentToken);

            // Paymee returns strictly true/false or a status string like "SUCCESS" depending on API doc
            // Typically their check API returns {"status": true, "data": {"payment_status": true}} etc.
            // Let's assume the API returns ['status' => true] for success or a property 'paid'
            // We should be careful and adapt to Paymee V1 Check response formats.
            if (isset($status['status']) && $status['status'] === true && isset($status['data']['payment_status']) && $status['data']['payment_status'] === true) {
                // Payment was successful!
                $event = $registration->getEvenement();

                if ($registration->getStatut() !== 'confirmé') {
                    $registration->setStatut('confirmé');
                    $event->setInscrits($event->getInscrits() + 1);
                    $entityManager->flush();

                    // Send admin notifications
                    $notificationService->notifyNewRegistration($registration);
                    $notificationService->checkCapacityWarning($event);

                    $this->addFlash('success', 'Paiement réussi ! Votre inscription est confirmée.');
                } else {
                    $this->addFlash('info', 'Ce paiement a déjà été traité.');
                }
                
                $this->addFlash('registration_id', $registration->getId());
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }

            $this->addFlash('error', 'Le paiement n\'a pas pu être vérifié ou a échoué.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la vérification du paiement: ' . $e->getMessage());
            return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
        }
    }

    #[Route('/payment/{id}/cancel', name: 'app_front_payment_cancel', methods: ['GET'])]
    public function paymentCancel(Registration $registration): Response
    {
        $this->addFlash('warning', 'Le paiement a été annulé. Votre inscription reste en attente.');
        return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
    }

    #[Route('/payment/{id}/retry', name: 'app_front_payment_retry', methods: ['GET'])]
    public function retryPayment(
        Registration $registration,
        \App\Service\PaymeeService $paymeeService,
        EntityManagerInterface $entityManager
    ): Response {
        if ($registration->getStatut() !== 'en_attente') {
            $this->addFlash('info', 'Cette inscription est déjà traitée.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
        }

        try {
            $returnUrl = $this->generateUrl('app_front_payment_cancel', ['id' => $registration->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            $callbackUrl = $this->generateUrl('app_front_payment_success', ['id' => $registration->getId()], \Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL);
            
            // Split name for V2
            $nameParts = explode(' ', $registration->getVisitorName(), 2);
            $firstName = $nameParts[0] ?? 'Client';
            $lastName = $nameParts[1] ?? 'Naja7ni';

            $paymentData = $paymeeService->createPayment(
                (float) $registration->getEvenement()->getPrix(),
                sprintf('NAJA7NI-EVT%d-REG%d', $registration->getEvenement()->getId(), $registration->getId()),
                $firstName,
                $lastName,
                $registration->getVisitorEmail(),
                '+21622222222', // Standard Paymee Sandbox Phone Number
                $callbackUrl,
                'Naja7ni - Inscription: ' . $registration->getEvenement()->getTitre()
            );

            if (isset($paymentData['status']) && $paymentData['status'] === true && isset($paymentData['data']['token'])) {
                $registration->setPaymentToken($paymentData['data']['token']);
                $entityManager->flush();
                
                $this->addFlash('paymee_token', $paymentData['data']['token']);
                $this->addFlash('registration_id_payment', $registration->getId());
                return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
            }

            $this->addFlash('error', 'Impossible de recréer le paiement Paymee.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de paiement: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
    }

    #[Route('/payment/{id}/check', name: 'app_front_payment_check', methods: ['GET'])]
    public function checkStatus(
        Registration $registration,
        \App\Service\PaymeeService $paymeeService,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
        Request $request
    ): Response {
        $token = $request->query->get('token') ?: $registration->getPaymentToken();
        if (!$token) {
            $this->addFlash('error', 'Token manquant et aucun token enregistré.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
        }

        try {
            $status = $paymeeService->checkPaymentStatus($token);
            if (isset($status['status']) && $status['status'] === true && isset($status['data']['payment_status']) && $status['data']['payment_status'] === true) {
                if ($registration->getStatut() !== 'confirmé') {
                    $registration->setStatut('confirmé');
                    $event = $registration->getEvenement();
                    $event->setInscrits($event->getInscrits() + 1);
                    $entityManager->flush();
                    $notificationService->notifyNewRegistration($registration);
                }
                $this->addFlash('success', 'Paiement confirmé.');
                $this->addFlash('registration_id', $registration->getId());
            } else {
                $this->addFlash('warning', 'Paiement non encore confirmé.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur verification: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_front_event_show', ['id' => $registration->getEvenement()->getId()]);
    }
}
