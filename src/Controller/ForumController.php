<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Message;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forum')]
final class ForumController extends AbstractController
{
    #[Route(name: 'app_forum_index', methods: ['GET'])]
    public function index(ForumRepository $forumRepository): Response
    {
        return $this->render('forum/index.html.twig', [
            'forums' => $forumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $forum = new Forum();
        
        // Pré-remplir le createdBy avec l'utilisateur connecté
        $user = $this->getUser();
        if ($user) {
            $forum->setCreatedBy($user->getUsername() ?? $user->getEmail() ?? 'Utilisateur connecté');
        }
        
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utiliser le nom entré dans le formulaire, ou l'utilisateur connecté si vide
            $enteredName = $forum->getCreatedBy();
            if ($enteredName && trim($enteredName) !== '') {
                $forum->setCreatedBy(trim($enteredName));
            } else {
                // Si le champ est vide, utiliser l'utilisateur connecté
                $user = $this->getUser();
                if ($user) {
                    $forum->setCreatedBy($user->getUsername() ?? $user->getEmail() ?? 'Utilisateur connecté');
                } else {
                    $forum->setCreatedBy('Anonyme');
                }
            }
            
            // Définir la date de création si elle n'est pas déjà définie
            if ($forum->getDateCreation() === null) {
                $forum->setDateCreation(new \DateTimeImmutable());
            }
            
            // Définir l'état par défaut si non défini
            if ($forum->getEtat() === null) {
                $forum->setEtat('Actif');
            }
            
            $entityManager->persist($forum);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/new.html.twig', [
            'forum' => $forum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_show', methods: ['GET'])]
    public function show(Forum $forum): Response
    {
        return $this->render('forum/show.html.twig', [
            'forum' => $forum,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le champ createdBy est vide, le remplir avec l'utilisateur connecté
            if (!$forum->getCreatedBy() || $forum->getCreatedBy() === 'Utilisateur anonyme') {
                $user = $this->getUser();
                if ($user) {
                    $forum->setCreatedBy($user->getUsername() ?? $user->getEmail() ?? 'Utilisateur connecté');
                }
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/edit.html.twig', [
            'forum' => $forum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($forum);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
    }
}
