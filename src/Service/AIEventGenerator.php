<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AIEventGenerator
{
    private const DEFAULT_MODEL = 'openrouter/optimus-alpha';
    private const DEFAULT_TEMPERATURE = 0.7;
    private const DEFAULT_MAX_TOKENS = 1000;
    private const DEFAULT_API_URL = 'https://openrouter.ai/api/v1/chat/completions';
    private const DEFAULT_HTTP_REFERER = 'e-waste-symfony.com';
    private const DEFAULT_APP_TITLE = 'E-Waste Event Generator';

    private readonly OpenRouterService $openRouterService;
    private readonly LoggerInterface $logger;

    public function __construct(
        OpenRouterService $openRouterService,
        LoggerInterface $logger
    ) {
        $this->openRouterService = $openRouterService;
        $this->logger = $logger;
    }

    /**
     * Generate event suggestions using AI.
     *
     * @param string $prompt The prompt to send to the AI
     * @return array The generated event suggestions in the format: ['events' => [...event objects...]]
     * @throws \RuntimeException If the API request or response parsing fails
     */
    public function generateEventSuggestions(string $prompt): array
    {
        if (empty($prompt)) {
            $this->logger->warning('Empty prompt provided, using default prompt');
            $prompt = 'Generate 3 e-waste recycling event ideas';
        }
        
        $this->logger->info('Generating event suggestions with prompt: {prompt}', [
            'prompt' => $prompt
        ]);

        try {
            // Enhance the prompt with specific instructions for JSON formatting
            $enhancedPrompt = $this->getSystemPrompt() . "\n\n" . $prompt;
            
            // Call the OpenRouter service
            $response = $this->openRouterService->generateResponse($enhancedPrompt);
            
            // Extract the content from the response
            $content = $response['choices'][0]['message']['content'] ?? null;
            
            if (!$content) {
                throw new \RuntimeException('Invalid API response: missing content');
            }
            
            // Format the response into event suggestions
            $suggestions = $this->formatEventSuggestions($content);
            
            $this->logger->info('Successfully generated {count} event suggestions', [
                'count' => count($suggestions['events'] ?? [])
            ]);
            
            return $suggestions;
        } catch (\Exception $e) {
            $this->logger->error('Failed to generate event suggestions: {message}', [
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ]);
            throw new \RuntimeException('Failed to generate event suggestions: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Get the system prompt for the AI
     * 
     * @return string The system prompt that instructs the AI how to respond
     */
    private function getSystemPrompt(): string
    {
        return <<<'EOT'
You are an event planning assistant for an e-waste management platform. Generate creative and engaging event ideas related to e-waste management, recycling, and environmental sustainability. For each event, provide a title, description, location suggestion, and recommended capacity. Format your response as JSON with the following structure: {"events": [{"title": "Event Title", "description": "Event description", "location": "Location", "capacity": 50}]}.
EOT;
    }

    /**
     * Format the event suggestions from the AI response
     * 
     * @param string $content The content from the AI response
     * @return array The formatted event suggestions
     */
    private function formatEventSuggestions(string $content): array
    {
        $this->logger->debug('Formatting event suggestions from content', [
            'content_length' => strlen($content)
        ]);

        // Clean the content by removing markdown code fences and extra formatting
        $cleanedContent = $this->cleanResponseContent($content);

        // Try to decode the cleaned content as JSON
        $decodedContent = json_decode($cleanedContent, true);
        $jsonError = json_last_error();

        if ($jsonError === JSON_ERROR_NONE && is_array($decodedContent)) {
            // Case 1: Content is already in the expected format with 'events' key
            if (isset($decodedContent['events']) && is_array($decodedContent['events'])) {
                $this->logger->debug('Content already in expected format with events key');
                return $this->sanitizeEventDescriptions($decodedContent);
            }

            // Case 2: Content is a single event object
            if ($this->isAssociativeArray($decodedContent) && isset($decodedContent['title'])) {
                $this->logger->debug('Content is a single event object');
                return ['events' => [$this->sanitizeEventDescription($decodedContent)]];
            }

            // Case 3: Content is an array of event objects
            if (count($decodedContent) > 0 && isset($decodedContent[0]['title'])) {
                $this->logger->debug('Content is an array of event objects');
                return ['events' => array_map([$this, 'sanitizeEventDescription'], $decodedContent)];
            }

            // Case 4: Some other JSON structure, return as is after sanitizing
            $this->logger->debug('Content is in an unexpected JSON format');
            return $this->sanitizeEventDescriptions($decodedContent);
        }

        // If JSON decoding failed, try to extract JSON from the content
        if ($jsonError !== JSON_ERROR_NONE) {
            $this->logger->warning('Failed to decode content as JSON: {error}', [
                'error' => json_last_error_msg()
            ]);
            
            // Try to extract JSON from malformed response
            $matches = [];
            if (preg_match('/\\{.*\\}/s', $cleanedContent, $matches)) {
                $jsonContent = $matches[0];
                $this->logger->debug('Extracted potential JSON from content');
                
                $decodedContent = json_decode($jsonContent, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedContent)) {
                    if (isset($decodedContent['title'])) {
                        return ['events' => [$this->sanitizeEventDescription($decodedContent)]];
                    } elseif (isset($decodedContent['events'])) {
                        return $this->sanitizeEventDescriptions($decodedContent);
                    }
                }
            }
        }

        // Fallback: wrap raw content in a default event structure
        $this->logger->notice('Using fallback event structure for non-JSON content');
        return [
            'events' => [
                [
                    'title' => 'Suggestion from AI',
                    'description' => $cleanedContent,
                    'location' => 'To be determined',
                    'capacity' => 50,
                ],
            ],
        ];
    }

    /**
     * Clean the AI response content by removing markdown code fences and extra formatting
     *
     * @param string $content The raw content from the AI response
     * @return string The cleaned content
     */
    private function cleanResponseContent(string $content): string
    {
        // Remove markdown code fences (```json, ```, etc.)
        $cleaned = preg_replace('/^```[a-zA-Z]*\n|\n```$/m', '', trim($content));
        
        // Remove any remaining backticks
        $cleaned = str_replace('```', '', $cleaned);
        
        // Remove extra whitespace and newlines
        $cleaned = trim(preg_replace('/\n\s*\n/', "\n", $cleaned));
        
        return $cleaned;
    }

    /**
     * Sanitize event descriptions in the response to ensure clean content
     *
     * @param array $data The decoded JSON data
     * @return array The sanitized data
     */
    private function sanitizeEventDescriptions(array $data): array
    {
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = array_map([$this, 'sanitizeEventDescription'], $data['events']);
        }
        return $data;
    }

    /**
     * Sanitize a single event description
     *
     * @param array $event The event data
     * @return array The sanitized event data
     */
    private function sanitizeEventDescription(array $event): array
    {
        if (isset($event['description'])) {
            // Remove any JSON-like formatting or extra quotes
            $event['description'] = trim($event['description'], '"');
            
            // Remove any remaining markdown or code-like syntax
            $event['description'] = preg_replace('/^```[a-zA-Z]*\n|\n```$/m', '', $event['description']);
            $event['description'] = str_replace('```', '', $event['description']);
            
            // Ensure the description is plain text
            $event['description'] = strip_tags($event['description']);
            
            // Trim and clean up whitespace
            $event['description'] = trim(preg_replace('/\s+/', ' ', $event['description']));
        }
        return $event;
    }

    /**
     * Check if an array is associative (has string keys)
     * 
     * @param array $array The array to check
     * @return bool True if the array is associative, false otherwise
     */
    private function isAssociativeArray(array $array): bool
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }
}