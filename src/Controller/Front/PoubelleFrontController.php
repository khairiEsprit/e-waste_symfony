<?php

namespace App\Controller\Front;

use App\Repository\PoubelleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PoubelleFrontController extends AbstractController
{
    #[Route('/poubelles', name: 'app_poubelles')]
    public function index(PoubelleRepository $poubelleRepository): Response
    {
        $centres = $poubelleRepository->findAllCentres();
        
        return $this->render('front/poubelle/index.html.twig', [
            'centres' => $centres,
        ]);
    }

    #[Route('/front/poubelles/search', name: 'app_poubelles_search')]
    public function search(Request $request, PoubelleRepository $poubelleRepository): Response
    {
        $search = $request->query->get('search');
        $etat = $request->query->get('etat');
        $centre = $request->query->get('centre');
        
        // Débogage des paramètres reçus
        $this->addFlash('debug', "Search: $search, Etat: $etat, Centre: $centre");
        
        $poubelles = $poubelleRepository->search($search, $etat, $centre);
        
        // Débogage du nombre de résultats
        $count = count($poubelles);
        $this->addFlash('debug', "Résultats trouvés: $count");
        
        return $this->render('front/poubelle/_search_results.html.twig', [
            'poubelles' => $poubelles,
        ]);
    }
}