<?php

namespace App\Service;

use App\Repository\MessageRepository;
use App\Repository\ForumRepository;

class ClusteringService
{
    private MessageRepository $messageRepository;
    private ForumRepository $forumRepository;

    public function __construct(
        MessageRepository $messageRepository,
        ForumRepository $forumRepository
    ) {
        $this->messageRepository = $messageRepository;
        $this->forumRepository = $forumRepository;
    }

    /**
     * Effectue le clustering des messages du forum
     * Retourne les clusters avec leurs messages associés
     */
    public function clusterMessages(int $numberOfClusters = 5): array
    {
        // Récupérer tous les messages actifs
        $messages = $this->messageRepository->findBy(['etat' => 'Actif']);
        
        if (empty($messages)) {
            return [];
        }

        // Prétraitement des textes
        $processedMessages = [];
        foreach ($messages as $message) {
            $processedMessages[] = [
                'id' => $message->getId(),
                'content' => $this->preprocessText($message->getContenu()),
                'forum_id' => $message->getForum()->getId(),
                'forum_title' => $message->getForum()->getTitre(),
                'author' => $message->getCreatedBy(),
                'date' => $message->getDatePublication()->format('Y-m-d H:i:s')
            ];
        }

        // Extraire les caractéristiques (TF-IDF simplifié)
        $features = $this->extractFeatures($processedMessages);

        // Appliquer K-means clustering
        $clusters = $this->kMeansClustering($features, $numberOfClusters);

        // Organiser les résultats
        return $this->organizeClusters($clusters, $processedMessages);
    }

