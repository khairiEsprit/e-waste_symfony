<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DebugController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/debug/user', name: 'debug_user')]
    public function debugUser(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new Response('No user authenticated');
        }
        
        $roles = $user->getRoles();
        $isEmployee = $this->security->isGranted('ROLE_EMPLOYEE');
        $isAdmin = $this->security->isGranted('ROLE_ADMIN');
        $isCitoyen = $this->security->isGranted('ROLE_CITOYEN');
        
        $content = "User: " . $user->getUserIdentifier() . "<br>";
        $content .= "Roles: " . implode(', ', $roles) . "<br>";
        $content .= "Is Employee: " . ($isEmployee ? 'Yes' : 'No') . "<br>";
        $content .= "Is Admin: " . ($isAdmin ? 'Yes' : 'No') . "<br>";
        $content .= "Is Citoyen: " . ($isCitoyen ? 'Yes' : 'No') . "<br>";
        
        return new Response($content);
    }
}
