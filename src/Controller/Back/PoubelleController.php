<?php

namespace App\Controller\Back;

use App\Entity\Poubelle;
use App\Entity\Historique;
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
        return $this->render('back/poubelle/index.html.twig', [
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

        return $this->render('back/poubelle/new.html.twig', [
            'poubelle' => $poubelle,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_poubelle_show', methods: ['GET'])]
    public function show(Poubelle $poubelle, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'historique trié par date (du plus récent au plus ancien)
        $historiqueRepository = $entityManager->getRepository(Historique::class);
        $historique = $historiqueRepository->findBy(
            ['poubelle' => $poubelle],
            ['date_evenement' => 'DESC']
        );
        
        return $this->render('back/poubelle/show.html.twig', [
            'poubelle' => $poubelle,
            'historique' => $historique,
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

        return $this->render('back/poubelle/edit.html.twig', [
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

    #[Route('/front', name: 'app_poubelle_front', methods: ['GET'])]
    public function front(PoubelleRepository $poubelleRepository): Response
    {
        return $this->render('front/poubelle/index.html.twig', [
            'poubelles' => $poubelleRepository->findAll(),
        ]);
    }
}
