<?php

namespace App\EventListener;

use App\Entity\GamificationAction;
use App\Entity\Reward;
use App\Entity\User;
use App\Entity\UserReward;
use App\EventListener\UserActionEvent;
use App\Repository\RewardRepository;
use App\Repository\UserRewardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserActionListener implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;
    private RewardRepository $rewardRepository;
    private UserRewardRepository $userRewardRepository;
    private RequestStack $requestStack;
    private LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        RewardRepository $rewardRepository,
        UserRewardRepository $userRewardRepository,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->rewardRepository = $rewardRepository;
        $this->userRewardRepository = $userRewardRepository;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'gamification.user_action' => 'onUserAction',
        ];
    }

    public function onUserAction(UserActionEvent $event): void
    {
        $user = $event->getUser();
        $action = $event->getAction();

        try {
            // Create user reward record
            $userReward = new UserReward();
            $userReward->setUser($user)
                ->setAction($action)
                ->setPoints($action->getPoints());

            $this->entityManager->persist($userReward);
            $this->entityManager->flush();

            // Check if user qualifies for any rewards
            $this->checkForRewards($user);

            // Add flash message if in web context
            $session = $this->requestStack->getSession();
            if ($session->isStarted()) {
                $session->getFlashBag()->add(
                    'success',
                    sprintf('You earned %d points for "%s"!', $action->getPoints(), $action->getName())
                );
            }

            $this->logger->info('User action processed', [
                'user_id' => $user->getId(),
                'action_id' => $action->getId(),
                'points' => $action->getPoints()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error processing user action', [
                'user_id' => $user->getId(),
                'action_id' => $action->getId(),
                'error' => $e->getMessage()
            ]);

            // Re-throw to allow controller to handle
            throw $e;
        }
    }

    private function checkForRewards(User $user): void
    {
        // Get user's total points
        $totalPoints = $this->userRewardRepository->getUserTotalPoints($user);

        // Find eligible rewards
        $eligibleRewards = $this->rewardRepository->findEligibleRewards($totalPoints);

        // Get rewards user already has
        $userRewards = $this->userRewardRepository->findBy(['user' => $user, 'reward' => $eligibleRewards]);
        $existingRewardIds = array_map(function ($ur) {
            return $ur->getReward()->getId();
        }, $userRewards);

        $newRewards = [];
        foreach ($eligibleRewards as $reward) {
            // Skip if user already has this reward
            if (in_array($reward->getId(), $existingRewardIds)) {
                continue;
            }

            // Assign new reward
            $userReward = new UserReward();
            $userReward->setUser($user)
                ->setReward($reward)
                ->setPoints(0); // No points for badge rewards

            $this->entityManager->persist($userReward);
            $newRewards[] = $reward;
        }

        if (!empty($newRewards)) {
            $this->entityManager->flush();

            // Add flash message for new rewards
            $session = $this->requestStack->getSession();
            if ($session->isStarted()) {
                foreach ($newRewards as $reward) {
                    $session->getFlashBag()->add(
                        'success',
                        sprintf('You earned a new reward: %s!', $reward->getName())
                    );
                }
            }
        }
    }
}
