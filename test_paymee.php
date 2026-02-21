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
            'phone' => '+21653435158',
            'order_id' => 'Ord-123'
        ],
        'headers' => [
            'Authorization' => 'Token 9f3584b12f66b1231c050d11f5bc2e8063d11312',
            'Content-Type' => 'application/json',
        ],
    ]);
    
    $data = json_decode($response->getContent(), true);
    if (!isset($data['data']['token'])) {
        die("No token received");
    }
    $token = $data['data']['token'];
    
    $res1 = $client->request('GET', "https://sandbox.paymee.tn/api/v2/payments/$token/check", [
        'headers' => [
            'Authorization' => 'Token 9f3584b12f66b1231c050d11f5bc2e8063d11312',
        ],
    ]);
    file_put_contents('out1.json', json_encode(json_decode($res1->getContent(false), true), JSON_PRETTY_PRINT));

    $res2 = $client->request('GET', "https://sandbox.paymee.tn/api/v2/payments/status/$token", [
        'headers' => [
            'Authorization' => 'Token 9f3584b12f66b1231c050d11f5bc2e8063d11312',
        ],
    ]);
    file_put_contents('out2.json', json_encode(json_decode($res2->getContent(false), true), JSON_PRETTY_PRINT));

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
