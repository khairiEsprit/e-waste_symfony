<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\FaceLoginAttempt;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FaceRecognitionService
{
    private const FACE_API_URL = 'http://localhost:5000';
    private const CONFIDENCE_THRESHOLD = 0.6;
    private const MAX_FAILED_ATTEMPTS = 3;
    private const RATE_LIMIT_WINDOW_MINUTES = 30;

    private $httpClient;
    private $entityManager;
    private $logger;
    private $requestStack;
    private $security;
    private $params;
    private $facePhotosDir;

    public function __construct(
        HttpClientInterface $httpClient,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RequestStack $requestStack,
        Security $security,
        ParameterBagInterface $params
    ) {
        $this->httpClient = $httpClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
        $this->security = $security;
        $this->params = $params;

        // Define the directory where face photos will be stored
        $this->facePhotosDir = $params->get('kernel.project_dir') . '/var/uploads/face_photos';

        // Create directory if it doesn't exist
        if (!is_dir($this->facePhotosDir)) {
            mkdir($this->facePhotosDir, 0755, true);
        }
    }

    /**
     * Check if the face recognition service is available
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = $this->httpClient->request('GET', self::FACE_API_URL . '/health', [
                'timeout' => 3,
            ]);

            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            $this->logger->error('Face recognition service health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Detect if an image contains a single valid face
     */
    public function detectFace(string $base64Image): array
    {
        try {
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/detect-face', [
                'json' => [
                    'image' => $base64Image
                ],
                'timeout' => 10,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Face detection failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face detection service unavailable: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extract face embeddings from an image
     */
    public function extractEmbeddings(string $base64Image): array
    {
        try {
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/extract-embedding', [
                'json' => [
                    'image' => $base64Image
                ],
                'timeout' => 15,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Face embedding extraction failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face embedding extraction failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Compare face embeddings
     */
    public function compareEmbeddings(array $embedding1, array $embedding2): array
    {
        try {
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/compare-embeddings', [
                'json' => [
                    'embedding1' => $embedding1,
                    'embedding2' => $embedding2
                ],
                'timeout' => 5,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Face embedding comparison failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face embedding comparison failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verify a face against stored embeddings
     */
    public function verifyFace(string $base64Image, array $storedEmbedding): array
    {
        try {
            $response = $this->httpClient->request('POST', self::FACE_API_URL . '/verify-face', [
                'json' => [
                    'image' => $base64Image,
                    'stored_embedding' => $storedEmbedding
                ],
                'timeout' => 15,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Face verification failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Face verification failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Setup face recognition for a user
     */
    public function setupFaceRecognition(User $user, string $base64Image): array
    {
        // Detect if the image contains a valid face
        $detectionResult = $this->detectFace($base64Image);
        if (!$detectionResult['success']) {
            return $detectionResult;
        }

        // Extract face embeddings
        $embeddingResult = $this->extractEmbeddings($base64Image);
        if (!$embeddingResult['success']) {
            return $embeddingResult;
        }

        // Save the embeddings to the user
        $user->setFaceEmbeddings(json_encode($embeddingResult['embedding']));
        $user->setFaceRecognitionEnabled(true);

        // Save the face photo
        $photoPath = $this->saveFacePhoto($user, $base64Image);
        $user->setFacePhotoPath($photoPath);

        // Persist changes
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return [
            'success' => true,
            'message' => 'Face recognition setup successful'
        ];
    }

    /**
     * Authenticate a user using face recognition
     */
    public function authenticateWithFace(string $base64Image): array
    {
        // Get all users with face recognition enabled
        $users = $this->entityManager->getRepository(User::class)->findBy(['isFaceRecognitionEnabled' => true]);

        if (empty($users)) {
            return [
                'success' => false,
                'message' => 'No users with face recognition enabled'
            ];
        }

        // Extract embeddings from the provided image
        $embeddingResult = $this->extractEmbeddings($base64Image);
        if (!$embeddingResult['success']) {
            return $embeddingResult;
        }

        $bestMatch = null;
        $highestSimilarity = 0;

        // Compare with all users' embeddings
        foreach ($users as $user) {
            $storedEmbeddings = json_decode($user->getFaceEmbeddings(), true);
            if (!$storedEmbeddings) {
                continue;
            }

            $comparisonResult = $this->compareEmbeddings($embeddingResult['embedding'], $storedEmbeddings);

            if ($comparisonResult['success'] && $comparisonResult['similarity'] > $highestSimilarity) {
                $highestSimilarity = $comparisonResult['similarity'];
                $bestMatch = [
                    'user' => $user,
                    'similarity' => $comparisonResult['similarity'],
                    'is_match' => $comparisonResult['is_match']
                ];
            }
        }

        // Log the authentication attempt
        $request = $this->requestStack->getCurrentRequest();
        $ipAddress = $request ? $request->getClientIp() : null;
        $userAgent = $request ? $request->headers->get('User-Agent') : null;

        $attempt = new FaceLoginAttempt();
        $attempt->setIpAddress($ipAddress);
        $attempt->setUserAgent($userAgent);

        if ($bestMatch && $bestMatch['is_match']) {
            $user = $bestMatch['user'];
            $attempt->setUser($user);
            $attempt->setSuccess(true);
            $attempt->setConfidenceScore($bestMatch['similarity']);

            $this->entityManager->persist($attempt);
            $this->entityManager->flush();

            return [
                'success' => true,
                'user' => $user,
                'similarity' => $bestMatch['similarity']
            ];
        } else {
            // If we have a best match but it's below threshold, we still log it
            if ($bestMatch) {
                $attempt->setUser($bestMatch['user']);
                $attempt->setConfidenceScore($bestMatch['similarity']);
                $attempt->setErrorMessage('Face recognition failed: Similarity below threshold');
            } else {
                $attempt->setErrorMessage('Face recognition failed: No matching face found');
            }

            $attempt->setSuccess(false);
            $this->entityManager->persist($attempt);
            $this->entityManager->flush();

            return [
                'success' => false,
                'message' => 'Face recognition failed. Please try again or use your password.'
            ];
        }
    }

    /**
     * Check if a user has exceeded the maximum number of failed face login attempts
     */
    public function hasExceededFailedAttempts(User $user): bool
    {
        $repository = $this->entityManager->getRepository(FaceLoginAttempt::class);
        $failedAttempts = $repository->countRecentFailedAttempts($user, self::RATE_LIMIT_WINDOW_MINUTES);

        return $failedAttempts >= self::MAX_FAILED_ATTEMPTS;
    }

    /**
     * Check if an IP address has exceeded the rate limit for failed face login attempts
     */
    public function hasExceededRateLimit(string $ipAddress): bool
    {
        $repository = $this->entityManager->getRepository(FaceLoginAttempt::class);
        $failedAttempts = $repository->countRecentFailedAttemptsFromIp($ipAddress, self::RATE_LIMIT_WINDOW_MINUTES);

        return $failedAttempts >= self::MAX_FAILED_ATTEMPTS * 2; // Higher threshold for IP-based rate limiting
    }

    /**
     * Disable face recognition for a user
     */
    public function disableFaceRecognition(User $user): void
    {
        $user->setFaceRecognitionEnabled(false);
        $user->setFaceEmbeddings(null);

        // Delete the face photo if it exists
        if ($user->getFacePhotoPath()) {
            $photoPath = $this->params->get('kernel.project_dir') . $user->getFacePhotoPath();
            if (file_exists($photoPath)) {
                unlink($photoPath);
            }
            $user->setFacePhotoPath(null);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Save a face photo to disk
     */
    private function saveFacePhoto(User $user, string $base64Image): string
    {
        // Extract the base64 data
        if (strpos($base64Image, ';base64,') !== false) {
            list(, $base64Image) = explode(';base64,', $base64Image);
        }

        // Generate a unique filename
        $filename = 'face_' . $user->getId() . '_' . uniqid() . '.jpg';
        $filepath = $this->facePhotosDir . '/' . $filename;

        // Save the file
        file_put_contents($filepath, base64_decode($base64Image));

        // Return the relative path
        return '/var/uploads/face_photos/' . $filename;
    }
}
