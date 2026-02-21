<?php

namespace App\Controller\Back;

use App\Entity\Registration;
use App\Form\RegistrationType;
use App\Repository\RegistrationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/back/registration')]
class RegistrationController extends AbstractController
{
    #[Route('/', name: 'back_registration_index', methods: ['GET'])]
    public function index(RegistrationRepository $registrationRepository): Response
    {
        return $this->render('back/registration/index.html.twig', [
            'registrations' => $registrationRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'back_registration_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $registration = new Registration();
        
        $registration->setStatut('en_attente');
        
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event = $registration->getEvenement();
            if ($event->getInscrits() >= $event->getCapacite()) {
                $this->addFlash('error', 'Cet événement est complet.');
                return $this->redirectToRoute('back_registration_new');
            }
            
            $event->setInscrits($event->getInscrits() + 1);
            
            $entityManager->persist($registration);
            $entityManager->flush();

            return $this->redirectToRoute('back_registration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/registration/new.html.twig', [
            'registration' => $registration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'back_registration_show', methods: ['GET'])]
    public function show(Registration $registration): Response
    {
        return $this->render('back/registration/show.html.twig', [
            'registration' => $registration,
        ]);
    }

    #[Route('/{id}/edit', name: 'back_registration_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Registration $registration, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationType::class, $registration);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('back_registration_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/registration/edit.html.twig', [
            'registration' => $registration,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'back_registration_delete', methods: ['POST'])]
    public function delete(Request $request, Registration $registration, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$registration->getId(), $request->request->get('_token'))) {
            $event = $registration->getEvenement();
            if ($event->getInscrits() > 0) {
                $event->setInscrits($event->getInscrits() - 1);
            }
            
            $entityManager->remove($registration);
            $entityManager->flush();
        }

        return $this->redirectToRoute('back_registration_index', [], Response::HTTP_SEE_OTHER);
    }
}
