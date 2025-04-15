<?php

namespace App\Controller\Front;

use App\Entity\GamificationAction;
use App\EventListener\UserActionEvent;
use App\Form\UserActionType;
use App\Repository\GamificationActionRepository;
use App\Repository\RewardRepository;
use App\Repository\UserRewardRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/actions', name: 'user_actions')]
    public function actions(GamificationActionRepository $actionRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $actions = $actionRepository->findAll();
        
        $form = $this->createForm(UserActionType::class);
        
        return $this->render('front/user/actions.html.twig', [
            'actions' => $actions,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/action/submit', name: 'user_action_submit', methods: ['POST'])]
    public function submitAction(
        Request $request,
        GamificationActionRepository $actionRepository,
        EventDispatcherInterface $eventDispatcher
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $form = $this->createForm(UserActionType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $action = $data['action'];
            
            if (!$action instanceof GamificationAction) {
                throw new \InvalidArgumentException('Invalid action selected');
            }
            
            $user = $this->getUser();
            
            // Dispatch event to handle the action
            $event = new UserActionEvent($user, $action);
            $eventDispatcher->dispatch($event, 'gamification.user_action');
            
            return $this->redirectToRoute('user_rewards');
        }
        
        // If form is invalid, redirect back to actions page
        return $this->redirectToRoute('user_actions');
    }
    
    #[Route('/rewards', name: 'user_rewards')]
    public function rewards(
        UserRewardRepository $userRewardRepository,
        RewardRepository $rewardRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Get user's total points
        $totalPoints = $userRewardRepository->getUserTotalPoints($user);
        
        // Get user's badges (rewards)
        $userRewards = $userRewardRepository->findBy(['user' => $user, 'reward' => null], ['earnedAt' => 'DESC']);
        
        // Get user's badges
        $badges = $userRewardRepository->findBy(
            ['user' => $user],
            ['earnedAt' => 'DESC']
        );
        
        // Filter to only include records with rewards
        $badges = array_filter($badges, function($ur) {
            return $ur->getReward() !== null;
        });
        
        // Get user's action history
        $actionHistory = $userRewardRepository->getUserActionHistory($user);
        
        // Get user's rank
        $rank = $userRewardRepository->getUserRank($user);
        
        return $this->render('front/user/rewards.html.twig', [
            'totalPoints' => $totalPoints,
            'badges' => $badges,
            'actionHistory' => $actionHistory,
            'rank' => $rank,
        ]);
    }
    
    #[Route('/leaderboard', name: 'user_leaderboard')]
    public function leaderboard(UserRewardRepository $userRewardRepository): Response
    {
        // Public page, no authentication required
        
        // Get leaderboard data
        $leaderboard = $userRewardRepository->getLeaderboard();
        
        // Get current user's rank if logged in
        $rank = null;
        if ($this->getUser()) {
            $rank = $userRewardRepository->getUserRank($this->getUser());
        }
        
        return $this->render('front/user/leaderboard.html.twig', [
            'leaderboard' => $leaderboard,
            'userRank' => $rank,
        ]);
    }
}
