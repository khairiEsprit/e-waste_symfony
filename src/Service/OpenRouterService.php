<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OpenRouterService
{
    private $httpClient;
    private $params;
    private string $apiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;
        // Fetch API URL from environment variable via ParameterBag
        $this->apiUrl = $this->params->get('openrouter_api_url');
    }

    public function generateResponse(string $text, ?string $imageUrl = null): array
    {
        $messages = [];
        $content = [];

        // Add text content
        $content[] = [
            'type' => 'text',
            'text' => $text
        ];

        // Add image if provided
        if ($imageUrl) {
            $content[] = [
                'type' => 'image_url',
                'image_url' => ['url' => $imageUrl]
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $content
        ];

        $response = $this->httpClient->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->params->get('openrouter_api_key'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'openrouter/quasar-alpha',
                'messages' => $messages
            ]
        ]);

        return $response->toArray();
    }
}