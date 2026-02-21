<?php

require __DIR__ . '/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AITest extends KernelTestCase
{
    public function testAi()
    {
        self::bootKernel();
        $container = static::getContainer();
        $aiService = $container->get(\App\Service\AIService::class);
        
        $desc = "Rejoignez notre grand hackathon IA de 2 jours à Paris. C'est gratuit et ouvert aux développeurs de niveau intermédiaire. Places limitées à 100 personnes. Début à 9h du matin.";
        $result = $aiService->improveEventMetadata($desc);
        
        print_r($result);
    }
}

$test = new AITest();
$test->testAi();
