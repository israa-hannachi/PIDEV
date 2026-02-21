<?php

namespace App\Service;

use App\Entity\Cours;
use Smalot\PdfParser\Parser as PdfParser;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CoursContentExtractor
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function extractText(Cours $cours): string
    {
        $contenu = trim((string) ($cours->getContenu() ?? ''));
        if ($contenu !== '') {
            return $this->normalizeText($contenu);
        }

        $fichier = trim((string) ($cours->getFichierContenu() ?? ''));
        if ($fichier === '') {
            throw new \RuntimeException('Aucun contenu texte ni fichier pour générer un résumé.');
        }

        $isRemote = str_starts_with($fichier, 'http://') || str_starts_with($fichier, 'https://');
        $source = $fichier;

        $tmpPath = null;
        try {
            if ($isRemote) {
                $tmpPath = $this->downloadToTemp($source);
                $path = $tmpPath;
            } else {
                $path = $this->resolveLocalPath($source);
            }

            $ext = '';
            if ($isRemote) {
                $ext = $this->guessExtensionFromUrl($source);
            }
            if ($ext === '') {
                $ext = strtolower((string) pathinfo(parse_url($path, PHP_URL_PATH) ?? $path, PATHINFO_EXTENSION));
            }
            if ($ext === '') {
                $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
            }

            $text = match ($ext) {
                'txt' => $this->extractTxt($path),
                'pdf' => $this->extractPdf($path),
                'docx' => $this->extractDocx($path),
                'pptx' => $this->extractPptx($path),
                default => throw new \RuntimeException('Format de fichier non supporté pour résumé IA: ' . ($ext ?: 'inconnu')),
            };

            $text = $this->normalizeText($text);
            if ($text === '') {
                throw new \RuntimeException('Impossible d\'extraire du texte depuis le fichier.');
            }

            // avoid sending too much text
            if (mb_strlen($text) > 12000) {
                $text = mb_substr($text, 0, 12000);
            }

            return $text;
        } finally {
            if ($tmpPath && is_file($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    private function resolveLocalPath(string $filename): string
    {
        $candidates = [
            // legacy local upload
            dirname(__DIR__, 2) . '/public/uploads/cours_fichiers/' . $filename,
            // in case filename already includes path
            dirname(__DIR__, 2) . '/public/' . ltrim($filename, '/'),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new \RuntimeException('Fichier introuvable sur le serveur.');
    }

    private function downloadToTemp(string $url): string
    {
        $response = $this->httpClient->request('GET', $url);
        $status = $response->getStatusCode();
        if ($status < 200 || $status >= 300) {
            throw new \RuntimeException('Téléchargement du fichier impossible (HTTP ' . $status . ').');
        }

        $content = $response->getContent();
        $tmp = tempnam(sys_get_temp_dir(), 'cours_');
        if ($tmp === false) {
            throw new \RuntimeException('Impossible de créer un fichier temporaire.');
        }

        $ext = $this->guessExtensionFromUrl($url);
        if ($ext === '') {
            $ext = $this->guessExtensionFromContentType($response->getHeaders(false));
        }

        if ($ext !== '') {
            $tmpWithExt = $tmp . '.' . $ext;
            @rename($tmp, $tmpWithExt);
            $tmp = $tmpWithExt;
        }

        file_put_contents($tmp, $content);
        return $tmp;
    }

    private function guessExtensionFromUrl(string $url): string
    {
        $path = (string) (parse_url($url, PHP_URL_PATH) ?? '');
        $ext = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));
        return $ext;
    }

    private function guessExtensionFromContentType(array $headers): string
    {
        $contentType = null;
        foreach (['content-type', 'Content-Type'] as $key) {
            if (isset($headers[$key][0]) && is_string($headers[$key][0])) {
                $contentType = $headers[$key][0];
                break;
            }
        }
        $contentType = strtolower(trim((string) $contentType));
        if ($contentType === '') {
            return '';
        }

        return match (true) {
            str_contains($contentType, 'application/pdf') => 'pdf',
            str_contains($contentType, 'text/plain') => 'txt',
            str_contains($contentType, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') => 'docx',
            str_contains($contentType, 'application/vnd.openxmlformats-officedocument.presentationml.presentation') => 'pptx',
            default => '',
        };
    }

    private function extractTxt(string $path): string
    {
        $content = @file_get_contents($path);
        return is_string($content) ? $content : '';
    }

    private function extractPdf(string $path): string
    {
        $parser = new PdfParser();
        $pdf = $parser->parseFile($path);
        return (string) $pdf->getText();
    }

    private function extractDocx(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('DOCX: impossible d\'ouvrir le fichier.');
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!is_string($xml) || trim($xml) === '') {
            return '';
        }

        return $this->xmlToText($xml);
    }

    private function extractPptx(string $path): string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) {
            throw new \RuntimeException('PPTX: impossible d\'ouvrir le fichier.');
        }

        $texts = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['name']) || !is_string($stat['name'])) {
                continue;
            }
            $name = $stat['name'];
            if (str_starts_with($name, 'ppt/slides/slide') && str_ends_with($name, '.xml')) {
                $xml = $zip->getFromName($name);
                if (is_string($xml) && trim($xml) !== '') {
                    $texts[] = $this->xmlToText($xml);
                }
            }
        }
        $zip->close();

        return implode("\n", $texts);
    }

    private function xmlToText(string $xml): string
    {
        // Strip tags fast + decode entities
        $text = strip_tags($xml);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5);
        return $text;
    }

    private function normalizeText(string $text): string
    {
        $text = html_entity_decode(strip_tags($text));
        $text = preg_replace('/\s+/', ' ', (string) $text);
        return trim((string) $text);
    }
}
