<?php
namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'back_dashboard')]
    public function index(): Response
    {
        return $this->render('back/index.html.twig');
    }
}