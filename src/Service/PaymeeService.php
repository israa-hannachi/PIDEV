<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class PaymeeService
{
    private string $apiKey;
    private string $apiUrl;
    private string $vendorId;
    private HttpClientInterface $httpClient;

    public function __construct(string $apiKey, string $apiUrl, string $vendorId, HttpClientInterface $httpClient)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl;
        $this->vendorId = $vendorId;
        $this->httpClient = $httpClient;
    }

    public function createPayment(
        float $amount, 
        string $orderId, 
        string $firstName, 
        string $lastName, 
        string $email, 
        string $phone,
        string $callbackUrl,
        string $note = 'Inscription Événement Naja7ni'
    ): array {
        $callbackUrl = str_replace('http://', 'https://', $callbackUrl);

        $response = $this->httpClient->request('POST', "{$this->apiUrl}/payments/create", [
            'json' => [
                'vendor' => $this->vendorId,
                'amount' => $amount,
                'note' => $note,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'order_id' => $orderId,
                'webhook_url' => $callbackUrl, // In V2 callback is often webhook_url
                'return_url' => $callbackUrl,
                'cancel_url' => $callbackUrl,
                'timeout' => 86400, // Important: 24h token validity to avoid immediate "Expired" messages during tests
            ],
            'headers' => [
                'Authorization' => "Token {$this->apiKey}",
                'Content-Type' => 'application/json',
            ],
        ]);

        return $response->toArray();
    }

    public function checkPaymentStatus(string $transactionId): array
    {
        $response = $this->httpClient->request('GET', "{$this->apiUrl}/payments/{$transactionId}/check", [
            'headers' => [
                'Authorization' => "Token {$this->apiKey}",
            ],
        ]);

        return $response->toArray();
    }
}
