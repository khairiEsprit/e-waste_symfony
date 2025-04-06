<?php
namespace App\Controller;

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
    #[Route('/{id}/traiter', name: 'app_demande_traiter', methods: ['POST'])]
    public function traiter(Demande $demande, Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = $request->request->get('commentaire');

        // Automatically create a Traitement entry
        $traitement = new Traitement();
        $traitement->setStatus('Traité');
        $traitement->setDateTraitement(new \DateTime()); // current date and time
        $traitement->setCommentaire($commentaire);
        $traitement->setDemande($demande); // associate with the given Demande

        // Persist the traitement entry
        $entityManager->persist($traitement);
        $entityManager->flush();

        // Update the Demande's type to "Traité"
        $demande->setType('Traité');
        $entityManager->flush();

        // Redirect back to the Demande listing page
        return $this->redirectToRoute('app_demande_indexback');
    }

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

        // Update the Demande's type to "Refusé"
        $demande->setType('Refusé');
        $entityManager->flush();

        // Redirect back to the Demande listing page
        return $this->redirectToRoute('app_demande_indexback');
    }

    #[Route(name: 'app_demande_index', methods: ['GET'])]
    public function index(DemandeRepository $demandeRepository): Response
    {
        return $this->render('front/demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }

    #[Route(path: '/back', name: 'app_demande_indexback', methods: ['GET'])]
    public function indexBack(DemandeRepository $demandeRepository): Response
    {
        return $this->render('back/demande/index.html.twig', [
            'demandes' => $demandeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_demande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, CenterRepository $centerRepository): Response
    {
        $demande = new Demande();

        // Fetch the user and center with id = 1
        $user = $userRepository->find(1);
        $center = $centerRepository->find(1);

        $form = $this->createForm(DemandeType::class, $demande, [
            'current_user' => $user,
            'current_center' => $center,
        ]);
        $form->handleRequest($request);

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

    #[Route('/{id}', name: 'app_demande_show', methods: ['GET'])]
    public function show(Demande $demande): Response
    {
        return $this->render('front/demande/show.html.twig', [
            'demande' => $demande,
        ]);
    }

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
