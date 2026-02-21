<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Entity\Cours;
use App\Entity\Module;
use App\Service\CoursContentExtractor;
use App\Service\OpenAiSummaryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AiSummaryController extends AbstractController
{
    #[Route('/ai/summary', name: 'app_ai_summary', methods: ['POST'])]
    public function summary(Request $request, EntityManagerInterface $entityManager, OpenAiSummaryService $openAi): JsonResponse
    {
        // Ollama peut être lent selon la machine/le modèle; éviter max_execution_time (FatalError)
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $payload = $request->toArray(false);

        $token = isset($payload['_token']) && is_string($payload['_token']) ? $payload['_token'] : '';
        if (!$this->isCsrfTokenValid('ai_summary', $token)) {
            return new JsonResponse(['error' => 'CSRF token invalide.'], 403);
        }

        $contenu = isset($payload['contenu']) && is_string($payload['contenu']) ? trim($payload['contenu']) : '';
        if ($contenu === '') {
            return new JsonResponse(['error' => 'Contenu manquant.'], 400);
        }

        $moduleId = isset($payload['moduleId']) ? (int) $payload['moduleId'] : 0;
        $niveau = null;
        if ($moduleId > 0) {
            $module = $entityManager->find(Module::class, $moduleId);
            if ($module) {
                $niveau = $module->getNiveau();
            }
        }

        try {
            $resume = $openAi->summarize($contenu, $niveau);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(['summary' => $resume]);
    }

    #[Route('/ai/summary/cours/{id}', name: 'app_ai_summary_cours', methods: ['POST'])]
    public function summaryForCours(Request $request, Cours $cour, CoursContentExtractor $extractor, OpenAiSummaryService $openAi): JsonResponse
    {
        // Ollama peut être lent selon la machine/le modèle; éviter max_execution_time (FatalError)
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $payload = $request->toArray(false);

        $token = isset($payload['_token']) && is_string($payload['_token']) ? $payload['_token'] : '';
        if (!$this->isCsrfTokenValid('ai_summary_cours' . $cour->getId(), $token)) {
            return new JsonResponse(['error' => 'CSRF token invalide.'], 403);
        }

        try {
            $contenu = $extractor->extractText($cour);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        $niveau = null;
        if ($cour->getModule()) {
            $niveau = $cour->getModule()->getNiveau();
        }

        try {
            $resume = $openAi->summarize($contenu, $niveau);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }

        return new JsonResponse(['summary' => $resume]);
    }

    #[Route('/ai/summary/cours/{id}/pdf', name: 'app_ai_summary_cours_pdf', methods: ['POST'])]
    public function downloadSummaryPdf(Request $request, Cours $cour): Response
    {
        @set_time_limit(300);
        @ini_set('max_execution_time', '300');

        $payload = $request->toArray(false);

        $token = isset($payload['_token']) && is_string($payload['_token']) ? $payload['_token'] : '';
        if (!$this->isCsrfTokenValid('ai_summary_cours_pdf' . $cour->getId(), $token)) {
            return new JsonResponse(['error' => 'CSRF token invalide.'], 403);
        }

        $text = isset($payload['text']) && is_string($payload['text']) ? trim($payload['text']) : '';
        if ($text === '') {
            return new JsonResponse(['error' => 'Texte manquant.'], 400);
        }

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        $titre = htmlspecialchars((string) ($cour->getTitre() ?? 'Résumé'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $body = nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $html = '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif;font-size:12px;}h1{font-size:18px;margin:0 0 12px 0;} .meta{color:#666;margin-bottom:12px;}</style></head><body>'
            . '<h1>' . $titre . '</h1>'
            . '<div class="meta">Généré et modifié par l\'étudiant</div>'
            . '<div>' . $body . '</div>'
            . '</body></html>';

        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $output = $dompdf->output();

        $filename = 'resume-cours-' . $cour->getId() . '.pdf';

        return new Response($output, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
