<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class FaceRecognitionController extends AbstractController
{
    private $faceRecognitionService;
    private $entityManager;
    private $security;

    public function __construct(
        FaceRecognitionService $faceRecognitionService,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    #[Route('/profile/face-recognition', name: 'profile_face_recognition')]
    public function faceRecognitionSetup(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('security_login');
        }

        // Check if face recognition service is available
        $serviceAvailable = $this->faceRecognitionService->isServiceAvailable();

        return $this->render('front/profile/face_recognition.html.twig', [
            'user' => $user,
            'serviceAvailable' => $serviceAvailable,
        ]);
    }

    #[Route('/profile/face-recognition/setup', name: 'profile_face_recognition_setup', methods: ['POST'])]
    public function setupFaceRecognition(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Get the base64 image from the request
        $data = json_decode($request->getContent(), true);
        $base64Image = $data['image'] ?? null;

        if (!$base64Image) {
            return $this->json(['success' => false, 'message' => 'No image provided'], Response::HTTP_BAD_REQUEST);
        }

        // Setup face recognition
        $result = $this->faceRecognitionService->setupFaceRecognition($user, $base64Image);

        return $this->json($result);
    }

    #[Route('/profile/face-recognition/disable', name: 'profile_face_recognition_disable', methods: ['POST'])]
    public function disableFaceRecognition(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['success' => false, 'message' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isFaceRecognitionEnabled()) {
            return $this->json(['success' => false, 'message' => 'Face recognition is not enabled for this user']);
        }

        // Disable face recognition
        $this->faceRecognitionService->disableFaceRecognition($user);

        return $this->json(['success' => true, 'message' => 'Face recognition has been disabled']);
    }

    #[Route('/login/face', name: 'login_face', methods: ['POST'])]
    public function loginWithFace(Request $request): JsonResponse
    {
        // This route is handled by the FaceRecognitionAuthenticator
        // This method should never be called directly
        return $this->json(['success' => false, 'message' => 'This route is handled by the authenticator']);
    }

    #[Route('/login/face-check', name: 'login_face_check', methods: ['POST'])]
    public function checkFace(Request $request): JsonResponse
    {
        // Get the base64 image from the request
        $data = json_decode($request->getContent(), true);
        $base64Image = $data['image'] ?? null;

        if (!$base64Image) {
            return $this->json(['success' => false, 'message' => 'No image provided'], Response::HTTP_BAD_REQUEST);
        }

        // Check if the image contains a valid face
        $result = $this->faceRecognitionService->detectFace($base64Image);

        return $this->json($result);
    }
}