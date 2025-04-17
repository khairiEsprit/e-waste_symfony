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
        $message = 'Access Denied'; // Default message

        // Get the requested path to provide more specific error messages
        $path = $request->getPathInfo();

        if ($this->security->isGranted('ROLE_ADMIN')) {
            $returnRoute = 'back_dashboard';
        } elseif ($this->security->isGranted('ROLE_EMPLOYEE')) {
            $returnRoute = 'app_tache_index';

            // If employee tries to access admin area
            if (str_starts_with($path, '/admin') || str_starts_with($path, '/back')) {
                $message = 'You do not have admin privileges to access this area.';
            }
        } elseif ($this->security->isGranted('ROLE_CITOYEN')) {
            $returnRoute = 'front_home';

            // If citizen tries to access admin or employee area
            if (str_starts_with($path, '/admin') || str_starts_with($path, '/back')) {
                $message = 'You do not have admin privileges to access this area.';
            } elseif (str_starts_with($path, '/tache') || str_starts_with($path, '/plannificationtache')) {
                $message = 'You do not have employee privileges to access this area.';
            }
        } else {
            // Not logged in or unknown role
            $returnRoute = 'security_login';
            $message = 'You must be logged in to access this area.';
        }

        return new Response(
            $this->twig->render('security/access_denied.html.twig', [
                'exception' => $accessDeniedException,
                'status_code' => Response::HTTP_FORBIDDEN,
                'return_route' => $returnRoute,
                'message' => $message
            ]),
            Response::HTTP_FORBIDDEN
        );
    }
}
