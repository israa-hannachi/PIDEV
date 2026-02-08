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
        NotificationService $notificationService
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
            $event->setInscrits($event->getInscrits() + 1);
            $entityManager->persist($registration);
            $entityManager->flush();

            // Send admin notifications
            $notificationService->notifyNewRegistration($registration);
            $notificationService->checkCapacityWarning($event);

            $this->addFlash('success', 'Registration successful! See you at the event.');
            $this->addFlash('registration_id', $registration->getId());
            return $this->redirectToRoute('app_front_event_show', ['id' => $event->getId()]);
        }

        return $this->render('front/show.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/registration/{id}/ticket', name: 'app_front_registration_ticket')]
    public function downloadTicket(Registration $registration): Response
    {
        // Simple PDF generation using basic HTML for now (requires dompdf)
        $html = $this->renderView('front/ticket_pdf.html.twig', [
            'registration' => $registration,
            'event' => $registration->getEvenement(),
        ]);

        if (class_exists(\Dompdf\Dompdf::class)) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            return new Response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ticket-' . $registration->getId() . '.pdf"',
            ]);
        }

        // Fallback or error if dompdf not installed
        return new Response("PDF generation is currently being set up. Please try again in a few moments.", 200);
    }
}
