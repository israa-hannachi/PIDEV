<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OpenAiSummaryService
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $ollamaBaseUrl,
        private readonly string $ollamaModel,
        private readonly int $ollamaTimeoutSeconds,
    ) {
    }

    public function summarize(string $contenu, ?string $niveau): string
    {
        $baseUrl = rtrim(trim($this->ollamaBaseUrl), '/');
        if ($baseUrl === '') {
            throw new \RuntimeException('Ollama n\'est pas configuré (OLLAMA_BASE_URL).');
        }

        $model = trim($this->ollamaModel);
        if ($model === '') {
            throw new \RuntimeException('Ollama n\'est pas configuré (OLLAMA_MODEL).');
        }

        $timeout = $this->ollamaTimeoutSeconds > 0 ? $this->ollamaTimeoutSeconds : 600;

        $niveauText = trim((string) $niveau);
        if ($niveauText === '') {
            $niveauText = 'Intermédiaire';
        }

        $system = 'Tu es un assistant pédagogique. Tu génères un résumé clair et structuré.';
        $contenu = trim($contenu);
        if (mb_strlen($contenu) > 5000) {
            $contenu = mb_substr($contenu, 0, 5000);
        }

        $user = "Résume le cours ci-dessous en français en adaptant le niveau au public: {$niveauText}.\n\nContraintes:\n- 6 à 10 lignes maximum\n- Ajoute 4 points clés en bullet points\n- Termine par 2 questions de révision\n\nContenu du cours:\n" . $contenu;

        try {
            $response = $this->httpClient->request('POST', $baseUrl . '/api/chat', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'timeout' => $timeout,
                'json' => [
                    'model' => $model,
                    'stream' => false,
                    'messages' => [
                        ['role' => 'system', 'content' => $system],
                        ['role' => 'user', 'content' => $user],
                    ],
                    'options' => [
                        'temperature' => 0.4,
                        'num_predict' => 350,
                    ],
                ],
            ]);

            $status = $response->getStatusCode();
            $data = $response->toArray(false);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Ollama ne répond pas à temps. Vérifie qu\'Ollama est lancé et essaie un modèle plus léger (ex: OLLAMA_MODEL=llama3.2:1b) ou augmente les ressources.');
        }

        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? json_encode($data) : (string) $response->getContent(false);
            throw new \RuntimeException('Erreur Ollama: ' . $message);
        }

        $content = null;
        if (is_array($data)
            && isset($data['message'])
            && is_array($data['message'])
            && isset($data['message']['content'])
            && is_string($data['message']['content'])
        ) {
            $content = $data['message']['content'];
        }

        $content = trim((string) $content);
        if ($content === '') {
            throw new \RuntimeException('Réponse Ollama invalide (contenu manquant).');
        }

        return $content;
    }
}
