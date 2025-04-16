<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class SecurityRedirectListener implements EventSubscriberInterface
{
    private RouterInterface $router;
    private TokenStorageInterface $tokenStorage;
    private Security $security;

    public function __construct(
        RouterInterface $router,
        TokenStorageInterface $tokenStorage,
        Security $security
    ) {
        $this->router = $router;
        $this->tokenStorage = $tokenStorage;
        $this->security = $security;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10], // Higher priority to run before other listeners
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $path = $request->getPathInfo();

        // Skip for public routes and assets
        if (
            $this->isPublicRoute($path) ||
            $this->isAssetRoute($path) ||
            $request->isXmlHttpRequest()
        ) {
            return;
        }

        // Check if user is authenticated
        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $event->setResponse(new RedirectResponse($this->router->generate('security_login')));
            return;
        }

        // Role-based access control
        try {
            $this->checkRoleAccess($path);
        } catch (AccessDeniedException $e) {
            // Redirect to appropriate page based on user role
            $redirectRoute = $this->getRedirectRouteByRole();
            $event->setResponse(new RedirectResponse($this->router->generate($redirectRoute)));
        }
    }

    private function isPublicRoute(string $path): bool
    {
        $publicRoutes = [
            '/auth/login',
            '/auth/register',
            '/auth/resetting',
            '/login',
            '/',
        ];

        foreach ($publicRoutes as $route) {
            if (strpos($path, $route) === 0) {
                return true;
            }
        }

        return false;
    }

    private function isAssetRoute(string $path): bool
    {
        $assetPaths = [
            '/assets/',
            '/bundles/',
            '/css/',
            '/js/',
            '/images/',
            '/_profiler/',
            '/_wdt/',
        ];

        foreach ($assetPaths as $assetPath) {
            if (strpos($path, $assetPath) === 0) {
                return true;
            }
        }

        return false;
    }

    private function checkRoleAccess(string $path): void
    {
        if (strpos($path, '/admin') === 0 || strpos($path, '/back') === 0) {
            if (!$this->security->isGranted('ROLE_ADMIN')) {
                throw new AccessDeniedException('Access denied. Admin role required.');
            }
        } elseif (strpos($path, '/tache') === 0) {
            if (!$this->security->isGranted('ROLE_EMPLOYEE')) {
                throw new AccessDeniedException('Access denied. Employee role required.');
            }
            elseif (strpos($path, '/plannificationtache') === 0) {
                if (!$this->security->isGranted('ROLE_EMPLOYEE')) {
                    throw new AccessDeniedException('Access denied. Employee role required.');
                }
            }
        } elseif (strpos($path, '/front') === 0 || strpos($path, '/profile') === 0) {
            if (!$this->security->isGranted('ROLE_CITOYEN') && 
                !$this->security->isGranted('ROLE_ADMIN') && 
                !$this->security->isGranted('ROLE_EMPLOYEE')) {
                throw new AccessDeniedException('Access denied. User role required.');
            }
        }
    }

    private function getRedirectRouteByRole(): string
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return 'back_dashboard';
        } elseif ($this->security->isGranted('ROLE_EMPLOYEE')) {
            return 'employee_dashboard';
        } elseif ($this->security->isGranted('ROLE_CITOYEN')) {
            return 'front_home';
        }

        return 'security_login';
    }
}
