<?php

namespace App\Controller;

use App\Entity\Capteur;
use App\Form\CapteurType;
use App\Repository\CapteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/capteur')]
final class CapteurController extends AbstractController
{
    #[Route(name: 'app_capteur_index', methods: ['GET'])]
    public function index(CapteurRepository $capteurRepository): Response
    {
        return $this->render('back/capteur/index.html.twig', [
            'capteurs' => $capteurRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_capteur_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $capteur = new Capteur();
        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($capteur);
            $entityManager->flush();

            return $this->redirectToRoute('app_capteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/capteur/new.html.twig', [
            'capteur' => $capteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id_c}', name: 'app_capteur_show', methods: ['GET'])]
    public function show(Capteur $capteur): Response
    {
        return $this->render('back/capteur/show.html.twig', [
            'capteur' => $capteur,
        ]);
    }

    #[Route('/{id_c}/edit', name: 'app_capteur_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Capteur $capteur, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CapteurType::class, $capteur);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_capteur_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/capteur/edit.html.twig', [
            'capteur' => $capteur,
            'form' => $form,
        ]);
    }

    #[Route('/{id_c}', name: 'app_capteur_delete', methods: ['POST'])]
    public function delete(Request $request, Capteur $capteur, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$capteur->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($capteur);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_capteur_index', [], Response::HTTP_SEE_OTHER);
    }
}
