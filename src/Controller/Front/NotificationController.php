<?php

namespace App\Controller\Front;

use App\Entity\Notification;
use App\Repository\NotificationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends AbstractController
{
    private $entityManager;

        public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/admin/notification/{id}/read', name: 'admin_notification_read', methods: ['GET'])]
    public function markAsRead(Notification $notification, NotificationRepository $notificationRepository,Request $request): Response
    {
        if (!$notification->isRead()) {
            $notification->setIsRead(true);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true]);
        }

        // Redirect to wherever you want after marking as read
        return $this->redirectToRoute('back_dashboard');
    }
}