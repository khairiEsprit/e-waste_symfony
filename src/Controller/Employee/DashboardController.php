<?php

namespace App\Controller\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/employee')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'employee_dashboard')]
    public function index(): Response
    {
        // Check if the user has the ROLE_EMPLOYEE role
        if (!$this->isGranted('ROLE_EMPLOYEE')) {
            throw new AccessDeniedException('Access denied. Employee role required.');
        }
        
        return $this->render('employee/dashboard/index.html.twig');
    }
}
