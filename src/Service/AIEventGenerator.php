<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AIEventGenerator
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    /**
     * Generate event suggestions using AI
     * 
     * @param string $prompt The prompt to send to the AI
     * @return array The generated event suggestions
     * @throws \Exception
     */
    public function generateEventSuggestions(string $prompt): array
    {
        $client = HttpClient::create();

        try {
            // Log the request for debugging
            error_log('Sending request to OpenRouter API: ' . self::API_URL);
            
            $response = $client->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => $_SERVER['HTTP_HOST'] ?? 'e-waste-symfony.com',
                    'X-Title' => 'E-Waste Event Generator'
                ],
                'json' => [
                    'model' => 'openrouter/optimus-alpha',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are an event planning assistant for an e-waste management platform. Generate creative and engaging event ideas related to e-waste management, recycling, and environmental sustainability. For each event, provide a title, description, location suggestion, and recommended capacity. Format your response as JSON with the following structure: {"events": [{"title": "Event Title", "description": "Event description", "location": "Location", "capacity": 50}]}.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ],
                    'temperature' => 0.7,
                    'max_tokens' => 1000,
                    'data_privacy' => [
                        'prompt_training' => true,
                        'response_training' => true
                    ]
                ]
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200) {
                $responseContent = $response->getContent(false);
                error_log('OpenRouter API error: Status ' . $statusCode . ', Response: ' . $responseContent);
                throw new \Exception('API returned status code ' . $statusCode . ': ' . $responseContent);
            }
            
            $data = $response->toArray();
            
            if (isset($data['choices'][0]['message']['content'])) {
                $content = $data['choices'][0]['message']['content'];
                
                // Log the raw content for debugging
                error_log('OpenRouter API response: ' . $content);
                
                // Try to decode JSON directly
                $decodedContent = json_decode($content, true);
                
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                    // If we have events array directly in the response
                    if (isset($decodedContent['events']) && is_array($decodedContent['events'])) {
                        return $decodedContent;
                    }
                    
                    // If we have a direct array of events
                    if (count(array_filter(array_keys($decodedContent), 'is_string')) > 0) {
                        // This is likely a single event or object with properties
                        return ['events' => [$decodedContent]];
                    }
                    
                    // If we have a numerically indexed array
                    if (count($decodedContent) > 0 && isset($decodedContent[0])) {
                        return ['events' => $decodedContent];
                    }
                    
                    // Return whatever we got
                    return $decodedContent;
                } else {
                    // Try to extract JSON if the response isn't properly formatted
                    $matches = [];
                    if (preg_match('/\{.*\}/s', $content, $matches)) {
                        $jsonContent = $matches[0];
                        $decodedContent = json_decode($jsonContent, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                            return ['events' => [$decodedContent]];
                        }
                    }
                    
                    // If we can't parse JSON, create a simple structure with the raw content
                    return [
                        'events' => [
                            [
                                'title' => 'Suggestion from AI',
                                'description' => $content,
                                'location' => 'To be determined',
                                'capacity' => 50
                            ]
                        ]
                    ];
                }
            }
            
            throw new \Exception('Invalid API response format: ' . json_encode($data));
        } catch (TransportExceptionInterface $e) {
            error_log('OpenRouter API transport error: ' . $e->getMessage());
            throw new \Exception('API request failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            error_log('OpenRouter API general error: ' . $e->getMessage());
            throw $e;
        }
    }
}
