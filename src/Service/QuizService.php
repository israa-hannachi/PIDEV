<?php
// src/Service/QuizService.php
namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class QuizService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

   // Générer les questions (toujours en anglais)
// Générer les questions (toujours en anglais)
public function getQuestions(
    int $amount = 10,
    int $category = null,
    string $difficulty = null,
    string $type = 'multiple'
): array {
    $url = 'https://opentdb.com/api.php?amount=' . $amount . '&type=' . $type;

    if ($category) {
        $url .= '&category=' . $category;
    }
    if ($difficulty) {
        $url .= '&difficulty=' . $difficulty;
    }

    $response = $this->client->request('GET', $url);
    return $response->toArray(); // toujours en anglais
}


}


