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
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';
        $imageUrl = $data['imageUrl'] ?? null;

        try {
            $response = $openRouterService->generateResponse($message, $imageUrl);
            return $this->json([
                'success' => true,
                'response' => $response['choices'][0]['message']['content'] ?? 'No response'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/upload', name: 'app_back_chatbot_upload', methods: ['POST'])]
    public function upload(Request $request): JsonResponse
    {
        $file = $request->files->get('file');

        if (!$file) {
            return $this->json(['success' => false, 'error' => 'No file uploaded'], 400);
        }

        try {
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
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}