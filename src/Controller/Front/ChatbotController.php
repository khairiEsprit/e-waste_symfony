<?php

namespace App\Controller\Front;

use App\Entity\ChatMessage;
use App\Service\ChatbotService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatbotController extends AbstractController
{
    #[Route('/chatbot', name: 'app_chatbot')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Clear chat history when the page loads
        $this->clearChatHistory($request, $entityManager);

        return $this->render('front/chatbot/index.html.twig');
    }

    #[Route('/chatbot/send', name: 'app_chatbot_send', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        ChatbotService $chatbotService,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $userMessage = $request->request->get('message', '');
        $ipAddress = $request->getClientIp();

        // Save user message
        $userChatMessage = new ChatMessage();
        $userChatMessage->setContent($userMessage);
        $userChatMessage->setIsFromUser(true);
        $userChatMessage->setIpAddress($ipAddress);
        $entityManager->persist($userChatMessage);

        // Get bot response
        $botResponse = $chatbotService->getEducationalResponse($userMessage, [
            'location' => 'Montreal', // Can be dynamic based on user
            'language' => $request->getLocale(),
        ]);

        // Save bot response
        $botChatMessage = new ChatMessage();
        $botChatMessage->setContent($botResponse);
        $botChatMessage->setIsFromUser(false);
        $botChatMessage->setIpAddress($ipAddress);
        $entityManager->persist($botChatMessage);

        $entityManager->flush();

        return $this->json([
            'user_message' => $userMessage,
            'bot_response' => $botResponse,
            'timestamp' => (new \DateTime())->format('H:i'),
        ]);
    }

    #[Route('/chatbot/history', name: 'app_chatbot_history')]
    public function getHistory(EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $messages = $entityManager->getRepository(ChatMessage::class)
            ->findBy(
                ['ipAddress' => $request->getClientIp()], 
                ['createdAt' => 'ASC'], 
                20
            );

        return $this->json(array_map(function(ChatMessage $message) {
            return [
                'content' => $message->getContent(),
                'isFromUser' => $message->isFromUser(),
                'time' => $message->getCreatedAt()->format('H:i'),
            ];
        }, $messages));
    }

    #[Route('/chatbot/clear-history', name: 'app_chatbot_clear', methods: ['POST'])]
    public function clearHistory(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->clearChatHistory($request, $entityManager);
        return new JsonResponse(['status' => 'success']);
    }

    /**
     * Helper method to clear chat history based on IP address.
     */
    private function clearChatHistory(Request $request, EntityManagerInterface $entityManager): void
    {
        $ip = $request->getClientIp();
        $messages = $entityManager->getRepository(ChatMessage::class)
            ->findBy(['ipAddress' => $ip]);
        
        foreach ($messages as $message) {
            $entityManager->remove($message);
        }
        
        $entityManager->flush();
    }
}