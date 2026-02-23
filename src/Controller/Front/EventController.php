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
        } else {
            // Check session for guest registration
            $lastId = $request->getSession()->get('last_registration_id_' . $event->getId());
            if ($lastId) {
                $userRegistration = $registrationRepository->find($lastId);
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
