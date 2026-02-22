<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Message;
use App\Form\ForumType;
use App\Form\MessageType;
use App\Repository\ForumRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forum')]
final class ForumController extends AbstractController
{
    // ✅ INDEX : affiche tous les forums (Back-office)
    #[Route(name: 'app_forum_index', methods: ['GET'])]
    public function index(ForumRepository $forumRepository): Response
    {
        return $this->render('forum/index.html.twig', [
            'forums' => $forumRepository->findAll(),
        ]);
    }

    // ✅ FRONT : page principale avec navigation 3 niveaux
    #[Route('/front', name: 'app_forum_front', methods: ['GET', 'POST'])]
    public function front(
        Request $request,
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager,
        ForumRepository $forumRepository
    ): Response {
        // Formulaire de message vide (pour le formulaire de publication)
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($user) {
                $message->setCreatedBy($user->getUsername() ?? $user->getEmail() ?? 'Anonyme');
            } else {
                $message->setCreatedBy('Anonyme');
            }
            $message->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message publié avec succès !');
            return $this->redirectToRoute('app_forum_front');
        }

        return $this->render('forum/Front/show.html.twig', [
            'categories' => $categorieRepository->findAll(), // ✅ pour la navigation
            'forum'      => $forumRepository->find(1) ?? new Forum(), // forum par défaut
            'form'       => $form,
        ]);
    }

    // ✅ SHOW : affiche un forum spécifique avec ses messages
    #[Route('/{id}', name: 'app_forum_show', methods: ['GET', 'POST'])]
    public function show(
        Forum $forum,
        Request $request,
        CategorieRepository $categorieRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // Formulaire pour publier un message dans ce forum
        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $message->setCreatedBy(
                $user ? ($user->getUsername() ?? $user->getEmail() ?? 'Anonyme') : 'Anonyme'
            );
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setForum($forum); // ✅ lier le message au forum

            $entityManager->persist($message);
            $entityManager->flush();

            $this->addFlash('success', 'Message publié avec succès !');
            return $this->redirectToRoute('app_forum_show', ['id' => $forum->getId()]);
        }

        return $this->render('forum/Front/show.html.twig', [
            'categories' => $categorieRepository->findAll(), // ✅ pour la navigation
            'forum'      => $forum,
            'form'       => $form,
        ]);
    }

    // ✅ NEW
    #[Route('/new', name: 'app_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $forum = new Forum();

        $user = $this->getUser();
        if ($user) {
            $forum->setCreatedBy($user->getUsername() ?? $user->getEmail() ?? 'Utilisateur connecté');
        }

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $enteredName = $forum->getCreatedBy();
            if (!$enteredName || trim($enteredName) === '') {
                $user = $this->getUser();
                $forum->setCreatedBy(
                    $user ? ($user->getUsername() ?? $user->getEmail() ?? 'Utilisateur connecté') : 'Anonyme'
                );
            } else {
                $forum->setCreatedBy(trim($enteredName));
            }

            if ($forum->getDateCreation() === null) {
                $forum->setDateCreation(new \DateTimeImmutable());
            }

            if ($forum->getEtat() === null) {
                $forum->setEtat('Actif');
            }

            $entityManager->persist($forum);
            $entityManager->flush();

            return $this->redirectToRoute('app_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/new.html.twig', [
            'forum' => $forum,
            'form'  => $form,
        ]);
    }

    // ✅ EDIT
    #[Route('/{id}/edit', name: 'app_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            'form'  => $form,
        ]);
    }

    // ✅ DELETE
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