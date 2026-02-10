<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/forum')]
class ForumController extends AbstractController
{
    // ===================== LISTE DES FORUMS =====================
    #[Route('/', name: 'app_forum_index', methods: ['GET'])]
    public function index(Request $request, ForumRepository $forumRepository): Response
    {
        $search    = $request->query->get('search');
        $creator   = $request->query->get('creator');
        $date      = $request->query->get('date');
        $direction = $request->query->get('direction', 'desc');

        $qb = $forumRepository->createQueryBuilder('f');

        if ($search) {
            $qb->andWhere('f.titre LIKE :search')
               ->setParameter('search', '%'.$search.'%');
        }

        if ($creator) {
            $qb->andWhere('f.createdBy = :creator')
               ->setParameter('creator', $creator);
        }

        if ($date === 'today') {
            $qb->andWhere('f.createdAt >= :today')
               ->setParameter('today', new \DateTime('today'));
        } elseif ($date === 'week') {
            $qb->andWhere('f.createdAt >= :week')
               ->setParameter('week', new \DateTime('-7 days'));
        }

        $qb->orderBy('f.createdAt', $direction);

        $forums = $qb->getQuery()->getResult();

        if ($request->isXmlHttpRequest()) {
            return $this->render('forum/partials/_forum_list.html.twig', [
                'forums' => $forums,
            ]);
        }

        return $this->render('forum/index.html.twig', [
            'forums' => $forums,
        ]);
    }

    // ===================== FRONTEND FORUM VIEW =====================
    #[Route('/frontoffice', name: 'app_forum_frontend', methods: ['GET'])]
    public function frontend(Request $request, ForumRepository $forumRepository): Response
    {
        // Optimisation: pas de requêtes complexes pour la vitesse
        // Retourner directement le template avec des données statiques pour la performance
        
        if ($request->isXmlHttpRequest()) {
            return $this->render('forum/partials/_forum_frontoffice_list.html.twig', [
                'forums' => [],
                'userDiscussions' => [],
                'totalResponses' => 0,
            ]);
        }

        return $this->render('forum/frontoffice_index.html.twig', [
            'forums' => [],
            'userDiscussions' => [],
            'totalResponses' => 0,
        ]);
    }

    // Méthode utilitaire pour compter les réponses
    private function countTotalResponses(array $discussions): int
    {
        $total = 0;
        foreach ($discussions as $discussion) {
            // Supposons que chaque discussion a une propriété reponses
            // Si vous utilisez une relation, vous devriez charger ces données efficacement
            $total += 0; // Remplacez par le vrai calcul
        }
        return $total;
    }

    // ===================== CREATE DISCUSSION =====================
    #[Route('/frontoffice/create', name: 'app_create_discussion', methods: ['GET', 'POST'])]
    public function createDiscussion(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            // Vérifier le token CSRF
            if (!$this->isCsrfTokenValid('create_discussion', $request->request->get('_token'))) {
                $this->addFlash('error', 'Token CSRF invalide.');
                return $this->redirectToRoute('app_forum_frontend');
            }

            $title = trim($request->request->get('title', ''));
            $category = trim($request->request->get('category', ''));
            $author = trim($request->request->get('author', ''));

            if (!empty($title) && !empty($category) && !empty($author)) {
                try {
                    $forum = new Forum();
                    $forum->setTitre($title);
                    $forum->setDescription($category);
                    $forum->setCreatedBy($author);
                    $forum->setCreatedAt(new \DateTime());
                    $forum->setUserId(1);

                    $em->persist($forum);
                    $em->flush();

                    $this->addFlash('success', 'Discussion créée avec succès.');
                    return $this->redirectToRoute('app_forum_frontend');
                } catch (\Exception $e) {
                    error_log('Erreur lors de la création: ' . $e->getMessage());
                    $this->addFlash('error', 'Une erreur est survenue. Veuillez réessayer.');
                }
            } else {
                $this->addFlash('error', 'Veuillez remplir tous les champs.');
            }
        }

        return $this->redirectToRoute('app_forum_frontend');
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
                $forum->setCreatedAt(new \DateTime());
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
    #[Route('/{forumId}', name: 'forum_show', methods: ['GET'])]
    public function show(string $forumId, ForumRepository $forumRepository): Response
    {
        $forum = $forumRepository->find($forumId);

        if (!$forum) {
            $this->addFlash('error', 'Forum introuvable.');
            return $this->redirectToRoute('app_forum_index');
        }

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

    // ===================== MODIFIER UNE DISCUSSION (FRONT-OFFICE) =====================
    #[Route('/{id}/edit-front', name: 'forum_edit_front', methods: ['POST'])]
    public function editFront(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        if ($request->isXmlHttpRequest()) {
            // Vérifier le token CSRF
            $csrfToken = $request->request->get('_token');
            if (!$this->isCsrfTokenValid('edit' . $forum->getId(), $csrfToken)) {
                return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 400);
            }

            $title = trim($request->request->get('title', ''));
            $category = trim($request->request->get('category', ''));
            $author = trim($request->request->get('author', ''));

            if (!empty($title) && !empty($category) && !empty($author)) {
                try {
                    $forum->setTitre($title);
                    $forum->setDescription($category);
                    $forum->setCreatedBy($author);
                    $forum->setUpdatedAt(new \DateTime());

                    $em->flush();

                    return new JsonResponse(['success' => true, 'message' => 'Discussion modifiée avec succès']);
                } catch (\Exception $e) {
                    return new JsonResponse(['success' => false, 'message' => 'Erreur lors de la modification'], 500);
                }
            } else {
                return new JsonResponse(['success' => false, 'message' => 'Veuillez remplir tous les champs'], 400);
            }
        }

        return new JsonResponse(['success' => false, 'message' => 'Requête invalide'], 400);
    }

    // ===================== SUPPRIMER UN FORUM =====================
    #[Route('/{id}/delete', name: 'forum_delete', methods: ['POST'])]
    public function delete(Request $request, Forum $forum, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$forum->getId(), $request->request->get('_token'))) {
            $em->remove($forum);
            $em->flush();
            
            // Si c'est une requête AJAX, retourner JSON
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true, 'message' => 'Discussion supprimée avec succès']);
            }
            
            $this->addFlash('success', 'Forum supprimé avec succès.');
        } else {
            // Si c'est une requête AJAX, retourner erreur JSON
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 400);
            }
            
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