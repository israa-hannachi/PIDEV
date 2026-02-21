<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\Rating;
use App\Form\RegistrationType;
use App\Entity\Registration;
use App\Service\NotificationService;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_event_show', methods: ['GET', 'POST'])]
    public function show(
        Event $event, 
        Request $request, 
        EntityManagerInterface $entityManager, 
        NotificationService $notificationService, 
        RegistrationRepository $registrationRepository, 
        \App\Service\PaymeeService $paymeeService,
        \App\Repository\RecommendationCacheRepository $recRepo,
        \App\Service\EventRecommendationEngine $recEngine
    ): Response
    {
        $userRegistration = null;
        $recommendation = null;
        $user = $this->getUser();

        if ($user) {
            $userRegistration = $registrationRepository->findOneBy([
                'evenement' => $event,
                'visitorEmail' => $user->getEmail(),
                'statut' => ['en_attente', 'confirmé', 'inscrit']
            ]);

            $recommendation = $recRepo->findOneBy(['user' => $user, 'event' => $event]);
            
            // Proactive AI Analysis if missing
            if (!$recommendation) {
                try {
                    $recommendation = $recEngine->triggerAnalysis($user, $event);
                } catch (\Exception $e) {
                    error_log("AI Recommendation Engine Error: " . $e->getMessage());
                }
            }
        }

        $registration = new Registration();
        $registration->setEvenement($event);
        
        // Pre-fill user data if available
        if ($user) {
            $registration->setVisitorName($user->getFirstName() . ' ' . $user->getLastName());
            $registration->setVisitorEmail($user->getEmail());
        }

        $form = $this->createForm(RegistrationType::class, $registration);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getInscrits() >= $event->getCapacite()) {
                $this->addFlash('error', 'Cet événement est complet.');
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }

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
                        // Store the token and redirect user to Paymee Gateway
                        $registration->setPaymentToken($paymentData['data']['token']);
                        $entityManager->flush();
                        
                        $this->addFlash('paymee_token', $paymentData['data']['token']);
                        $this->addFlash('registration_id_payment', $registration->getId());
                        return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
                    }

                    // Debugging: Log the problematic response
                    error_log("Paymee Payment Creation Failed: " . json_encode($paymentData));
                    $this->addFlash('error', 'Impossible de créer le paiement Paymee. Détails: ' . ($paymentData['message'] ?? 'Erreur inconnue de l\'API'));
                } catch (\Exception $e) {
                    error_log("Paymee Connection Error: " . $e->getMessage());
                    $this->addFlash('error', 'Erreur de paiement: ' . $e->getMessage());
                }

                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            } elseif ((float) $event->getPrix() > 0 && $registration->getPaymentMethod() === 'espece') {
                // Event is paid via Cash - set to pending but no Paymee
                $registration->setStatut('en_attente');
                $entityManager->flush();
                
                $this->addFlash('success', 'Votre inscription est enregistrée ! Veuillez régler le montant de ' . $event->getPrix() . ' DT sur place le jour de l\'événement.');
                $this->addFlash('registration_id', $registration->getId());
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }

            $event->setInscrits($event->getInscrits() + 1);
            $registration->setStatut('confirmé');
            $entityManager->flush();

            // Send admin notifications
            $notificationService->notifyNewRegistration($registration);
            $notificationService->checkCapacityWarning($event);

            $this->addFlash('success', 'Votre inscription est réussie ! Bienvenue à l\'événement.');
            $this->addFlash('registration_id', $registration->getId());
            
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/events/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'userRegistration' => $userRegistration,
            'recommendation' => $recommendation,
        ]);
    }



    #[Route('/{id}/rate', name: 'app_front_event_rate', methods: ['POST'])]
    public function rate(Event $event, Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = $request->request->all('rating');
        
        if (isset($data['stars']) && isset($data['comment'])) {
            $rating = new Rating();
            $rating->setEvent($event);
            $rating->setStars((int) $data['stars']);
            $rating->setComment($data['comment']);
            $rating->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($rating);
            $entityManager->flush();

            $this->addFlash('success', 'Merci pour votre avis !');
        } else {
            $this->addFlash('error', 'Veuillez remplir tous les champs.');
        }

        return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
    }

    #[Route('/{id}/not-interested', name: 'app_front_event_not_interested', methods: ['POST'])]
    public function notInterested(Event $event, Request $request, \App\Service\UserBehaviorTracker $behaviorTracker): Response
    {
        if (!$this->getUser()) {
            return $this->json(['error' => 'You must be logged in.'], 403);
        }

        $behaviorTracker->trackNotInterested($this->getUser(), $event);

        return $this->json(['success' => true]);
    }
}
