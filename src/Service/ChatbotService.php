<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotService
{
    private $httpClient;
    private $apiToken;
    private const MODEL = 'facebook/blenderbot-3B';
    private const API_URL = 'https://api-inference.huggingface.co/models/';

    public function __construct(HttpClientInterface $httpClient, string $apiToken)
    {
        $this->httpClient = $httpClient;
        $this->apiToken = $apiToken;
    }

    /**
     * Gets an educational response about waste management from the BlenderBot model.
     *
     * @param string $userMessage The user's input message
     * @param array $context Additional context like location or language (currently unused)
     * @return string The chatbot's educational response
     */
    public function getEducationalResponse(string $userMessage, array $context = []): string
    {
        try {
            // Refactored prompt for stricter educational focus
            $prompt = "You are a waste management expert. Based on the following user input, provide a concise, factual, and educational response about waste management. Focus on useful information and avoid casual conversation: ";
            $inputText = $prompt . $userMessage;

            $response = $this->httpClient->request('POST', self::API_URL . self::MODEL, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apiToken}",
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'inputs' => $inputText,
                    'parameters' => [
                        'max_length' => 150,
                        'temperature' => 0.6, // Lowered further for more focus
                    ],
                ],
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false); // Get raw content even on error
            error_log("Status: $statusCode, Response: $content"); // Log for debugging

            if ($statusCode !== 200) {
                return "Error: HTTP $statusCode - $content";
            }

            $data = $response->toArray();
            $botResponse = $data[0]['generated_text'] ?? 'Sorry, I couldn’t generate a response.';
            
            // Clean the response by removing the prompt and user input
            $cleanResponse = str_replace([$prompt, $userMessage], '', $botResponse);
            return trim($cleanResponse);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}