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

class RegistrationController extends AbstractController
{
    #[Route('/event/{id}/register', name: 'app_front_event_register', methods: ['GET', 'POST'])]
    public function register(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($event->getInscrits() >= $event->getCapacite()) {
            $this->addFlash('error', 'Sorry, this event is already full.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        $registration = new Registration();
        $registration->setEvenement($event);
        
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setInscrits($event->getInscrits() + 1);
            $entityManager->persist($registration);
            $entityManager->flush();

            $this->addFlash('success', 'Registration successful! See you at the event.');
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/register.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }
}
