<?php
// src/Controller/Front/MainController.php

namespace App\Controller\Front;

use App\Repository\EventRepository;
use App\Repository\SponsorRepository;
use App\Entity\Event;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'app_front_home')]
    public function index(EventRepository $eventRepository, SponsorRepository $sponsorRepository): Response
    {
        return $this->render('front/index.html.twig', [
            'events' => $eventRepository->findBy(['statut' => 'planifié'], ['dateDebut' => 'ASC']),
            'sponsors' => $sponsorRepository->findBy(['statut' => 'actif']),
        ]);
    }

    #[Route('/event/{id}', name: 'app_front_event_show')]
    public function show(Event $event): Response
    {
        return $this->render('front/show.html.twig', [
            'event' => $event,
        ]);
    }
}
