<?php

namespace App\Controller;

use App\Entity\Poubelle;
use App\Form\PoubelleType;
use App\Repository\PoubelleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/poubelle')]
final class PoubelleController extends AbstractController
{
    #[Route(name: 'app_poubelle_index', methods: ['GET'])]
    public function index(PoubelleRepository $poubelleRepository): Response
    {
        return $this->render('poubelle/index.html.twig', [
            'poubelles' => $poubelleRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_poubelle_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $poubelle = new Poubelle();
        $form = $this->createForm(PoubelleType::class, $poubelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($poubelle);
            $entityManager->flush();

            return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('poubelle/new.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poubelle_show', methods: ['GET'])]
    public function show(Poubelle $poubelle): Response
    {
        return $this->render('poubelle/show.html.twig', [
            'poubelle' => $poubelle,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_poubelle_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Poubelle $poubelle, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PoubelleType::class, $poubelle);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('poubelle/edit.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poubelle_delete', methods: ['POST'])]
    public function delete(Request $request, Poubelle $poubelle, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$poubelle->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($poubelle);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_poubelle_index', [], Response::HTTP_SEE_OTHER);
    }
}
