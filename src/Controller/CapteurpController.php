<?php

namespace App\Controller;

use App\Entity\Capteurp;
use App\Form\CapteurpType;
use App\Repository\CapteurpRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/capteurp')]
final class CapteurpController extends AbstractController
{
    #[Route(name: 'app_capteurp_index', methods: ['GET'])]
    public function index(CapteurpRepository $capteurpRepository): Response
    {
        return $this->render('capteurp/index.html.twig', [
            'capteurps' => $capteurpRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_capteurp_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $capteurp = new Capteurp();
        $form = $this->createForm(CapteurpType::class, $capteurp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($capteurp);
            $entityManager->flush();

            return $this->redirectToRoute('app_capteurp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('capteurp/new.html.twig', [
            'capteurp' => $capteurp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_capteurp_show', methods: ['GET'])]
    public function show(Capteurp $capteurp): Response
    {
        return $this->render('capteurp/show.html.twig', [
            'capteurp' => $capteurp,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_capteurp_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Capteurp $capteurp, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CapteurpType::class, $capteurp);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_capteurp_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('capteurp/edit.html.twig', [
            'capteurp' => $capteurp,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_capteurp_delete', methods: ['POST'])]
    public function delete(Request $request, Capteurp $capteurp, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$capteurp->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($capteurp);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_capteurp_index', [], Response::HTTP_SEE_OTHER);
    }
}
