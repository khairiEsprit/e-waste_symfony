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
    public function index(Request $request, PoubelleRepository $poubelleRepository): Response
    {
        try {
            $centres = $poubelleRepository->findAllCentres();
            $poubelles = $poubelleRepository->findAll();

            // Si c'est une requête AJAX, retourner seulement les résultats
            if ($request->isXmlHttpRequest() || $request->query->has('search') || $request->query->has('etat') || $request->query->has('centre')) {
                // Filtrer les poubelles si des paramètres de recherche sont fournis
                $search = $request->query->get('search');
                $etat = $request->query->get('etat');
                $centre = $request->query->get('centre');

                if ($search || $etat || $centre) {
                    $poubelles = $poubelleRepository->search($search, $etat, $centre);
                }

                return $this->render('front/poubelle/_search_results.html.twig', [
                    'poubelles' => $poubelles,
                ]);
            }

            // Sinon, retourner la page complète
            return $this->render('front/poubelle/index.html.twig', [
                'centres' => $centres,
                'poubelles' => $poubelles,
            ]);
        } catch (\Exception $e) {
            // Log l'erreur
            $this->addFlash('error', 'Une erreur est survenue: ' . $e->getMessage());

            if ($request->isXmlHttpRequest()) {
                return $this->render('front/poubelle/_search_results.html.twig', [
                    'error' => $e->getMessage()
                ]);
            }

            return $this->render('front/poubelle/index.html.twig', [
                'centres' => [],
                'error' => $e->getMessage()
            ]);
        }
    }

    #[Route('/front/poubelles/search', name: 'app_poubelles_search')]
    public function search(Request $request): Response
    {
        // Pour la compatibilité avec le code existant, rediriger vers la route principale
        return $this->redirectToRoute('app_poubelles', $request->query->all());
    }
}