<?php
use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$container = $kernel->getContainer();
$paymeeService = $container->get(\App\Service\PaymeeService::class);

try {
    echo "Creating payment test...\n";
    $result = $paymeeService->createPayment(
        10.50,
        'TND',
        'test_order_xyz',
        'http://localhost:8000/payment/test_order_xyz/success',
        'http://localhost:8000/payment/test_order_xyz/cancel'
    );
    
    echo "Paymee Response:\n";
    print_r($result);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
