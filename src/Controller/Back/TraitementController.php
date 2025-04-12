<?php

namespace App\Controller\Back;

use App\Entity\Traitement;
use App\Form\TraitementType;
use App\Repository\DemandeRepository;
use App\Repository\TraitementRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/traitement')]
final class TraitementController extends AbstractController
{
    #[Route('/new/{demandeId}', name: 'app_traitement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, DemandeRepository $demandeRepository, int $demandeId): Response
    {
        // Fetch the Demande by ID
        $demande = $demandeRepository->find($demandeId);

        // If the Demande does not exist
        if (!$demande) {
            throw $this->createNotFoundException('Demande not found');
        }

        // Create a new Traitement instance
        $traitement = new Traitement();
        $traitement->setStatus('Traité');
        $traitement->setDateTraitement(new \DateTime());
        $traitement->setCommentaire('Demande traitée avec succès');
        $traitement->setDemande($demande);

        // Persist the Traitement
        $entityManager->persist($traitement);
        $entityManager->flush();

        // Optionally, update the Demande's state if needed
        // $demande->setEtat('Traité');  // This will be updated if you want the Demande's state to be modified.

        // Redirect back to the Demande index page
        return $this->redirectToRoute('app_demande_indexback');
    }
    
    #[Route(name: 'app_traitement_index', methods: ['GET'])]
    public function index(TraitementRepository $traitementRepository): Response
    {
        return $this->render('back/traitement/index.html.twig', [
            'traitements' => $traitementRepository->findAll(),
        ]);
    }


    #[Route('/{id}', name: 'app_traitement_show', methods: ['GET'])]
    public function show(Traitement $traitement): Response
    {
        return $this->render('back/traitement/show.html.twig', [
            'traitement' => $traitement,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_traitement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/traitement/edit.html.twig', [
            'traitement' => $traitement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_traitement_delete', methods: ['POST'])]
    public function delete(Request $request, Traitement $traitement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$traitement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($traitement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_traitement_index', [], Response::HTTP_SEE_OTHER);
    }
}