<?php

namespace App\Controller;

use App\Entity\Plannificationtache;
use App\Form\PlannificationtacheType;
use App\Repository\PlannificationtacheRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plannificationtache')]
final class PlannificationtacheController extends AbstractController
{
    #[Route(name: 'app_plannificationtache_index', methods: ['GET'])]
    public function index(PlannificationtacheRepository $plannificationtacheRepository): Response
    {
        return $this->render('back/plannificationtache/index.html.twig', [
            'plannificationtaches' => $plannificationtacheRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_plannificationtache_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plannificationtache = new Plannificationtache();
        $form = $this->createForm(PlannificationtacheType::class, $plannificationtache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($plannificationtache);
            $entityManager->flush();

            return $this->redirectToRoute('app_plannificationtache_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/plannificationtache/new.html.twig', [
            'plannificationtache' => $plannificationtache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plannificationtache_show', methods: ['GET'])]
    public function show(Plannificationtache $plannificationtache): Response
    {
        return $this->render('back/plannificationtache/show.html.twig', [
            'plannificationtache' => $plannificationtache,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plannificationtache_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plannificationtache $plannificationtache, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlannificationtacheType::class, $plannificationtache);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_plannificationtache_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('back/plannificationtache/edit.html.twig', [
            'plannificationtache' => $plannificationtache,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plannificationtache_delete', methods: ['POST'])]
    public function delete(Request $request, Plannificationtache $plannificationtache, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plannificationtache->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plannificationtache);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plannificationtache_index', [], Response::HTTP_SEE_OTHER);
    }
}
