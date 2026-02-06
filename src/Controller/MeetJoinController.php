<?php

namespace App\Controller;

use App\Entity\Meet;
use App\Entity\Participant;
use App\Form\ParticipantType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/meet')]
class MeetJoinController extends AbstractController
{
    #[Route('/join/{id}', name: 'app_meet_join', methods: ['GET', 'POST'])]
    public function join(Meet $meet, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Créer un nouveau participant pour rejoindre la réunion
        $participant = new Participant();
        $form = $this->createForm(ParticipantType::class, $participant);
        
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $participant->setCreatedAt(new \DateTime());
            
            try {
                $entityManager->persist($participant);
                $entityManager->flush();

                $this->addFlash('success', 'Participant créé avec succès! Vous pouvez maintenant rejoindre la réunion.');

                // Rediriger vers la salle (room) de la réunion
                return $this->redirectToRoute('app_meet_room', ['id' => $meet->getId()]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la création: ' . $e->getMessage());
            }
        }

        return $this->render('meet/join_front.html.twig', [
            'meet' => $meet,
            'form' => $form,
        ]);
    }
}
