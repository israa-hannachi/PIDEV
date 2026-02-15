<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontForumController extends AbstractController
{
    #[Route('/front/forum', name: 'app_front_forum')]
    public function index(ForumRepository $forumRepository): Response
    {
        return $this->render('front_forum/index.html.twig', [
            'forums' => $forumRepository->findAll(),
        ]);
    }

    #[Route('/front/forum/{id}', name: 'app_front_forum_show', methods: ['GET', 'POST'])]
    public function show(Forum $forum, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Nouveau message lié au forum
        $message = new Message();
        $message->setForum($forum);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les valeurs du formulaire
            $nom = $message->getCreatedBy();
            $contenu = $message->getContenu();
            
            // Auteur : connecté ou invité
            if ($this->getUser()) {
                $message->setCreatedBy($this->getUser()->getUsername());
            } else {
                // Utiliser le nom entré dans le formulaire, ou "Anonyme" si vide
                $enteredName = $message->getCreatedBy();
                $message->setCreatedBy($enteredName ?: 'Anonyme');
            }

            $message->setDatePublication(new \DateTimeImmutable());
            $message->setEtat('Actif');

            $entityManager->persist($message);
            $entityManager->flush();

            // Ajouter les messages flash avec les valeurs nettoyées
            $this->addFlash('success', "Message publié avec succès !");

            return $this->redirectToRoute('app_front_forum_show', ['id' => $forum->getId()]);
        }

        return $this->render('front_forum/show.html.twig', [
            'forum' => $forum,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/front/forum/{forumId}/message/{id}/edit', name: 'app_front_forum_message_edit', methods: ['GET','POST'])]
    public function edit(int $forumId, Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        // Traitement manuel pour les données envoyées par JavaScript
        if ($request->isMethod('POST')) {
            $createdBy = $request->request->get('createdBy');
            $contenu = $request->request->get('contenu');
            
            if ($createdBy && $contenu) {
                $message->setCreatedBy($createdBy);
                $message->setContenu($contenu);
                $message->setDatePublication(new \DateTimeImmutable());
                
                $entityManager->flush();
                
                // Retourner une réponse JSON pour le JavaScript
                return new Response('', 200);
            }
        }

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setDatePublication(new \DateTimeImmutable());
            $entityManager->flush();

            $this->addFlash('success', 'Message modifié avec succès !');

            return $this->redirectToRoute('app_front_forum_show', ['id' => $message->getForum()->getId()]);
        }

        return $this->render('forum/edit.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/front/forum/message/{id}/delete', name: 'app_front_forum_message_delete', methods: ['POST'])]
    public function deleteMessage(Message $message, Request $request, EntityManagerInterface $entityManager): Response
    {
        $forumId = $message->getForum()->getId();
        
        $entityManager->remove($message);
        $entityManager->flush();
        
        $this->addFlash('success', 'Message supprimé avec succès !');
        
        return $this->redirectToRoute('app_front_forum_show', ['id' => $forumId]);
    }

    #[Route('/front/forum/message/{id}/like', name: 'app_front_forum_like', methods: ['POST'])]
    public function like(Message $message, Request $request, EntityManagerInterface $entityManager): Response
    {
        $forumId = $message->getForum()->getId();
        
        // Ajouter ici la logique pour enregistrer le like si nécessaire
        
        $this->addFlash('success', 'Vous avez aimé ce message !');
        
        return $this->redirectToRoute('app_front_forum_show', ['id' => $forumId]);
    }

    #[Route('/front/forum/message/{id}/dislike', name: 'app_front_forum_dislike', methods: ['POST'])]
    public function dislike(Message $message, Request $request, EntityManagerInterface $entityManager): Response
    {
        $forumId = $message->getForum()->getId();
        
        // Ajouter ici la logique pour enregistrer le dislike si nécessaire
        
        $this->addFlash('info', 'Vous n\'avez pas aimé ce message.');
        
        return $this->redirectToRoute('app_front_forum_show', ['id' => $forumId]);
    }

}
