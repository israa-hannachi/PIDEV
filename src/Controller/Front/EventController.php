<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Entity\Rating;
use App\Form\RegistrationType;
use App\Entity\Registration;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/event')]
class EventController extends AbstractController
{
    #[Route('/{id}', name: 'app_front_event_show', methods: ['GET', 'POST'])]
    public function show(Event $event, Request $request, EntityManagerInterface $entityManager, NotificationService $notificationService): Response
    {
        $registration = new Registration();
        $registration->setEvenement($event);
        $form = $this->createForm(RegistrationType::class, $registration);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->getInscrits() >= $event->getCapacite()) {
                $this->addFlash('error', 'Cet événement est complet.');
                return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
            }

            $event->setInscrits($event->getInscrits() + 1);
            $entityManager->persist($registration);
            $entityManager->flush();

            // Send admin notifications
            $notificationService->notifyNewRegistration($registration);
            $notificationService->checkCapacityWarning($event);

            $this->addFlash('success', 'Registration successful!');
            $this->addFlash('registration_id', $registration->getId());
            
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
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
}
