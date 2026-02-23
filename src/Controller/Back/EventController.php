<?php
// src/Controller/Back/EventController.php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/back/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'back_event_index', methods: ['GET'])]
    public function index(
        EventRepository $eventRepository,
        \App\Repository\RegistrationRepository $registrationRepository,
        \App\Repository\SponsorRepository $sponsorRepository,
        \App\Service\NotificationService $notificationService
    ): Response {
        $events = $eventRepository->findAll();
        $registrations = $registrationRepository->findAll();
        $sponsors = $sponsorRepository->findAll();

        // Financial stats
        $totalRevenue = 0;
        foreach ($registrations as $reg) {
            if ($reg->getStatut() === 'confirmé' || $reg->getStatut() === 'inscrit') {
                $totalRevenue += (float)$reg->getEvenement()->getPrix();
            }
        }

        $totalSponsorship = 0;
        foreach ($sponsors as $sponsor) {
            $totalSponsorship += (float)$sponsor->getMontant();
        }

        // Stats by category
        $statsByCategory = [];
        foreach ($events as $event) {
            $cat = $event->getCategorie();
            $statsByCategory[$cat] = ($statsByCategory[$cat] ?? 0) + 1;
        }

        return $this->render('back/event/index.html.twig', [
            'events' => $events,
            'total_events' => count($events),
            'total_registrations' => count($registrations),
            'total_sponsors' => count($sponsors),
            'total_revenue' => $totalRevenue,
            'total_sponsorship' => $totalSponsorship,
            'stats_category_labels' => array_keys($statsByCategory),
            'stats_category_data' => array_values($statsByCategory),
            'admin_notifications' => $notificationService->getAdminNotifications(),
        ]);
    }

    #[Route('/calendar', name: 'back_event_calendar', methods: ['GET'])]
    public function calendar(): Response
    {
        return $this->render('back/event/calendar.html.twig');
    }

    #[Route('/api/calendar', name: 'back_event_api_calendar', methods: ['GET'])]
    public function calendarApi(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        $calendarEvents = [];

        foreach ($events as $event) {
            $calendarEvents[] = [
                'id' => $event->getId(),
                'title' => $event->getTitre(),
                'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $event->getDateFin() ? $event->getDateFin()->format('Y-m-d\TH:i:s') : null,
                'url' => $this->generateUrl('back_event_show', ['id' => $event->getId()]),
                'backgroundColor' => $event->getStatut() === 'planifié' ? '#f59e0b' : '#10b981', // warning/success colors
                'borderColor' => $event->getStatut() === 'planifié' ? '#f59e0b' : '#10b981',
                'textColor' => '#ffffff'
            ];
        }

        return $this->json($calendarEvents);
    }

    #[Route('/new', name: 'back_event_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request, 
        EntityManagerInterface $entityManager, 
        SluggerInterface $slugger,
        \App\Service\NotificationService $notificationService
    ): Response
    {
        $event = new Event();

        // Check if we came from the calendar with a pre-selected date
        $startDateParam = $request->query->get('start_date');
        if ($startDateParam) {
            try {
                $startDate = new \DateTime($startDateParam);
                $event->setDateDebut($startDate);
                
                // Set default end date to 1 hour after start date for convenience
                $endDate = clone $startDate;
                $endDate->modify('+1 hour');
                $event->setDateFin($endDate);
            } catch (\Exception $e) {
                // Ignore invalid date format
            }
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$event->getStatut()) {
                $event->setStatut('planifié');
            }
            
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/assets/uploads/events',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $event->setImage('assets/uploads/events/'.$newFilename);
            }
            
            $entityManager->persist($event);
            $entityManager->flush();

            // Notify admin
            $notificationService->notifyEventCreated($event);
            $this->addFlash('success', 'Event created successfully and notifications sent.');

            return $this->redirectToRoute('back_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'back_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $registrations = $event->getRegistrations();
        $sponsors = $event->getSponsors();

        return $this->render('back/event/show.html.twig', [
            'event' => $event,
            'registrations' => $registrations,
            'sponsors' => $sponsors,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir').'/public/assets/uploads/events',
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                $event->setImage('assets/uploads/events/'.$newFilename);
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('back_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/ai/improve', name: 'back_event_ai_improve', methods: ['POST'])]
    public function aiImprove(Request $request, \App\Service\AIService $aiService): Response
    {
        $description = $request->request->get('description');
        if (!$description) {
            return $this->json(['success' => false, 'error' => 'Description manquante']);
        }

        $suggestions = $aiService->improveEventMetadata($description);
        return $this->json($suggestions);
    }

    #[Route('/{id}', name: 'back_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_event_index', [], Response::HTTP_SEE_OTHER);
    }
}