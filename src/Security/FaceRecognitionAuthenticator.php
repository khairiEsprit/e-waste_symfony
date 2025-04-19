<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class FaceRecognitionAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    private $faceRecognitionService;
    private $entityManager;
    private $urlGenerator;
    private $logger;

    public function __construct(
        FaceRecognitionService $faceRecognitionService,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
        LoggerInterface $logger
    ) {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->logger = $logger;
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/login/face' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $this->logger->info('Face recognition authentication attempt started');

        // Try to get the face image from the request content (JSON)
        $content = $request->getContent();
        $data = json_decode($content, true);

        $base64Image = $data['face_image'] ?? null;

        // If not found in JSON, try to get from request parameters
        if (empty($base64Image)) {
            $base64Image = $request->request->get('face_image');
        }

        if (empty($base64Image)) {
            $this->logger->error('Face authentication failed: No face image provided');
            throw new CustomUserMessageAuthenticationException('No face image provided.');
        }

        $this->logger->info('Face image received, checking service availability');

        // Check if the face recognition service is available
        if (!$this->faceRecognitionService->isServiceAvailable()) {
            $this->logger->error('Face authentication failed: Service unavailable');
            throw new CustomUserMessageAuthenticationException('Face recognition service is currently unavailable. Please use email/password login.');
        }

        // Check for rate limiting by IP
        $ipAddress = $request->getClientIp();
        $this->logger->info('Checking rate limit for IP: ' . $ipAddress);

        if ($this->faceRecognitionService->hasExceededRateLimit($ipAddress)) {
            $this->logger->warning('Face authentication failed: Rate limit exceeded for IP ' . $ipAddress);
            throw new CustomUserMessageAuthenticationException('Too many failed attempts. Please try again later or use email/password login.');
        }

        // Authenticate with face recognition
        $this->logger->info('Attempting face authentication with service');
        $result = $this->faceRecognitionService->authenticateWithFace($base64Image);
        $this->logger->info('Face authentication service result: ' . json_encode($result));

        if (!$result['success']) {
            $this->logger->error('Face authentication failed: ' . ($result['message'] ?? 'Unknown error'));
            throw new CustomUserMessageAuthenticationException($result['message'] ?? 'Face recognition failed. Please try again or use email/password login.');
        }

        /** @var User $user */
        $user = $result['user'];
        $this->logger->info('Face authentication successful for user: ' . $user->getUserIdentifier());

        // Check if the user is active
        if (!$user->isActive()) {
            $this->logger->warning('Face authentication failed: User account is disabled - ' . $user->getUserIdentifier());
            throw new CustomUserMessageAuthenticationException('Your account is disabled. Please contact an administrator.');
        }

        // Check if the user has exceeded the maximum number of failed attempts
        if ($this->faceRecognitionService->hasExceededFailedAttempts($user)) {
            $this->logger->warning('Face authentication failed: Too many failed attempts for user ' . $user->getUserIdentifier());
            throw new CustomUserMessageAuthenticationException('Too many failed attempts. Please try again later or use email/password login.');
        }

        $this->logger->info('Face authentication completed successfully for user: ' . $user->getUserIdentifier());

        // Create a passport with the user
        return new SelfValidatingPassport(
            new UserBadge($user->getUserIdentifier(), function () use ($user) {
                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Update the user's last login time
        $user = $token->getUser();
        if ($user instanceof User) {
            $user->setLastLogin(new \DateTime());
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        // Determine the redirect URL based on user role
        $redirectUrl = $this->urlGenerator->generate('front_home'); // Default redirect

        if ($user instanceof User) {
            if ($user->hasRole('ROLE_ADMIN')) {
                $redirectUrl = $this->urlGenerator->generate('back_dashboard');
            } elseif ($user->hasRole('ROLE_EMPLOYEE')) {
                $redirectUrl = $this->urlGenerator->generate('employee_dashboard');
            } elseif ($user->hasRole('ROLE_CITOYEN')) {
                $redirectUrl = $this->urlGenerator->generate('front_home');
            }
        }

        // For AJAX requests, return a JSON response with the redirect URL
        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode([
                'success' => true,
                'redirect' => $redirectUrl
            ]), 200, ['Content-Type' => 'application/json']);
        }

        // For regular requests, redirect directly
        return new RedirectResponse($redirectUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $this->logger->error('Face authentication failure: ' . $exception->getMessage());

        if ($request->hasSession()) {
            $request->getSession()->set('security.authentication.error', $exception);
        }

        // For AJAX requests, return a JSON response
        if ($request->isXmlHttpRequest()) {
            return new Response(json_encode([
                'success' => false,
                'message' => $exception->getMessage()
            ]), 401, ['Content-Type' => 'application/json']);
        }

        return new RedirectResponse($this->urlGenerator->generate('security_login', [
            'face_auth_error' => $exception->getMessage()
        ]));
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new RedirectResponse($this->urlGenerator->generate('security_login'));
    }
}
