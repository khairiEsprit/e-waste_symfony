<?php
// src/Controller/ChatController.php
namespace App\Controller\Front;

use App\Entity\Chat;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
class ChatbotController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    #[Route('/chat', name: 'chat', methods: ['GET'])]
    public function index(): Response
    {
        // Render the template without passing previous messages
        // The frontend will handle messages dynamically via AJAX
        return $this->render('front/chatbot/index.html.twig', [
            'messages' => [], // Empty array since we clear on reload
        ]);
    }

    #[Route('/chat/upload', name: 'chat_upload', methods: ['POST'])]
    public function upload(Request $request, EntityManagerInterface $em): Response
    {
        $file = $request->files->get('image');
        $userMessage = $request->request->get('message', 'User uploaded an image');

        if (!$file) {
            return new JsonResponse([
                'success' => false,
                'error' => 'No image uploaded',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Save the file temporarily
        $filename = uniqid() . '.' . $file->guessExtension();
        $filePath = $this->getParameter('upload_directory') . '/' . $filename;
        $file->move($this->getParameter('upload_directory'), $filename);

        // Save user message to database
        $chat = new Chat();
        $chat->setUser('User')->setMessage($userMessage . ' [Image: ' . $filename . ']');
        $em->persist($chat);

        try {
            // Prepare the multipart/form-data request
            $response = $this->httpClient->request('POST', 'http://localhost:5000/predict', [
                'body' => [
                    'image' => fopen($filePath, 'r'),
                ],
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
            ]);

            $data = $response->toArray();
            $prediction = $data['prediction'] ?? 'Unknown';
            $probability = $data['probability'] ?? 'N/A';
            $botMessage = "This waste is classified as: $prediction (Probability: $probability)";
        } catch (\Exception $e) {
            $botMessage = "Sorry, I couldn’t classify this waste. Error: " . $e->getMessage();
        }

        // Save bot response to database
        $botChat = new Chat();
        $botChat->setUser('Bot')->setMessage($botMessage);
        $em->persist($botChat);
        $em->flush();

        // Optionally, delete the temporary file
        // unlink($filePath);

        // Return JSON response instead of redirect
        return new JsonResponse([
            'success' => true,
            'message' => $botMessage, // This will be displayed by the frontend
        ]);
    }
}