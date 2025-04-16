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
    public function index(CentreRepository $centreRepository): Response
    {
        return $this->render('back/centre/index.html.twig', [
            'centres' => $centreRepository->findAll(),
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
        if ($this->isCsrfTokenValid('delete'.$centre->getId(), $request->get('_token'))) {
            $entityManager->remove($centre);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_centre_index', [], Response::HTTP_SEE_OTHER);
    }
}
