<?php

namespace App\Controller;

use App\Service\ClusteringService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/back/clustering')]
class BackClusteringController extends AbstractController
{
    private ClusteringService $clusteringService;

    public function __construct(ClusteringService $clusteringService)
    {
        $this->clusteringService = $clusteringService;
    }

    #[Route('/', name: 'app_back_clustering_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $numberOfClusters = (int) $request->query->get('clusters', 5);
        
        try {
            // Effectuer le clustering
            $clusters = $this->clusteringService->clusterMessages($numberOfClusters);
            
            // Analyser les tendances temporelles
            $trends = $this->clusteringService->analyzeTemporalTrends($clusters);
            
            // Calculer les statistiques globales
            $stats = $this->calculateStats($clusters);
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du clustering: ' . $e->getMessage());
            $clusters = [];
            $trends = [];
            $stats = [];
        }

        return $this->render('clustering/back/index.html.twig', [
            'clusters' => $clusters,
            'trends' => $trends,
            'stats' => $stats,
            'current_clusters_count' => $numberOfClusters,
            'available_clusters' => range(2, 10)
        ]);
    }

    #[Route('/refresh', name: 'app_back_clustering_refresh', methods: ['GET'])]
    public function refresh(Request $request): Response
    {
        $numberOfClusters = (int) $request->query->get('clusters', 5);
        
        return $this->redirectToRoute('app_back_clustering_index', [
            'clusters' => $numberOfClusters
        ]);
    }

    #[Route('/cluster/{id}', name: 'app_back_clustering_detail', methods: ['GET'])]
    public function detail(int $id, Request $request): Response
    {
        try {
            $clusters = $this->clusteringService->clusterMessages(
                (int) $request->query->get('clusters', 5)
            );
            
            $cluster = null;
            foreach ($clusters as $c) {
                if ($c['id'] === $id) {
                    $cluster = $c;
                    break;
                }
            }
            
            if (!$cluster) {
                $this->addFlash('error', 'Cluster non trouvé');
                return $this->redirectToRoute('app_back_clustering_index');
            }
            
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors du chargement du cluster: ' . $e->getMessage());
            return $this->redirectToRoute('app_back_clustering_index');
        }

        return $this->render('clustering/back/detail.html.twig', [
            'cluster' => $cluster,
            'clusters_count' => (int) $request->query->get('clusters', 5)
        ]);
    }

    /**
     * Calcule les statistiques globales sur les clusters
     */
    private function calculateStats(array $clusters): array
    {
        if (empty($clusters)) {
            return [];
        }

        $totalMessages = 0;
        $clusterSizes = [];
        $allKeywords = [];

        foreach ($clusters as $cluster) {
            $messageCount = count($cluster['messages']);
            $totalMessages += $messageCount;
            $clusterSizes[] = $messageCount;
            $allKeywords = array_merge($allKeywords, $cluster['keywords']);
        }

        return [
            'total_clusters' => count($clusters),
            'total_messages' => $totalMessages,
            'avg_messages_per_cluster' => round($totalMessages / count($clusters), 2),
            'largest_cluster_size' => max($clusterSizes),
            'smallest_cluster_size' => min($clusterSizes),
            'top_keywords' => array_count_values($allKeywords)
        ];
    }
}
