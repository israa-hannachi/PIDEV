<?php

namespace App\Controller;

use App\Repository\ForumRepository;
use App\Repository\MessageRepository;
use App\Repository\CategorieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/stats')]
class BackStatsController extends AbstractController
{
    #[Route('/', name: 'app_back_stats_index', methods: ['GET'])]
    public function index(ForumRepository $forumRepository, MessageRepository $messageRepository, CategorieRepository $categorieRepository): Response
    {
        // Statistiques des forums
        $totalForums = $forumRepository->count([]);
        $activeForums = $forumRepository->count(['etat' => 'actif']);
        $inactiveForums = $totalForums - $activeForums;
        
        // Statistiques des messages
        $totalMessages = $messageRepository->count([]);
        
        // Forums par catégorie
        $forumsByCategory = [];
        $categories = $categorieRepository->findAll();
        foreach ($categories as $category) {
            $count = $forumRepository->count(['categorie' => $category]);
            $forumsByCategory[] = [
                'category' => $category->getTitre(),
                'count' => $count
            ];
        }
        
        // Messages par forum (top 5)
        $messagesByForum = $messageRepository->getMessagesCountByForum(5);
        
        return $this->render('forum/Back/stats/index.html.twig', [
            'totalForums' => $totalForums,
            'activeForums' => $activeForums,
            'inactiveForums' => $inactiveForums,
            'totalMessages' => $totalMessages,
            'forumsByCategory' => $forumsByCategory,
            'messagesByForum' => $messagesByForum,
        ]);
    }
}
