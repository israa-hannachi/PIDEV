<?php

namespace App\Controller;

use App\Entity\Forum;
use App\Entity\Message;
use App\Form\MessageType;
use App\Form\ForumType;
use App\Repository\ForumRepository;
use App\Repository\MessageRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class FrontForumController extends AbstractController
{
    private function getUserStats(ForumRepository $forumRepository, MessageRepository $messageRepository): array
    {
        // Compter TOUS les forums (frontoffice + backoffice)
        $allForums = $forumRepository->findAll();
        $forumsCount = count($allForums);
        
        // Compter TOUS les messages (frontoffice + backoffice)
        $allMessages = $messageRepository->findAll();
        $messagesCount = count($allMessages);
        
        // Debug pour vérifier les comptages exacts
        error_log('DEBUG: Total forums found: ' . $forumsCount);
        error_log('DEBUG: Total messages found: ' . $messagesCount);
        
        // Comptage alternatif pour validation
        $forumsCountAlt = $forumRepository->count([]);
        $messagesCountAlt = $messageRepository->count([]);
        
        error_log('DEBUG: Alternative count - Forums: ' . $forumsCountAlt . ', Messages: ' . $messagesCountAlt);

        return [
            'forums_count' => $forumsCount,
            'messages_count' => $messagesCount
        ];
    }

    private function cleanText(?string $text): string
    {
        if ($text === null || $text === '') {
            return '';
        }
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace([
            "\u{00A0}", "\u{202F}", "\u{2009}", "\u{2007}",
            "\u{2008}", "\u{205F}", "\u{3000}", "\u{200B}", "\u{FEFF}",
        ], ' ', $text);
        $text = preg_replace("/'\s+/u", "'", $text);
        $text = preg_replace("/\s+'/u", "'", $text);
        $text = preg_replace('/\s{2,}/u', ' ', $text);
        return trim($text);
    }

    // -------------------------------------------------------
    // PAGE D'ACCUEIL : affiche les CATÉGORIES
    // -------------------------------------------------------
    #[Route('/front/forum', name: 'app_front_forum')]
    public function index(
        Request $request,
        CategorieRepository $categorieRepository,
        ForumRepository $forumRepository,
        MessageRepository $messageRepository
    ): Response {
        // Statistiques pour la page principale
        $totalForums = $forumRepository->count([]);
        $totalMessages = $messageRepository->count([]);
        $activeForums = $forumRepository->count(['etat' => 'actif']);
        
        // Récupérer le terme de recherche
        $searchQuery = $request->query->get('search', '');
        
        // Initialiser les tableaux de résultats
        $forums = [];
        $categories = [];
        
        if (!empty($searchQuery)) {
            // Rechercher dans les forums
            $forumQueryBuilder = $forumRepository->createQueryBuilder('f')
                ->orderBy('f.dateCreation', 'DESC');
                
            $forumQueryBuilder->where('f.titre LIKE :searchQuery OR f.description LIKE :searchQuery')
                             ->setParameter('searchQuery', '%' . $searchQuery . '%');
            
            $forums = $forumQueryBuilder->getQuery()->getResult();
            
            // Rechercher dans les catégories (titre uniquement)
            $categoryQueryBuilder = $categorieRepository->createQueryBuilder('c')
                ->orderBy('c.titre', 'ASC');
                
            $categoryQueryBuilder->where('c.titre LIKE :searchQuery')
                                 ->setParameter('searchQuery', '%' . $searchQuery . '%');
            
            $categories = $categoryQueryBuilder->getQuery()->getResult();
        } else {
            // Si pas de recherche, afficher toutes les catégories
            $categories = $categorieRepository->findAll();
        }
        
        return $this->render('front_forum/index.html.twig', [
            'categories' => $categories,
            'allCategories' => $categorieRepository->findAll(),
            'forums' => $forums,
            'searchQuery' => $searchQuery,
            'totalForums' => $totalForums,
            'totalMessages' => $totalMessages,
            'activeForums' => $activeForums,
        ]);
    }

    // -------------------------------------------------------
    // AFFICHE UN FORUM (sujets + messages) avec toutes les catégories
    // -------------------------------------------------------
    #[Route('/front/forum/{id}', name: 'app_front_forum_show', methods: ['GET', 'POST'])]
    public function show(
        Forum $forum,
        Request $request,
        EntityManagerInterface $entityManager,
        CategorieRepository $categorieRepository,
        ForumRepository $forumRepository,
        MessageRepository $messageRepository
    ): Response {
        $userStats = $this->getUserStats($forumRepository, $messageRepository);
        
        $message = new Message();
        $message->setForum($forum);

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($this->getUser()) {
                    $message->setCreatedBy($this->cleanText($this->getUser()->getUsername()));
                } else {
                    $message->setCreatedBy($this->cleanText($message->getCreatedBy()));
                }
                $message->setContenu($this->cleanText($message->getContenu()));
                $message->setDatePublication(new \DateTimeImmutable());
                $message->setEtat('Actif');

                $entityManager->persist($message);
                $entityManager->flush();

                $this->addFlash('success', "Message publié avec succès !");
                return $this->redirectToRoute('app_front_forum_show', ['id' => $forum->getId()]);
            } else {
                // Debug: Ajouter les erreurs de validation aux flash messages
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                $this->addFlash('error', 'Erreurs de validation: ' . implode(', ', $errors));
            }
        }

        return $this->render('front_forum/show.html.twig', [
            'forum'      => $forum,
            'form'       => $form->createView(),
            'categories' => $categorieRepository->findAll(),
            'userStats'  => $userStats,
        ]);
    }

    // -------------------------------------------------------
    // NOUVEAU FORUM
    // -------------------------------------------------------
    #[Route('/front/forum/new', name: 'app_front_forum_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $forum = new Forum();
        $form = $this->createForm(ForumType::class, $forum);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$forum->getDateCreation()) {
                $forum->setDateCreation(new \DateTimeImmutable());
            }
            if (!$forum->getEtat()) {
                $forum->setEtat('Actif');
            }
            if ($this->getUser()) {
                $forum->setCreatedBy($this->getUser()->getUsername());
            } elseif (!$forum->getCreatedBy()) {
                $forum->setCreatedBy('Anonyme');
            }

            $entityManager->persist($forum);
            $entityManager->flush();

            $this->addFlash('success', 'Forum créé avec succès !');
            return $this->redirectToRoute('app_front_forum');
        }

        return $this->render('front_forum/new.html.twig', [
            'forum' => $forum,
            'form'  => $form->createView(),
        ]);
    }

    // -------------------------------------------------------
    // ÉDITER UN MESSAGE
    // -------------------------------------------------------
    #[Route('/front/forum/{forumId}/message/{id}/edit', name: 'app_front_forum_message_edit', methods: ['GET','POST'])]
    public function edit(int $forumId, Request $request, Message $message, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $createdBy = $request->request->get('createdBy');
            $contenu   = $request->request->get('contenu');

            if ($createdBy && $contenu) {
                $message->setCreatedBy($this->cleanText($createdBy));
                $message->setContenu($this->cleanText($contenu));
                $message->setDatePublication(new \DateTimeImmutable());
                $entityManager->flush();
                
                $this->addFlash('success', 'Message modifié avec succès !');
                return $this->redirectToRoute('app_front_forum_show', ['id' => $forumId]);
            }
        }

        return $this->render('front_forum/show.html.twig', [
            'forum' => $entityManager->getRepository(Forum::class)->find($forumId),
            'form'   => $this->createForm(MessageType::class)->createView(),
            'message' => $message,
        ]);
    }

    // -------------------------------------------------------
    // SUPPRIMER UN MESSAGE
    // -------------------------------------------------------
    #[Route('/front/forum/message/{id}/delete', name: 'app_front_forum_message_delete', methods: ['POST'])]
    public function deleteMessage(Message $message, Request $request, EntityManagerInterface $entityManager): Response
    {
        $forumId = $message->getForum()->getId();
        $entityManager->remove($message);
        $entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new Response('', 200);
        }

        $this->addFlash('success', 'Message supprimé avec succès !');
        return $this->redirectToRoute('app_front_forum_show', ['id' => $forumId]);
    }

    // -------------------------------------------------------
    // LIKE / DISLIKE
    // -------------------------------------------------------
    #[Route('/front/forum/message/{id}/like', name: 'app_front_forum_like', methods: ['POST'])]
    public function like(Message $message, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('success', 'Vous avez aimé ce message !');
        return $this->redirectToRoute('app_front_forum_show', ['id' => $message->getForum()->getId()]);
    }

    #[Route('/front/forum/message/{id}/dislike', name: 'app_front_forum_dislike', methods: ['POST'])]
    public function dislike(Message $message, EntityManagerInterface $entityManager): Response
    {
        $this->addFlash('info', "Vous n'avez pas aimé ce message.");
        return $this->redirectToRoute('app_front_forum_show', ['id' => $message->getForum()->getId()]);
    }

    // -------------------------------------------------------
    // MINI STATISTIQUES FRONT OFFICE
    // -------------------------------------------------------
    #[Route('/front/stats', name: 'app_front_stats')]
    public function stats(ForumRepository $forumRepository, MessageRepository $messageRepository): Response
    {
        // Statistiques simples pour le front office
        $totalForums = $forumRepository->count([]);
        $totalMessages = $messageRepository->count([]);
        $activeForums = $forumRepository->count(['etat' => 'actif']);
        
        // Top 3 forums les plus actifs
        $topForums = $messageRepository->getMessagesCountByForum(3);
        
        return $this->render('front_forum/stats.html.twig', [
            'totalForums' => $totalForums,
            'totalMessages' => $totalMessages,
            'activeForums' => $activeForums,
            'topForums' => $topForums,
        ]);
    }
}