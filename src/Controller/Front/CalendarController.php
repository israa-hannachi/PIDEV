<?php

namespace App\Controller\Front;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\ICalService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/calendar', name: 'calendar_')]
class CalendarController extends AbstractController
{
    public function __construct(
        private EventRepository $eventRepository,
        private ICalService $icalService,
        private EntityManagerInterface $em
    ) {
    }

    /**
     * Display the calendar with all events
     */
    #[Route('', name: 'index')]
    public function index(): Response
    {
        $events = $this->eventRepository->findBy(
            ['statut' => 'planifié'],
            ['dateDebut' => 'ASC']
        );

        // Convert events to calendar format
        $calendarEvents = array_map(function (Event $event) {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitre(),
                'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $event->getDateFin()->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'location' => $event->getLieu(),
                    'description' => $event->getDescription(),
                    'category' => $event->getCategorie(),
                    'price' => $event->getPrix(),
                    'capacity' => $event->getCapacite(),
                    'registered' => $event->getInscrits(),
                    'timezone' => $event->getTimeZone(),
                ],
            ];
        }, $events);

        return $this->render('front/calendar/index.html.twig', [
            'events' => $events,
            'calendar_events' => json_encode($calendarEvents),
        ]);
    }

    /**
     * View single event details
     */
    #[Route('/{id}', name: 'show')]
    public function show(Event $event): Response
    {
        return $this->render('front/calendar/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * Create a new event with iCal export (requires authentication)
     */
    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request): Response
    {
        $event = new Event();
        
        // Set current user as organizer if authenticated
        if ($this->getUser()) {
            $event->setOrganizerEmail($this->getUser()->getEmail());
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validate recurrence settings
            if ($event->isRecurring() && !$event->getRecurrenceFrequency()) {
                $this->addFlash('warning', 'Recurrence frequency is required for recurring events.');
                return $this->redirectToRoute('calendar_create');
            }

            // Parse attendees emails
            $attendeesInput = $form->get('attendeesEmails')->getData();
            if ($attendeesInput) {
                if (str_starts_with(trim($attendeesInput), '[')) {
                    // JSON format validation
                    json_decode($attendeesInput, true);
                }
                $event->setAttendeesEmails($attendeesInput);
            }

            $this->em->persist($event);
            $this->em->flush();

            // Generate iCal file
            $result = $this->icalService->generateICalFile($event);
            
            if ($result['success']) {
                $event->setIcalId($result['ical_id']);
                $this->em->flush();
                
                $this->addFlash('success', 'Event created successfully! iCal file generated.');
                return $this->redirectToRoute('calendar_show', ['id' => $event->getId()]);
            } else {
                $this->addFlash('error', 'Event created but iCal generation failed: ' . $result['error']);
                return $this->redirectToRoute('calendar_show', ['id' => $event->getId()]);
            }
        }

        return $this->render('front/calendar/form.html.twig', [
            'form' => $form,
            'action' => 'Create',
        ]);
    }

    /**
     * Edit an event
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Event $event): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($event->isRecurring() && !$event->getRecurrenceFrequency()) {
                $this->addFlash('warning', 'Recurrence frequency is required for recurring events.');
                return $this->redirectToRoute('calendar_edit', ['id' => $event->getId()]);
            }

            $this->em->flush();

            // Regenerate iCal file
            $result = $this->icalService->generateICalFile($event);
            
            if (!$result['success']) {
                $this->addFlash('warning', 'Event updated but iCal regeneration failed.');
            } else {
                $this->addFlash('success', 'Event updated successfully!');
            }

            return $this->redirectToRoute('calendar_show', ['id' => $event->getId()]);
        }

        return $this->render('front/calendar/form.html.twig', [
            'form' => $form,
            'event' => $event,
            'action' => 'Edit',
        ]);
    }

    /**
     * Download iCal file for an event
     */
    #[Route('/{id}/download-ical', name: 'download_ical')]
    public function downloadIcal(Event $event): Response
    {
        $result = $this->icalService->generateICalFile($event);

        if (!$result['success']) {
            throw $this->createNotFoundException('Failed to generate iCal file: ' . $result['error']);
        }

        return new Response(
            $result['data'],
            Response::HTTP_OK,
            [
                'Content-Type' => 'text/calendar; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="' . $this->sanitizeFileName($event->getTitre()) . '.ics"',
            ]
        );
    }

    /**
     * Cancel an event and generate cancellation iCal
     */
    #[Route('/{id}/cancel', name: 'cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('cancel' . $event->getId(), $request->request->get('_token'))) {
            $event->setStatut('annulé');
            $this->em->flush();

            // Generate cancellation iCal
            $result = $this->icalService->cancelEvent($event);

            if ($result['success']) {
                $this->addFlash('success', 'Event cancelled. Cancellation notice sent.');
            } else {
                $this->addFlash('warning', 'Event cancelled but cancellation notice failed.');
            }

            return $this->redirectToRoute('calendar_index');
        }

        throw $this->createAccessDeniedException('Invalid CSRF token');
    }

    /**
     * Get events as JSON for calendar plugin (AJAX)
     */
    #[Route('/api/events', name: 'api_events')]
    public function apiEvents(Request $request): JsonResponse
    {
        $filter = $request->query->get('filter', 'all'); // all, upcoming, past
        $category = $request->query->get('category');
        $month = $request->query->get('month');

        $qb = $this->eventRepository->createQueryBuilder('e');

        // Status filter
        if ($filter === 'upcoming') {
            $qb->where('e.dateDebut >= :now')
                ->setParameter('now', new \DateTime());
        } elseif ($filter === 'past') {
            $qb->where('e.dateFin < :now')
                ->setParameter('now', new \DateTime());
        }

        // Category filter
        if ($category) {
            $qb->andWhere('e.categorie = :category')
                ->setParameter('category', $category);
        }

        // Month filter
        if ($month) {
            $startDate = \DateTime::createFromFormat('Y-m', $month);
            $endDate = clone $startDate;
            $endDate->modify('last day of this month');
            
            $qb->andWhere('e.dateDebut >= :start AND e.dateDebut <= :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate);
        }

        $events = $qb->orderBy('e.dateDebut', 'ASC')->getQuery()->getResult();

        $calendarEvents = array_map(function (Event $event) {
            return [
                'id' => $event->getId(),
                'title' => $event->getTitre(),
                'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $event->getDateFin()->format('Y-m-d\TH:i:s'),
                'extendedProps' => [
                    'location' => $event->getLieu(),
                    'description' => substr($event->getDescription(), 0, 100) . '...',
                    'category' => $event->getCategorie(),
                    'timezone' => $event->getTimeZone(),
                ],
            ];
        }, $events);

        return $this->json($calendarEvents);
    }

    /**
     * Get personal calendar (current user's events)
     */
    #[Route('/my-calendar', name: 'my_calendar')]
    #[IsGranted('ROLE_USER')]
    public function myCalendar(): Response
    {
        $user = $this->getUser();
        
        // Get events where user is organizer or registered
        $organizedEvents = $this->eventRepository->findBy(['organizerEmail' => $user->getEmail()]);
        
        // You can add registered events logic based on your Registration entity
        
        return $this->render('front/calendar/my-calendar.html.twig', [
            'organized_events' => $organizedEvents,
        ]);
    }

    /**
     * Sanitize filename for download
     */
    private function sanitizeFileName(string $filename): string
    {
        $filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);
        return substr($filename, 0, 50) . '.ics';
    }
}
