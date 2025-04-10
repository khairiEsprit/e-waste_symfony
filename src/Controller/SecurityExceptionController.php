<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;
use Twig\Environment;

class SecurityExceptionController extends AbstractController implements AccessDeniedHandlerInterface
{
    private $twig;
    private $security;

    public function __construct(Environment $twig, Security $security)
    {
        $this->twig = $twig;
        $this->security = $security;
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): ?Response
    {
        // Determine the appropriate return route based on user's role
        $returnRoute = 'front_home'; // Default route
        
        if ($this->security->isGranted('ROLE_ADMIN')) {
            $returnRoute = 'back_dashboard';
        } elseif ($this->security->isGranted('ROLE_EMPLOYEE')) {
            $returnRoute = 'employee_dashboard';
        } elseif ($this->security->isGranted('ROLE_CITOYEN')) {
            $returnRoute = 'front_home';
        }

        return new Response(
            $this->twig->render('security/access_denied.html.twig', [
                'exception' => $accessDeniedException,
                'status_code' => Response::HTTP_FORBIDDEN,
                'return_route' => $returnRoute
            ]),
            Response::HTTP_FORBIDDEN
        );
    }
}
