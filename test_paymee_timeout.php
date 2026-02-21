<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpClient\HttpClient;

$client = HttpClient::create();
try {
    $response = $client->request('POST', 'https://sandbox.paymee.tn/api/v2/payments/create', [
        'json' => [
            'vendor' => 4297,
            'amount' => 10.0,
            'note' => 'Test',
            'return_url' => 'https://localhost/return',
            'cancel_url' => 'https://localhost/cancel',
            'webhook_url' => 'https://localhost/webhook',
            'first_name' => 'Flen',
            'last_name' => 'Fouleni',
            'email' => 'test@test.com',
            'phone' => '+21622222222',
            'order_id' => 'Ord-123',
            'timeout' => 86400
        ],
        'headers' => [
            'Authorization' => 'Token 9f3584b12f66b1231c050d11f5bc2e8063d11312',
            'Content-Type' => 'application/json',
        ],
    ]);
    echo json_encode(json_decode($response->getContent(false), true), JSON_PRETTY_PRINT);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
