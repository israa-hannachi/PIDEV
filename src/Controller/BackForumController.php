<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/forum')]
class BackForumController extends AbstractController
{
    #[Route('/', name: 'app_back_forum_index', methods: ['GET'])]
    public function index(ForumRepository $forumRepository): Response
    {
        return $this->render('forum/Back/index.html.twig', [
            'forums' => $forumRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_back_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $forum = new Forum();
        
        // Pré-remplir le createdBy avec l'utilisateur connecté
        $user = $this->getUser();
        if ($user) {
            $creatorName = method_exists($user, 'getNom') && method_exists($user, 'getPrenom') 
                ? $user->getNom() . ' ' . $user->getPrenom() 
                : $user->getUserIdentifier();
            $forum->setCreatedBy($creatorName);
        } else {
            $forum->setCreatedBy('Anonyme');
        }
        
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création si elle n'est pas déjà définie
            if ($forum->getDateCreation() === null) {
                $forum->setDateCreation(new \DateTimeImmutable());
            }
            
            $em->persist($forum);
            $em->flush();

            return $this->redirectToRoute('app_back_forum_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('forum/Back/new.html.twig', [
            'forum' => $forum,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_back_forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            return $this->redirectToRoute('app_back_forum_index');
        }

        return $this->render('forum/Back/edit.html.twig', [ 
            'form' => $form,
            'forum' => $forum,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_back_forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->request->get('_token'))) {
            $em->remove($forum);
            $em->flush();
        }

        return $this->redirectToRoute('app_back_forum_index');
    }
}
