<?php

namespace App\Controller\Back;

use App\Entity\GamificationAction;
use App\Entity\Reward;
use App\Entity\UserReward;
use App\Form\AssignRewardType;
use App\Form\GamificationActionType;
use App\Form\RewardType;
use App\Repository\EngagementPredictionRepository;
use App\Repository\GamificationActionRepository;
use App\Repository\RewardRepository;
use App\Repository\UserRepository;
use App\Repository\UserRewardRepository;
use App\Service\OpenRouterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/gamification')]
class GamificationController extends AbstractController
{
    #[Route('/', name: 'admin_gamification_dashboard')]
    public function index(
        GamificationActionRepository $actionRepository,
        RewardRepository $rewardRepository,
        UserRewardRepository $userRewardRepository,
        EngagementPredictionRepository $predictionRepository
    ): Response {
        // Get all actions and rewards
        $actions = $actionRepository->findAll();
        $rewards = $rewardRepository->findAll();

        // Get leaderboard data
        $leaderboard = $userRewardRepository->getLeaderboard(10);

        // Get points data for chart (last 30 days)
        $pointsData = $userRewardRepository->getPointsLast30Days();

        // Format data for Chart.js
        $chartLabels = [];
        $chartValues = [];

        foreach ($pointsData as $data) {
            $chartLabels[] = $data['date'];
            $chartValues[] = $data['points'];
        }

        // Get high engagement users
        $highEngagementUsers = $predictionRepository->getHighEngagementUsers();

        return $this->render('back/gamification/dashboard.html.twig', [
            'actions' => $actions,
            'rewards' => $rewards,
            'leaderboard' => $leaderboard,
            'chartLabels' => json_encode($chartLabels),
            'chartValues' => json_encode($chartValues),
            'highEngagementUsers' => $highEngagementUsers,
        ]);
    }

    #[Route('/action/new', name: 'admin_gamification_action_new', methods: ['GET', 'POST'])]
    public function newAction(Request $request, GamificationActionRepository $actionRepository): Response
    {
        $action = new GamificationAction();
        $form = $this->createForm(GamificationActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actionRepository->save($action, true);

            $this->addFlash('success', 'Action created successfully.');

            return $this->redirectToRoute('admin_gamification_dashboard');
        }

        return $this->render('back/gamification/action_form.html.twig', [
            'form' => $form->createView(),
            'action' => $action,
            'isNew' => true,
        ]);
    }

    #[Route('/action/{id}/edit', name: 'admin_gamification_action_edit', methods: ['GET', 'POST'])]
    public function editAction(
        Request $request,
        GamificationAction $action,
        GamificationActionRepository $actionRepository
    ): Response {
        $form = $this->createForm(GamificationActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $actionRepository->save($action, true);

            $this->addFlash('success', 'Action updated successfully.');

            return $this->redirectToRoute('admin_gamification_dashboard');
        }

        return $this->render('back/gamification/action_form.html.twig', [
            'form' => $form->createView(),
            'action' => $action,
            'isNew' => false,
        ]);
    }

