<?php

namespace App\Controller\Back;

use App\Service\AIEventGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/event/ai-suggestions')]
class AIEventSuggestionController extends AbstractController
{
    private AIEventGenerator $aiEventGenerator;

    public function __construct(AIEventGenerator $aiEventGenerator)
    {
        $this->aiEventGenerator = $aiEventGenerator;
    }

    #[Route('/', name: 'app_event_ai_suggestions', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('back/event/ai_suggestions/index.html.twig');
    }

    #[Route('/generate', name: 'app_event_ai_suggestions_generate', methods: ['POST'])]
    public function generate(Request $request): JsonResponse
    {
        try {
            $prompt = $request->request->get('prompt', 'Generate 3 creative e-waste management event ideas');
            
            $suggestions = $this->aiEventGenerator->generateEventSuggestions($prompt);
            
            return $this->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to generate suggestions: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/view/{id}', name: 'app_event_ai_suggestion_view', methods: ['GET'])]
    public function view(string $id): Response
    {
        // This route will be used to view a single suggestion in detail
        // The ID will be used to identify which suggestion from the session to display
        
        return $this->render('back/event/ai_suggestions/view.html.twig', [
            'id' => $id
        ]);
    }
}
