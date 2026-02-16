<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

class CloudinaryUploader
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $cloudName,
        private string $apiKey,
        private string $apiSecret,
        private string $folder,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $cloudName = trim($this->cloudName);
        $apiKey = trim($this->apiKey);
        $apiSecret = trim($this->apiSecret);

        if ($cloudName == '' || $apiKey == '' || $apiSecret == '') {
            throw new \RuntimeException('Cloudinary n\'est pas configuré (CLOUDINARY_CLOUD_NAME / CLOUDINARY_API_KEY / CLOUDINARY_API_SECRET).');
        }

        $timestamp = time();
        $folder = trim($this->folder);

        $mimeType = (string) ($file->getMimeType() ?? '');
        $resourceType = str_starts_with($mimeType, 'image/') ? 'image' : 'raw';

        $originalName = (string) $file->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $extension = strtolower((string) pathinfo($originalName, PATHINFO_EXTENSION));
        if ($extension === '') {
            $guessed = (string) ($file->guessExtension() ?? '');
            $extension = strtolower($guessed);
        }

        $slugger = new AsciiSlugger();
        $safeBaseName = (string) $slugger->slug($baseName !== '' ? $baseName : 'fichier');
        $publicId = $safeBaseName . '-' . uniqid();

        $accessMode = 'public';
        $deliveryType = 'upload';

        $paramsToSign = [
            'access_mode' => $accessMode,
            'folder' => $folder,
            'format' => $extension !== '' ? $extension : '',
            'public_id' => $publicId,
            'timestamp' => (string) $timestamp,
            'type' => $deliveryType,
        ];

        $signature = $this->sign($paramsToSign, $apiSecret);

        $endpoint = sprintf('https://api.cloudinary.com/v1_1/%s/%s/upload', rawurlencode($cloudName), $resourceType);

        $response = $this->httpClient->request('POST', $endpoint, [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'body' => [
                'file' => fopen($file->getRealPath(), 'r'),
                'api_key' => $apiKey,
                'timestamp' => (string) $timestamp,
                'folder' => $folder,
                'public_id' => $publicId,
                'format' => $extension !== '' ? $extension : null,
                'access_mode' => $accessMode,
                'type' => $deliveryType,
                'signature' => $signature,
            ],
        ]);

        $status = $response->getStatusCode();
        $data = $response->toArray(false);

        if ($status < 200 || $status >= 300) {
            $message = is_array($data) ? json_encode($data) : (string) $response->getContent(false);
            throw new \RuntimeException('Erreur Cloudinary: ' . $message);
        }

        if (!is_array($data) || !isset($data['secure_url']) || !is_string($data['secure_url'])) {
            throw new \RuntimeException('Réponse Cloudinary invalide: secure_url manquant.');
        }

        $returnedType = isset($data['type']) && is_string($data['type']) ? $data['type'] : null;
        $returnedAccessMode = isset($data['access_mode']) && is_string($data['access_mode']) ? $data['access_mode'] : null;
        $returnedResourceType = isset($data['resource_type']) && is_string($data['resource_type']) ? $data['resource_type'] : null;

        if (($returnedType !== null && $returnedType !== 'upload') || ($returnedAccessMode !== null && $returnedAccessMode !== 'public')) {
            $debug = [
                'type' => $returnedType,
                'access_mode' => $returnedAccessMode,
                'resource_type' => $returnedResourceType,
                'public_id' => isset($data['public_id']) && is_string($data['public_id']) ? $data['public_id'] : null,
                'secure_url' => $data['secure_url'],
            ];

            throw new \RuntimeException('Cloudinary a retourné un asset non public (probable preset/setting côté Cloudinary). Détails: ' . json_encode($debug));
        }

        return $data['secure_url'];
    }

    /**
     * @param array<string,string> $params
     */
    private function sign(array $params, string $apiSecret): string
    {
        ksort($params);
        $pairs = [];
        foreach ($params as $key => $value) {
            if ($value === '') {
                continue;
            }
            $pairs[] = $key . '=' . $value;
        }
        $toSign = implode('&', $pairs) . $apiSecret;

        return sha1($toSign);
    }
}
