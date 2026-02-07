<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Forum;
use App\Form\MessageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum/{id}/messages')]
class MessageController extends AbstractController
{
    #[Route('/new', name: 'app_message_new', methods: ['GET', 'POST'])]
    public function new(
        Forum $forum,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $message = new Message();

        // Lien obligatoire avec le forum
        $message->setForum($forum);

        // ⚠️ createdAt est déjà initialisé dans le constructeur de Message
        // donc on NE le redéfinit PAS ici

        // Utilisateur en texte simple
        // (à adapter si tu ajoutes plus tard la sécurité)
        $message->setUtilisateur('Anonyme');

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();

            $this->addFlash('success', 'Message ajouté avec succès.');

            return $this->redirectToRoute('app_forum_show', [
                'id' => $forum->getId(),
            ]);
        }

        return $this->render('message/new.html.twig', [
            'forum' => $forum,
            'form' => $form->createView(),
        ]);
    }
}
