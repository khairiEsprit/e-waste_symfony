<?php
// src/Security/AuthenticationSuccessHandler.php
namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private const ROLE_REDIRECTS = [
        'ROLE_ADMIN' => 'back_dashboard',
        'ROLE_CITOYEN' => 'front_home',
        'ROLE_EMPLOYEE' => 'app_tache_index',
    ];

    private const DEFAULT_REDIRECT = 'front_home';

    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        $roles = $user->getRoles();

        // Debug information
        error_log('AuthenticationSuccessHandler: User authenticated');
        error_log('AuthenticationSuccessHandler: User identifier: ' . $user->getUserIdentifier());
        error_log('AuthenticationSuccessHandler: User roles: ' . implode(', ', $roles));

        $response = $this->createRedirectResponse($roles);
        error_log('AuthenticationSuccessHandler: Redirecting to: ' . $response->getTargetUrl());

        return $response;
    }

    private function createRedirectResponse(array $roles): RedirectResponse
    {
        // Prioritize roles in a specific order
        // Admin first, then Employee, then Citoyen
        if (in_array('ROLE_ADMIN', $roles, true)) {
            error_log('AuthenticationSuccessHandler: User has ROLE_ADMIN, redirecting to back_dashboard');
            return new RedirectResponse($this->router->generate(self::ROLE_REDIRECTS['ROLE_ADMIN']));
        }

        if (in_array('ROLE_EMPLOYEE', $roles, true)) {
            error_log('AuthenticationSuccessHandler: User has ROLE_EMPLOYEE, redirecting to app_tache_index');
            return new RedirectResponse($this->router->generate(self::ROLE_REDIRECTS['ROLE_EMPLOYEE']));
        }

        if (in_array('ROLE_CITOYEN', $roles, true)) {
            error_log('AuthenticationSuccessHandler: User has ROLE_CITOYEN, redirecting to front_home');
            return new RedirectResponse($this->router->generate(self::ROLE_REDIRECTS['ROLE_CITOYEN']));
        }

        error_log('AuthenticationSuccessHandler: User has no specific role, redirecting to default');
        return new RedirectResponse($this->router->generate(self::DEFAULT_REDIRECT));
    }
}