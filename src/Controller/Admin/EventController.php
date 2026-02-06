<?php
// src/Controller/Admin/EventController.php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/event')]
class EventController extends AbstractController
{
    #[Route('/', name: 'admin_event_index', methods: ['GET'])]
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
            if ($reg->getPaiementStatut() === 'payé' || $reg->getStatut() === 'confirmé') {
                $totalRevenue += (float)$reg->getMontantPaye();
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

        return $this->render('admin/event/index.html.twig', [
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

    #[Route('/new', name: 'admin_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$event->getStatut()) {
                $event->setStatut('planifié');
            }
            
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        $registrations = $event->getRegistrations();
        $sponsors = $event->getSponsors();

        return $this->render('admin/event/show.html.twig', [
            'event' => $event,
            'registrations' => $registrations,
            'sponsors' => $sponsors,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('admin_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
