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
        'ROLE_EMPLOYEE' => 'employee_dashboard',
    ];

    private const DEFAULT_REDIRECT = 'web_home';

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

        return $this->createRedirectResponse($roles);
    }

    private function createRedirectResponse(array $roles): RedirectResponse
    {
        foreach (self::ROLE_REDIRECTS as $role => $route) {
            if (in_array($role, $roles, true)) {
                return new RedirectResponse($this->router->generate($route));
            }
        }

        return new RedirectResponse($this->router->generate(self::DEFAULT_REDIRECT));
    }
}