    /**
     * Prétraite le texte : nettoyage et tokenisation
     */
    private function preprocessText(string $text): string
    {
        // Convertir en minuscules
        $text = strtolower($text);
        
        // Supprimer les balises HTML
        $text = strip_tags($text);
        
        // Supprimer la ponctuation
        $text = preg_replace('/[^\w\s]/', ' ', $text);
        
        // Supprimer les mots courants (stop words)
        $stopWords = ['le', 'la', 'les', 'de', 'des', 'du', 'un', 'une', 'et', 'est', 'dans', 'pour', 'avec', 'par', 'sur', 'ce', 'cette', 'ces', 'il', 'elle', 'ils', 'elles', 'nous', 'vous', 'leur', 'leurs', 'son', 'sa', 'ses', 'mon', 'ma', 'mes', 'ton', 'ta', 'tes', 'on', 'y', 'en', 'a', 'à', 'se', 'ne', 'pas', 'plus', 'moins', 'très', 'bien', 'aussi', 'comme', 'si', 'ou', 'où', 'quand', 'que', 'qui', 'quoi', 'dont', 'mais', 'ou', 'est', 'donc', 'or', 'ni', 'car'];
        
        $words = explode(' ', $text);
        $filteredWords = [];
        
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) > 2 && !in_array($word, $stopWords)) {
                $filteredWords[] = $word;
            }
        }
        
        return implode(' ', $filteredWords);
    }

    /**
     * Extrait les caractéristiques TF-IDF simplifiées
     */
    private function extractFeatures(array $messages): array
    {
        $allWords = [];
        $documentWords = [];
        
        // Compter les mots dans chaque document
        foreach ($messages as $index => $message) {
            $words = explode(' ', $message['content']);
            $documentWords[$index] = array_count_values($words);
            $allWords = array_merge($allWords, $words);
        }
        
        // Calculer TF-IDF
        $allWords = array_unique($allWords);
        $totalDocs = count($messages);
        $features = [];
        
        foreach ($messages as $docIndex => $message) {
            $featureVector = [];
            
            foreach ($allWords as $word) {
                $tf = $documentWords[$docIndex][$word] ?? 0;
                
                // Calculer DF (Document Frequency)
                $df = 0;
                foreach ($documentWords as $docWords) {
                    if (isset($docWords[$word])) {
                        $df++;
                    }
                }
                
                // Calculer IDF
                $idf = $df > 0 ? log($totalDocs / $df) : 0;
                
                // TF-IDF
                $featureVector[] = $tf * $idf;
            }
            
            $features[] = $featureVector;
        }
        
        return $features;
    }

    /**
     * Algorithme K-means clustering
     */
    private function kMeansClustering(array $features, int $k): array
    {
        $n = count($features);
        if ($n <= $k) {
            // Si moins de documents que de clusters, assigner chaque document à un cluster
            return range(0, $n - 1);
        }

        // Initialiser les centroïdes aléatoirement
        $centroids = [];
        $indices = array_rand($features, $k);
        foreach ($indices as $index) {
            $centroids[] = $features[$index];
        }

        $clusters = [];
        $maxIterations = 100;
        $iteration = 0;

        while ($iteration < $maxIterations) {
            // Assigner chaque point au cluster le plus proche
            $newClusters = [];
            foreach ($features as $feature) {
                $minDistance = PHP_FLOAT_MAX;
                $closestCluster = 0;
                
                foreach ($centroids as $clusterIndex => $centroid) {
                    $distance = $this->euclideanDistance($feature, $centroid);
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $closestCluster = $clusterIndex;
                    }
                }
                
                $newClusters[] = $closestCluster;
            }

            // Vérifier la convergence
            if ($newClusters === $clusters) {
                break;
            }
            
            $clusters = $newClusters;

            // Recalculer les centroïdes
            $centroids = $this->recalculateCentroids($features, $clusters, $k);
            
            $iteration++;
        }

        return $clusters;
    }

    /**
     * Calcule la distance euclidienne entre deux vecteurs
     */
    private function euclideanDistance(array $a, array $b): float
    {
        $sum = 0;
        $length = min(count($a), count($b));
        
        for ($i = 0; $i < $length; $i++) {
            $sum += pow($a[$i] - $b[$i], 2);
        }
        
        return sqrt($sum);
    }

    /**
     * Recalcule les centroïdes des clusters
     */
    private function recalculateCentroids(array $features, array $clusters, int $k): array
    {
        $centroids = [];
        $clusterPoints = [];
        
        // Regrouper les points par cluster
        foreach ($clusters as $pointIndex => $clusterIndex) {
            $clusterPoints[$clusterIndex][] = $features[$pointIndex];
        }
        
        // Calculer la moyenne pour chaque cluster
        for ($i = 0; $i < $k; $i++) {
            if (!isset($clusterPoints[$i])) {
                // Si le cluster est vide, garder l'ancien centroïde ou en créer un nouveau
                $centroids[] = $features[array_rand($features)];
                continue;
            }
            
            $points = $clusterPoints[$i];
            $dimension = count($points[0]);
            $centroid = array_fill(0, $dimension, 0);
            
            foreach ($points as $point) {
                for ($j = 0; $j < $dimension; $j++) {
                    $centroid[$j] += $point[$j];
                }
            }
            
            for ($j = 0; $j < $dimension; $j++) {
                $centroid[$j] /= count($points);
            }
            
            $centroids[] = $centroid;
        }
        
        return $centroids;
    }

    /**
     * Organise les clusters pour l'affichage
     */
    private function organizeClusters(array $clusters, array $messages): array
    {
        $organizedClusters = [];
        
        foreach ($clusters as $messageIndex => $clusterIndex) {
            if (!isset($organizedClusters[$clusterIndex])) {
                $organizedClusters[$clusterIndex] = [
                    'id' => $clusterIndex,
                    'name' => 'Cluster ' . ($clusterIndex + 1),
                    'messages' => [],
                    'keywords' => []
                ];
            }
            
            $organizedClusters[$clusterIndex]['messages'][] = $messages[$messageIndex];
        }

        // Extraire les mots-clés pour chaque cluster
        foreach ($organizedClusters as $clusterIndex => &$cluster) {
            $cluster['keywords'] = $this->extractClusterKeywords($cluster['messages']);
            $cluster['message_count'] = count($cluster['messages']);
        }

        return array_values($organizedClusters);
    }

    /**
     * Extrait les mots-clés les plus fréquents d'un cluster
     */
    private function extractClusterKeywords(array $messages): array
    {
        $allWords = [];
        
        foreach ($messages as $message) {
            $words = explode(' ', $message['content']);
            $allWords = array_merge($allWords, $words);
        }
        
        $wordCounts = array_count_values($allWords);
        arsort($wordCounts);
        
        return array_slice(array_keys($wordCounts), 0, 10);
    }

    /**
     * Analyse les tendances temporelles des clusters
     */
    public function analyzeTemporalTrends(array $clusters): array
    {
        $trends = [];
        
        foreach ($clusters as $cluster) {
            $monthlyCounts = [];
            
            foreach ($cluster['messages'] as $message) {
                $date = new \DateTime($message['date']);
                $monthKey = $date->format('Y-m');
                
                if (!isset($monthlyCounts[$monthKey])) {
                    $monthlyCounts[$monthKey] = 0;
                }
                
                $monthlyCounts[$monthKey]++;
            }
            
            // S'assurer qu'on a au moins 6 mois de données
            $now = new \DateTime();
            $sixMonthsAgo = (clone $now)->modify('-6 months');
            
            // Remplir les mois manquants avec 0
            $current = clone $sixMonthsAgo;
            while ($current <= $now) {
                $monthKey = $current->format('Y-m');
                if (!isset($monthlyCounts[$monthKey])) {
                    $monthlyCounts[$monthKey] = 0;
                }
                $current->modify('+1 month');
            }
            
            ksort($monthlyCounts);
            
            $trends[] = [
                'cluster_id' => $cluster['id'],
                'cluster_name' => $cluster['name'],
                'monthly_counts' => $monthlyCounts,
                'total_messages' => count($cluster['messages'])
            ];
        }
        
        return $trends;
    }
}
