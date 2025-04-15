<?php

namespace App\Controller\Back;

use App\Service\OpenRouterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Cloudinary\Cloudinary;

#[Route('/admin')]
class ChatbotController extends AbstractController
{
    private $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }
    
    #[Route('/chatbot', name: 'app_back_chatbot')]
    public function index(): Response
    {
        return $this->render('back/chatbot/index.html.twig');
    }

    #[Route('/chatbot/message', name: 'app_back_chatbot_message', methods: ['POST'])]
    public function message(Request $request, OpenRouterService $openRouterService): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!$data) {
                throw new \Exception('Invalid JSON received: ' . $request->getContent());
            }
            
            $message = $data['message'] ?? '';
            $imageUrl = $data['imageUrl'] ?? null;

            if (empty($message)) {
                return $this->json([
                    'success' => false,
                    'error' => 'Message cannot be empty',
                    'response' => 'Please provide a message.'
                ], 400);
            }

            // Log incoming request for debugging
            error_log('Chatbot message request: ' . json_encode([
                'message' => $message,
                'hasImage' => !empty($imageUrl)
            ]));
            
            try {
                $response = $openRouterService->generateResponse($message, $imageUrl);
                $botResponse = $response['choices'][0]['message']['content'];
                
                error_log('Bot response: ' . $botResponse);
                
                return $this->json([
                    'success' => true,
                    'response' => $botResponse
                ]);
            } catch (\Exception $e) {
                error_log('OpenRouter API Error: ' . $e->getMessage());
                
                // Provide a more specific error message based on the exception
                if (strpos($e->getMessage(), '404') !== false) {
                    return $this->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'response' => 'The AI service endpoint could not be found. Please check the API configuration.'
                    ], 500);
                } else {
                    return $this->json([
                        'success' => false,
                        'error' => $e->getMessage(),
                        'response' => 'Sorry, there was an error connecting to the AI service. Please try again later.'
                    ], 500);
                }
            }
        } catch (\Exception $e) {
            error_log('Chatbot error: ' . $e->getMessage());
            
            // Return a more specific error message based on exception
            $errorMessage = 'Sorry, there was an error processing your request. ';
            
            if (strpos($e->getMessage(), 'API') !== false) {
                $errorMessage .= 'The AI service is currently unavailable. Please try again later.';
            } elseif (strpos($e->getMessage(), 'timeout') !== false) {
                $errorMessage .= 'The request timed out. Please try a shorter message.';
            } else {
                $errorMessage .= 'Please try again later.';
            }
            
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'response' => $errorMessage
            ], 500);
        }
    }

    #[Route('/upload', name: 'app_back_chatbot_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        try {
            $file = $request->files->get('file');

            if (!$file) {
                return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
            }

            // Validate file type
            $mimeType = $file->getMimeType();
            if (!str_starts_with($mimeType, 'image/')) {
                return $this->json(['success' => false, 'error' => 'Only image files are allowed'], 400);
            }

            // Validate file size (5MB limit)
            if ($file->getSize() > 5 * 1024 * 1024) {
                return $this->json(['success' => false, 'error' => 'File size exceeds 5MB limit'], 400);
            }

            $uploadResult = $this->cloudinary->uploadApi()->upload(
                $file->getPathname(),
                [
                    'folder' => 'chatbot',
                    'resource_type' => 'image',
                ]
            );

            $publicUrl = $uploadResult['secure_url'];

            return $this->json([
                'success' => true,
                'url' => $publicUrl
            ]);
        } catch (\Exception $e) {
            error_log('Upload error: ' . $e->getMessage());
            return $this->json([
                'success' => false,
                'error' => 'File upload failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
