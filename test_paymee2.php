<?php
require 'vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpClient\HttpClient;
use App\Service\PaymeeService;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

$httpClient = HttpClient::create();
$service = new PaymeeService($_ENV['PAYMEE_API_KEY'], $_ENV['PAYMEE_API_URL'], $_ENV['PAYMEE_VENDOR_ID'], $httpClient);

try {
    $response = $service->checkPaymentStatus('9d554a72d7f8d5c1e46e03df0fbd9e23');
    print_r($response);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
