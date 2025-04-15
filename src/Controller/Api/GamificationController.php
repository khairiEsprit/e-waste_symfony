<?php

namespace App\Controller\Api;

use App\Entity\GamificationAction;
use App\Entity\User;
use App\EventListener\UserActionEvent;
use App\Repository\GamificationActionRepository;
use App\Repository\UserRepository;
use App\Repository\UserRewardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class GamificationController extends AbstractController
{
    #[Route('/user/rewards', name: 'api_user_rewards', methods: ['GET'])]
    public function getUserRewards(
        Request $request,
        UserRewardRepository $userRewardRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        // Check if user is authenticated
        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Get user from query parameter or use current user
        $userId = $request->query->get('userId');
        if ($userId) {
            // Only admins can view other users' rewards
            if (!$this->isGranted('ROLE_ADMIN')) {
                return new JsonResponse(['error' => 'Permission denied'], Response::HTTP_FORBIDDEN);
            }
            
            $user = $userRepository->find($userId);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
        } else {
            $user = $this->getUser();
        }
        
        // Get user's total points
        $totalPoints = $userRewardRepository->getUserTotalPoints($user);
        
        // Get user's badges
        $badges = [];
        $badgeRewards = $userRewardRepository->findBy(
            ['user' => $user],
            ['earnedAt' => 'DESC']
        );
        
        foreach ($badgeRewards as $reward) {
            if ($reward->getReward()) {
                $badges[] = [
                    'name' => $reward->getReward()->getName(),
                    'earnedAt' => $reward->getEarnedAt()->format('Y-m-d H:i:s'),
                    'imageUrl' => $reward->getReward()->getImageUrl(),
                ];
            }
        }
        
        // Get user's action history
        $actions = [];
        $actionHistory = $userRewardRepository->getUserActionHistory($user);
        
        foreach ($actionHistory as $history) {
            if ($history->getAction()) {
                $actions[] = [
                    'name' => $history->getAction()->getName(),
                    'points' => $history->getPoints(),
                    'date' => $history->getEarnedAt()->format('Y-m-d H:i:s'),
                ];
            }
        }
        
        $data = [
            'userId' => $user->getId(),
            'totalPoints' => $totalPoints,
            'badges' => $badges,
            'actions' => $actions,
        ];
        
        return new JsonResponse($data);
    }
    
    #[Route('/action', name: 'api_action_submit', methods: ['POST'])]
    public function submitAction(
        Request $request,
        GamificationActionRepository $actionRepository,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher
    ): JsonResponse {
        // Check if user is authenticated
        if (!$this->isGranted('ROLE_USER')) {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }
        
        // Parse request data
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['actionId'])) {
            return new JsonResponse(['error' => 'Action ID is required'], Response::HTTP_BAD_REQUEST);
        }
        
        // Get action
        $action = $actionRepository->find($data['actionId']);
        if (!$action) {
            return new JsonResponse(['error' => 'Action not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Get user (from request or current user)
        if (isset($data['userId']) && $this->isGranted('ROLE_ADMIN')) {
            $user = $userRepository->find($data['userId']);
            if (!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }
        } else {
            $user = $this->getUser();
        }
        
        try {
            // Dispatch event to handle the action
            $event = new UserActionEvent($user, $action);
            $eventDispatcher->dispatch($event, 'gamification.user_action');
            
            return new JsonResponse([
                'success' => true,
                'message' => sprintf('Earned %d points for "%s"', $action->getPoints(), $action->getName()),
                'points' => $action->getPoints(),
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
