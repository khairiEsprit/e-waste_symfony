<?php

namespace App\Controller\Back;

use App\Entity\Centre;
use App\Form\CentreType;
use App\Repository\CentreRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/centre')]
final class CentreController extends AbstractController
{
    #[Route(name: 'app_centre_index', methods: ['GET'])]
    public function index(Request $request, CentreRepository $centreRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $regionFilter = $request->query->get('region', '');

        // Default Tunisia map boundaries
        $minLongitude = 7.5;
        $maxLongitude = 11.6;
        $minLatitude = 30.2;
        $maxLatitude = 37.5;

        // Apply region filter if selected
        if ($regionFilter) {
            // Define region boundaries (simplified for example)
            $regions = [
                'north' => ['minLong' => 8.0, 'maxLong' => 11.0, 'minLat' => 36.0, 'maxLat' => 37.5],
                'central' => ['minLong' => 8.0, 'maxLong' => 11.0, 'minLat' => 34.0, 'maxLat' => 36.0],
                'south' => ['minLong' => 7.5, 'maxLong' => 11.6, 'minLat' => 30.2, 'maxLat' => 34.0],
                'coastal' => ['minLong' => 10.0, 'maxLong' => 11.6, 'minLat' => 32.0, 'maxLat' => 37.5],
                'inland' => ['minLong' => 7.5, 'maxLong' => 10.0, 'minLat' => 32.0, 'maxLat' => 37.5],
            ];

            if (isset($regions[$regionFilter])) {
                $region = $regions[$regionFilter];
                $minLongitude = $region['minLong'];
                $maxLongitude = $region['maxLong'];
                $minLatitude = $region['minLat'];
                $maxLatitude = $region['maxLat'];

                $centres = $centreRepository->filterByLocation(
                    $minLongitude,
                    $maxLongitude,
                    $minLatitude,
                    $maxLatitude
                );
            } else {
                $centres = $centreRepository->findAll();
            }
        } else if ($searchTerm) {
            // Search by term if provided
            $centres = $centreRepository->searchByTerm($searchTerm);
        } else {
            // Default: get all centres
            $centres = $centreRepository->findAll();
        }

        // Handle AJAX requests for DataTables
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'data' => array_map(function ($centre) {
                    return [
                        'id' => $centre->getId(),
                        'nom' => $centre->getNom(),
                        'coordinates' => [
                            'longitude' => $centre->getLongitude(),
                            'latitude' => $centre->getAltitude()
                        ],
                        'contact' => [
                            'telephone' => $centre->getTelephone(),
                            'email' => $centre->getEmail()
                        ],
                        'actions' => $this->renderView('back/centre/_row_actions.html.twig', [
                            'centre' => $centre
                        ])
                    ];
                }, $centres)
            ]);
        }

        return $this->render('back/centre/index.html.twig', [
            'centres' => $centres,
            'searchTerm' => $searchTerm,
            'regionFilter' => $regionFilter
        ]);
    }

    #[Route('/new', name: 'app_centre_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $centre = new Centre();
        $form = $this->createForm(CentreType::class, $centre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validation manuelle
            $errors = $validator->validate($centre);

            if (count($errors) > 0) {
                return $this->render('back/centre/new.html.twig', [
                    'centre' => $centre,
                    'form' => $form,
                    'errors' => $errors,  // Affiche les erreurs dans la vue
                ]);
            }

            if ($form->isValid()) {
                $entityManager->persist($centre);
                $entityManager->flush();

                return $this->redirectToRoute('app_centre_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('back/centre/new.html.twig', [
            'centre' => $centre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_centre_show', methods: ['GET'])]
    public function show(Centre $centre): Response
    {
        return $this->render('back/centre/show.html.twig', [
            'centre' => $centre,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_centre_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Centre $centre, EntityManagerInterface $entityManager, ValidatorInterface $validator): Response
    {
        $form = $this->createForm(CentreType::class, $centre);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Validation manuelle
            $errors = $validator->validate($centre);

            if (count($errors) > 0) {
                return $this->render('back/centre/edit.html.twig', [
                    'centre' => $centre,
                    'form' => $form,
                    'errors' => $errors,  // Affiche les erreurs dans la vue
                ]);
            }

            if ($form->isValid()) {
                $entityManager->flush();

                return $this->redirectToRoute('app_centre_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('back/centre/edit.html.twig', [
            'centre' => $centre,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_centre_delete', methods: ['POST'])]
    public function delete(Request $request, Centre $centre, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $centre->getId(), $request->get('_token'))) {
            $entityManager->remove($centre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_centre_index', [], Response::HTTP_SEE_OTHER);
    }
}
