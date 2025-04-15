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
use App\Repository\UserRewardRepository;
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
        if ($this->isCsrfTokenValid('delete'.$action->getId(), $request->request->get('_token'))) {
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
        if ($this->isCsrfTokenValid('delete'.$reward->getId(), $request->request->get('_token'))) {
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
}
