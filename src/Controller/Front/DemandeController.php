<?php
namespace App\Controller\Front;

use App\Entity\Demande;
use App\Entity\Traitement;
use App\Entity\User;
use App\Entity\Center;
use App\Form\DemandeType;
use App\Repository\DemandeRepository;
use App\Repository\UserRepository;
use App\Repository\CenterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/demande')]
final class DemandeController extends AbstractController
{
    // Action pour traiter une demande
#[Route('/{id}/traiter', name: 'app_demande_traiter', methods: ['POST'])]
public function traiter(Demande $demande, Request $request, EntityManagerInterface $entityManager): Response
{
    // Récupère le commentaire depuis le formulaire
    $commentaire = $request->request->get('commentaire');

    // Crée un nouvel objet Traitement
    $traitement = new Traitement();
    $traitement->setStatus('Traité');
    $traitement->setDateTraitement(new \DateTime()); // current date and time
    $traitement->setCommentaire($commentaire);
    $traitement->setDemande($demande); // associate with the given Demande

    // Sauvegarder le traitement dans la base de données
    $entityManager->persist($traitement);
    $entityManager->flush();

    // Rediriger vers la liste des demandes (back office)
    return $this->redirectToRoute('app_demande_indexback');
}

// Action pour refuser une demande
#[Route('/{id}/refuser', name: 'app_demande_refuser', methods: ['POST'])]
public function refuser(Demande $demande, Request $request, EntityManagerInterface $entityManager): Response
{
    $commentaire = $request->request->get('commentaire');

    // Automatically create a Traitement entry
    $traitement = new Traitement();
    $traitement->setStatus('Refusé');
    $traitement->setDateTraitement(new \DateTime()); // current date and time
    $traitement->setCommentaire($commentaire);
    $traitement->setDemande($demande); // associate with the given Demande

    // Persist the traitement entry
    $entityManager->persist($traitement);
    $entityManager->flush();

    // Redirect back to the Demande listing page
    return $this->redirectToRoute('app_demande_indexback');
}


    // Affiche la liste des demandes (front)
    #[Route(name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('front/demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }


// Affiche la liste des demandes (back office)
    #[Route(path: '/back', name: 'app_demande_indexback', methods: ['GET'])]
    public function indexBack(DemandeRepository $demandeRepository): Response
    {
        return $this->render('back/demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }


    
// Créer une nouvelle demande
    #[Route('/new', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, CenterRepository $centerRepository): Response
    {
        $demande = new Demande();

        // Fetch the user and center with id = 1
        $user = $userRepository->find(1);
        $center = $centerRepository->find(1);
// Crée le formulaire de demande
        $form = $this->createForm(DemandeType::class, $demande, [
            'current_user' => $user,
            'current_center' => $center,
        ]);
        $form->handleRequest($request);
// Si le formulaire est soumis et valide, enregistrer la demande
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the user and center for the demande
            $demande->setUtilisateur($user);
            $demande->setCenter($center);

            $entityManager->persist($demande);
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/new.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }
// Affiche les détails d'une demande
    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('front/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }
// Modifier une demande existante
    #[Route('/{id}/edit', name: 'app_demande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(DemandeType::class, $demande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('front/demande/edit.html.twig', [
            'demande' => $demande,
            'form' => $form->createView(),
        ]);
    }
// Supprimer une demande
    #[Route('/{id}', name: 'app_demande_delete', methods: ['POST'])]
    public function delete(Request $request, Demande $demande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$demande->getId(), $request->get('_token'))) {
            $entityManager->remove($demande);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_demande_index', [], Response::HTTP_SEE_OTHER);
    }
}
