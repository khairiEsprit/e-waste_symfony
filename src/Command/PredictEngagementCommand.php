<?php

namespace App\Command;

use App\Entity\EngagementPrediction;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\UserRewardRepository;
use App\Service\OpenRouterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:predict-engagement',
    description: 'Predict user engagement using AI',
)]
class PredictEngagementCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserRepository $userRepository;
    private UserRewardRepository $userRewardRepository;
    private OpenRouterService $openRouterService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        UserRewardRepository $userRewardRepository,
        OpenRouterService $openRouterService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->userRewardRepository = $userRewardRepository;
        $this->openRouterService = $openRouterService;
    }

    protected function configure(): void
    {
        $this->setHelp('This command predicts user engagement likelihood using OpenRouter AI API');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Predicting User Engagement');

        $users = $this->userRepository->findBy(['active' => true]);
        $io->progressStart(count($users));

        $successCount = 0;
        $errorCount = 0;

        foreach ($users as $user) {
            try {
                $this->predictForUser($user, $io);
                $successCount++;
            } catch (\Exception $e) {
                $io->error("Error predicting for user {$user->getId()}: {$e->getMessage()}");
                $errorCount++;
            }
            $io->progressAdvance();
        }

        $io->progressFinish();
        $io->success("Completed predictions for {$successCount} users. Errors: {$errorCount}");

        return Command::SUCCESS;
    }

    private function predictForUser(User $user, SymfonyStyle $io): void
    {
        // Get user stats
        $actionCount = $this->userRewardRepository->getUserActionCount($user);
        $totalPoints = $this->userRewardRepository->getUserTotalPoints($user);

        // Skip users with no activity
        if ($actionCount === 0) {
            $io->note("Skipping user {$user->getId()} with no activity");
            return;
        }

        // Create prompt for AI
        $prompt = "Given a user with {$actionCount} waste disposal actions and {$totalPoints} points in our e-waste recycling platform, predict their engagement likelihood (0-100%) for recycling in the next week. Provide only a number between 0 and 100 as your answer.";

        // Call OpenRouter API
        $response = $this->openRouterService->generateResponse($prompt);
        $aiResponse = $response['choices'][0]['message']['content'] ?? null;

        if (!$aiResponse) {
            throw new \Exception('No response from AI service');
        }

        // Extract score from response (looking for a number between 0-100)
        $score = $this->extractScore($aiResponse);

        // Create and save prediction
        $prediction = new EngagementPrediction();
        $prediction->setUser($user)
            ->setScore($score)
            ->setAiResponse($aiResponse);

        $this->entityManager->persist($prediction);
        $this->entityManager->flush();
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
