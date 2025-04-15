<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OpenRouterService
{
    private $httpClient;
    private $params;
    private string $apiUrl;
    private string $apiKey;

    public function __construct(
        HttpClientInterface $httpClient,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->params = $params;

        // Fetch API URL and key with better error handling
        $this->apiUrl = 'https://openrouter.ai/api/v1/chat/completions'; // Hardcoded correct URL
        $this->apiKey = $this->params->get('openrouter_api_key');

        // Validate essential configuration
        if (empty($this->apiUrl) || empty($this->apiKey)) {
            throw new \RuntimeException('OpenRouter API configuration is missing. Please check your environment variables.');
        }
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

        // Try multiple models in case one fails
        $models = [
            'openai/gpt-4.1',
            'qwen/qwen2.5-coder-7b-instruct',

        ];

        $lastException = null;

        // Try each model until one works
        foreach ($models as $model) {
            $requestPayload = [
                'model' => $model,
                'messages' => $messages
            ];

            try {
                // Log the request for debugging
                error_log('OpenRouter API Request with model ' . $model . ': ' . json_encode($requestPayload));

                $response = $this->httpClient->request('POST', $this->apiUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->apiKey,
                        'Content-Type' => 'application/json',
                        'HTTP-Referer' => 'https://e-waste-management.com', // Add referer for API tracking
                    ],
                    'json' => $requestPayload,
                    'timeout' => 30, // Add a reasonable timeout
                ]);

                $statusCode = $response->getStatusCode();
                $content = $response->getContent(false); // Get raw content

                // Log the response for debugging
                error_log("OpenRouter API Response Status: $statusCode");
                error_log("OpenRouter API Response Content: $content");

                if ($statusCode !== 200) {
                    throw new \Exception("API returned non-200 status code: $statusCode with message: $content");
                }

                $responseData = $response->toArray();

                // Validate response structure
                if (!isset($responseData['choices']) || !isset($responseData['choices'][0]['message']['content'])) {
                    throw new \Exception('Invalid response format from API: ' . json_encode($responseData));
                }

                return $responseData;
            } catch (\Exception $e) {
                error_log('OpenRouter API Error with model ' . $model . ': ' . $e->getMessage());
                $lastException = $e;
                // Continue to the next model
            }
        }

        // If we've tried all models and none worked, throw the last exception
        if ($lastException) {
            throw $lastException;
        } else {
            throw new \Exception('All API models failed to respond');
        }
    }
}
