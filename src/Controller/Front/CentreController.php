<?php

namespace App\Controller\Front;

use App\Entity\Centre;
use App\Repository\CentreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CentreController extends AbstractController
{
    #[Route('/centres', name: 'app_centreFront')]
    public function index(CentreRepository $centreRepository): Response
    {
        $centres = $centreRepository->findAll();
        
        return $this->render('front/centres/index.html.twig', [
            'centres' => $centres,
        ]);
    }

    #[Route('/centres/{id}', name: 'app_centre_detailsFront')]
    #[ParamConverter('centre', class: Centre::class)]
    public function details(?Centre $centre): Response
    {
        if (!$centre) {
            throw $this->createNotFoundException('Centre not found');
        }

        return $this->render('front/centres/details.html.twig', [
            'centre' => $centre,
        ]);
    }
}