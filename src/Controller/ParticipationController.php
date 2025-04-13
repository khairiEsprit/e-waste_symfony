<?php

namespace App\Controller;

use App\Entity\Participation;
use App\Form\ParticipationType;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/participation')]
class ParticipationController extends AbstractController
{
    #[Route('/', name: 'app_participation_index', methods: ['GET'])]
    public function index(Request $request, ParticipationRepository $repository): Response
    {
        $searchTerm = trim($request->query->get('search', ''));

        if (!empty($searchTerm)) {
            try {
                $participations = $repository->search($searchTerm);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de la recherche');
                $participations = $repository->findAll();
            }
        } else {
            $participations = $repository->findAll();
        }

        return $this->render('participation/index.html.twig', [
            'participations' => $participations,
            'search_term' => $searchTerm
        ]);
    }

    #[Route('/new', name: 'app_participation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $participation = new Participation();
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification de l'unicité du téléphone et de l'email
            $existingParticipation = $entityManager
                ->getRepository(Participation::class)
                ->findOneBy(['email' => $participation->getEmail()]);

            $existingPhone = $entityManager
                ->getRepository(Participation::class)
                ->findOneBy(['phone' => $participation->getPhone()]);

            if ($existingParticipation) {
                $form->get('email')->addError(new FormError('Cet email est déjà utilisé.'));
            }

            if ($existingPhone) {
                $form->get('phone')->addError(new FormError('Ce numéro de téléphone est déjà utilisé.'));
            }

            if (!$existingParticipation && !$existingPhone) {
                $entityManager->persist($participation);
                $entityManager->flush();

                $this->addFlash('success', 'Votre participation a été enregistrée avec succès!');
                return $this->redirectToRoute('app_participation_index');
            }
        }

        return $this->render('participation/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_participation_show', methods: ['GET'])]
    public function show(Participation $participation): Response
    {
        return $this->render('participation/show.html.twig', [
            'participation' => $participation,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_participation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ParticipationType::class, $participation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérification des doublons seulement si email ou phone ont changé
            $emailChanged = $participation->getEmail() !== $form->get('email')->getData();
            $phoneChanged = $participation->getPhone() !== $form->get('phone')->getData();

            $hasError = false;

            if ($emailChanged) {
                $existingEmail = $entityManager
                    ->getRepository(Participation::class)
                    ->findOneBy(['email' => $participation->getEmail()]);

                if ($existingEmail && $existingEmail->getId() !== $participation->getId()) {
                    $form->get('email')->addError(new \Symfony\Component\Form\FormError('Cet email est déjà utilisé.'));
                    $hasError = true;
                }
            }

            if ($phoneChanged) {
                $existingPhone = $entityManager
                    ->getRepository(Participation::class)
                    ->findOneBy(['phone' => $participation->getPhone()]);

                if ($existingPhone && $existingPhone->getId() !== $participation->getId()) {
                    $form->get('phone')->addError(new \Symfony\Component\Form\FormError('Ce numéro de téléphone est déjà utilisé.'));
                    $hasError = true;
                }
            }

            if (!$hasError) {
                $entityManager->flush();
                $this->addFlash('success', 'La participation a été mise à jour avec succès.');
                return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
            }
        }

        return $this->render('participation/edit.html.twig', [
            'participation' => $participation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_participation_delete', methods: ['POST'])]
    public function delete(Request $request, Participation $participation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$participation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($participation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_participation_index', [], Response::HTTP_SEE_OTHER);
    }
}