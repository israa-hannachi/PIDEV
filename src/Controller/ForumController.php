<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/forum')]
class ForumController extends AbstractController
{
    // ===================== LISTE DES FORUMS =====================
    #[Route('/', name: 'app_forum_index', methods: ['GET'])]
    public function index(Request $request, ForumRepository $forumRepository): Response
    {
        $search    = $request->query->get('search');
        $direction = $request->query->get('direction', 'asc');

        $qb = $forumRepository->createQueryBuilder('f');

        if ($search) {
            $qb->andWhere('f.titre LIKE :search')
               ->setParameter('search', '%'.$search.'%');
        }

        // Utiliser le nom de la propriété PHP : createdAt
        if (!in_array(strtolower($direction), ['asc', 'desc'])) {
            $direction = 'asc';
        }
        $qb->orderBy('f.createdAt', $direction);

        $forums = $qb->getQuery()->getResult();

        // ✅ Ajout pour Ajax : renvoyer uniquement le fragment si la requête est Ajax
        if ($request->isXmlHttpRequest()) {
            return $this->render('partials/_forum_list.html.twig', [
                'forums' => $forums,
            ]);
        }

        return $this->render('forum/index.html.twig', [
            'forums' => $forums,
        ]);
    }

    // ===================== CREATION D’UN FORUM =====================
    #[Route('/new', name: 'forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $forum = new Forum();

        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$forum->getCreatedAt()) {
                $forum->setCreatedAt(new \DateTimeImmutable());
            }
            if (!$forum->getCreatedBy()) {
                $forum->setCreatedBy('Inconnu');
            }
            if (!$forum->getUserId()) {
                $forum->setUserId(0);
            }

            $em->persist($forum);
            $em->flush();

            $this->addFlash('success', 'Forum créé avec succès.');
            return $this->redirectToRoute('app_forum_index');
        }

        return $this->render('forum/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ===================== AFFICHER UN FORUM =====================
    #[Route('/{id}', name: 'forum_show', methods: ['GET'])]
    public function show(Forum $forum): Response
    {
        return $this->render('forum/show.html.twig', [
            'forum' => $forum,
        ]);
    }

    // ===================== MODIFIER UN FORUM =====================
    #[Route('/{id}/edit', name: 'forum_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$forum->getUserId()) {
                $forum->setUserId(0);
            }

            $em->flush();
            $this->addFlash('success', 'Forum modifié avec succès.');
            return $this->redirectToRoute('app_forum_index');
        }

        return $this->render('forum/edit.html.twig', [
            'forum' => $forum,
            'form'  => $form->createView(),
        ]);
    }

    // ===================== SUPPRIMER UN FORUM =====================
    #[Route('/{id}/delete', name: 'forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->request->get('_token'))) {
            $em->remove($forum);
            $em->flush();
            $this->addFlash('success', 'Forum supprimé avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide.');
        }

        return $this->redirectToRoute('app_forum_index');
    }

    // ===================== BLOQUER UN FORUM =====================
    #[Route('/{id}/block', name: 'forum_block', methods: ['GET'])]
    public function block(Forum $forum, EntityManagerInterface $em): Response
    {
        $forum->setIsBlocked(true);
        $em->flush();

        $this->addFlash('success', 'Forum bloqué avec succès.');
        return $this->redirectToRoute('app_forum_index');
    }

    // ===================== DÉBLOQUER UN FORUM =====================
    #[Route('/{id}/unblock', name: 'forum_unblock', methods: ['GET'])]
    public function unblock(Forum $forum, EntityManagerInterface $em): Response
    {
        $forum->setIsBlocked(false);
        $em->flush();

        $this->addFlash('success', 'Forum débloqué avec succès.');
        return $this->redirectToRoute('app_forum_index');
    }   
}
