<?php

namespace App\Security;

use App\Entity\User;
use App\Service\FaceRecognitionService;
use Doctrine\ORM\EntityManagerInterface;
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

    public function __construct(
        FaceRecognitionService $faceRecognitionService,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->faceRecognitionService = $faceRecognitionService;
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/login/face' && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        $base64Image = $request->request->get('face_image');
        if (empty($base64Image)) {
            throw new CustomUserMessageAuthenticationException('No face image provided.');
        }

        // Check if the face recognition service is available
        if (!$this->faceRecognitionService->isServiceAvailable()) {
            throw new CustomUserMessageAuthenticationException('Face recognition service is currently unavailable. Please use email/password login.');
        }

        // Check for rate limiting by IP
        $ipAddress = $request->getClientIp();
        if ($this->faceRecognitionService->hasExceededRateLimit($ipAddress)) {
            throw new CustomUserMessageAuthenticationException('Too many failed attempts. Please try again later or use email/password login.');
        }

        // Authenticate with face recognition
        $result = $this->faceRecognitionService->authenticateWithFace($base64Image);

        if (!$result['success']) {
            throw new CustomUserMessageAuthenticationException($result['message'] ?? 'Face recognition failed. Please try again or use email/password login.');
        }

        /** @var User $user */
        $user = $result['user'];

        // Check if the user is active
        if (!$user->isActive()) {
            throw new CustomUserMessageAuthenticationException('Your account is disabled. Please contact an administrator.');
        }

        // Check if the user has exceeded the maximum number of failed attempts
        if ($this->faceRecognitionService->hasExceededFailedAttempts($user)) {
            throw new CustomUserMessageAuthenticationException('Too many failed attempts. Please try again later or use email/password login.');
        }

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

        // Redirect based on user role
        if ($user instanceof User) {
            if ($user->hasRole('ROLE_ADMIN')) {
                return new RedirectResponse($this->urlGenerator->generate('back_dashboard'));
            } elseif ($user->hasRole('ROLE_EMPLOYEE')) {
                return new RedirectResponse($this->urlGenerator->generate('employee_dashboard'));
            } elseif ($user->hasRole('ROLE_CITOYEN')) {
                return new RedirectResponse($this->urlGenerator->generate('front_home'));
            }
        }

        // Default redirect if no specific role matched
        return new RedirectResponse($this->urlGenerator->generate('front_home'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->hasSession()) {
            $request->getSession()->set('security.authentication.error', $exception);
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