    #[Route('/action/{id}/delete', name: 'admin_gamification_action_delete', methods: ['POST'])]
    public function deleteAction(
        Request $request,
        GamificationAction $action,
        GamificationActionRepository $actionRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $action->getId(), $request->request->get('_token'))) {
            $actionRepository->remove($action, true);

            $this->addFlash('success', 'Action deleted successfully.');
        }

        return $this->redirectToRoute('admin_gamification_dashboard');
    }

    #[Route('/reward/new', name: 'admin_gamification_reward_new', methods: ['GET', 'POST'])]
    public function newReward(Request $request, RewardRepository $rewardRepository): Response
    {
        $reward = new Reward();
        $form = $this->createForm(RewardType::class, $reward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rewardRepository->save($reward, true);

            $this->addFlash('success', 'Reward created successfully.');

            return $this->redirectToRoute('admin_gamification_dashboard');
        }

        return $this->render('back/gamification/reward_form.html.twig', [
            'form' => $form->createView(),
            'reward' => $reward,
            'isNew' => true,
        ]);
    }

    #[Route('/reward/{id}/edit', name: 'admin_gamification_reward_edit', methods: ['GET', 'POST'])]
    public function editReward(
        Request $request,
        Reward $reward,
        RewardRepository $rewardRepository
    ): Response {
        $form = $this->createForm(RewardType::class, $reward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rewardRepository->save($reward, true);

            $this->addFlash('success', 'Reward updated successfully.');

            return $this->redirectToRoute('admin_gamification_dashboard');
        }

        return $this->render('back/gamification/reward_form.html.twig', [
            'form' => $form->createView(),
            'reward' => $reward,
            'isNew' => false,
        ]);
    }

    #[Route('/reward/{id}/delete', name: 'admin_gamification_reward_delete', methods: ['POST'])]
    public function deleteReward(
        Request $request,
        Reward $reward,
        RewardRepository $rewardRepository
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $reward->getId(), $request->request->get('_token'))) {
            $rewardRepository->remove($reward, true);

            $this->addFlash('success', 'Reward deleted successfully.');
        }

        return $this->redirectToRoute('admin_gamification_dashboard');
    }

    #[Route('/assign-reward', name: 'admin_gamification_assign_reward', methods: ['GET', 'POST'])]
    public function assignReward(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $userReward = new UserReward();
        $form = $this->createForm(AssignRewardType::class, $userReward);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($userReward);
            $entityManager->flush();

            $this->addFlash('success', 'Reward assigned successfully.');

            return $this->redirectToRoute('admin_gamification_dashboard');
        }

        return $this->render('back/gamification/assign_reward_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/generate-predictions', name: 'admin_gamification_generate_predictions', methods: ['GET', 'POST'])]
    public function generatePredictions(
        Request $request,
        EngagementPredictionRepository $predictionRepository,
        UserRepository $userRepository,
        UserRewardRepository $userRewardRepository,
        OpenRouterService $openRouterService,
        EntityManagerInterface $entityManager
    ): Response {
        $generateNew = $request->query->has('generate');
        $generationResult = null;

        if ($generateNew) {
            // Run the prediction command manually
            $users = $userRepository->findBy(['active' => true]);
            $successCount = 0;
            $errorCount = 0;
            $skippedCount = 0;

            foreach ($users as $user) {
                try {
                    // Get user stats
                    $actionCount = $userRewardRepository->getUserActionCount($user);
                    $totalPoints = $userRewardRepository->getUserTotalPoints($user);

                    // Skip users with no activity
                    if ($actionCount === 0) {
                        $skippedCount++;
                        continue;
                    }

                    // Create prompt for AI
                    $prompt = "Given a user with {$actionCount} waste disposal actions and {$totalPoints} points in our e-waste recycling platform, predict their engagement likelihood (0-100%) for recycling in the next week. Provide only a number between 0 and 100 as your answer.";

                    // Call OpenRouter API
                    $response = $openRouterService->generateResponse($prompt);
                    $aiResponse = $response['choices'][0]['message']['content'] ?? null;

                    if (!$aiResponse) {
                        throw new \Exception('No response from AI service');
                    }

                    // Extract score from response (looking for a number between 0-100)
                    $score = $this->extractScore($aiResponse);

                    // Create and save prediction
                    $prediction = new \App\Entity\EngagementPrediction();
                    $prediction->setUser($user)
                        ->setScore($score)
                        ->setAiResponse($aiResponse);

                    $entityManager->persist($prediction);
                    $successCount++;
                } catch (\Exception $e) {
                    $errorCount++;
                }
            }

            $entityManager->flush();

            $generationResult = [
                'success' => $successCount,
                'error' => $errorCount,
                'skipped' => $skippedCount,
                'total' => count($users)
            ];

            $this->addFlash('success', "Generated predictions for {$successCount} users. Errors: {$errorCount}. Skipped: {$skippedCount}");
        }

        // Get all predictions, grouped by user
        $allPredictions = $predictionRepository->findAllPredictionsWithUsers();

        return $this->render('back/gamification/predictions.html.twig', [
            'predictions' => $allPredictions,
            'generationResult' => $generationResult
        ]);
    }

    private function extractScore(string $aiResponse): float
    {
        // Try to extract a number between 0-100 from the response
        preg_match('/\b([0-9]{1,2}(\.[0-9]+)?|100(\.0+)?)\b/', $aiResponse, $matches);

        if (isset($matches[1])) {
            $score = (float) $matches[1];
            // Ensure score is between 0-100
            return max(0, min(100, $score));
        }

        // Default score if no valid number found
        return 50.0;
    }
}
