<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/avis')]
class AvisController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    #[Route('/', name: 'app_avis_index', methods: ['GET'])]
    public function index(Request $request, AvisRepository $avisRepository): Response
    {
        $searchTerm = $request->query->get('search');
        $avis = $searchTerm
            ? $avisRepository->findByNom($searchTerm)
            : $avisRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('avis/index.html.twig', [
            'avis' => $avis,
            'average_rating' => $avisRepository->getAverageRating(),
            'search_term' => $searchTerm
        ]);
    }

    #[Route('/new', name: 'app_avis_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $avi = new Avis();
        // Initialize createdAt to current date/time
        $avi->setCreatedAt(new \DateTime());
        
        $form = $this->createForm(AvisType::class, $avi, [
            'validation_groups' => ['create']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Debug form data
            $formData = $request->request->all();
            
            // Get the note value directly from the request if it exists
            if (isset($formData['avis']) && isset($formData['avis']['note']) && !empty($formData['avis']['note'])) {
                $noteValue = (int) $formData['avis']['note'];
                if ($noteValue >= 1 && $noteValue <= 5) {
                    $avi->setNote($noteValue);
                }
            }
            
            // Ensure note field is set
            if (!$avi->getNote()) {
                $form->get('note')->addError(new \Symfony\Component\Form\FormError('Veuillez sélectionner une note'));
            }
            
            // Validation manuelle pour vérifier l'unicité
            $errors = $this->validator->validate($avi, null, ['create']);
            
            if ($form->isValid() && count($errors) === 0) {
                try {
                    $this->entityManager->persist($avi);
                    $this->entityManager->flush();
                    
                    $this->addFlash('success', 'Votre avis a été publié avec succès !');
                    
                    // If it's an AJAX request, return a JSON response
                    if ($request->isXmlHttpRequest()) {
                        return $this->json([
                            'success' => true,
                            'message' => 'Votre avis a été publié avec succès !',
                            'redirect' => $this->generateUrl('app_avis_index')
                        ]);
                    }
                    
                    return $this->redirectToRoute('app_avis_index');
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage());
                    
                    // If it's an AJAX request, return a JSON response
                    if ($request->isXmlHttpRequest()) {
                        return $this->json([
                            'success' => false,
                            'message' => 'Une erreur est survenue lors de l\'enregistrement : ' . $e->getMessage()
                        ], 500);
                    }
                }
            }

            foreach ($errors as $error) {
                $this->addFlash('error', $error->getMessage());
            }
            
            // If it's an AJAX request, return form errors as JSON
            if ($request->isXmlHttpRequest()) {
                $formErrors = [];
                foreach ($form->getErrors(true) as $error) {
                    $formErrors[$error->getOrigin()->getName()] = $error->getMessage();
                }
                
                return $this->json([
                    'success' => false,
                    'errors' => $formErrors
                ], 400);
            }
        }

        return $this->render('avis/new.html.twig', [
            'form' => $form->createView(),
            'avi' => $avi
        ]);
    }

    #[Route('/{id}', name: 'app_avis_show', methods: ['GET'])]
    public function show(Avis $avi): Response
    {
        return $this->render('avis/show.html.twig', [
            'avi' => $avi,
            'current_rating' => $avi->getNote()
        ]);
    }

    #[Route('/{id}/edit', name: 'app_avis_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Avis $avi): Response
    {
        $form = $this->createForm(AvisType::class, $avi, [
            'validation_groups' => ['update']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            try {
                // Get the note value directly from the request if it exists
                $formData = $request->request->all();
                if (isset($formData['avis']) && isset($formData['avis']['note']) && !empty($formData['avis']['note'])) {
                    $noteValue = (int) $formData['avis']['note'];
                    if ($noteValue >= 1 && $noteValue <= 5) {
                        $avi->setNote($noteValue);
                    }
                }
                
                $errors = $this->validator->validate($avi, null, ['update']);

                if ($form->isValid() && count($errors) === 0) {
                    $this->entityManager->flush();
                    $this->addFlash('success', 'L\'avis a été mis à jour avec succès !');
                    return $this->redirectToRoute('app_avis_show', ['id' => $avi->getId()]);
                }

                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'avis : ' . $e->getMessage());
            }
        }

        return $this->render('avis/edit.html.twig', [
            'form' => $form->createView(),
            'avi' => $avi
        ]);
    }

    #[Route('/{id}', name: 'app_avis_delete', methods: ['POST'])]
    public function delete(Request $request, Avis $avi): Response
    {
        if ($this->isCsrfTokenValid('delete'.$avi->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($avi);
            $this->entityManager->flush();
            $this->addFlash('success', 'L\'avis a été supprimé avec succès !');
        } else {
            $this->addFlash('error', 'Jeton CSRF invalide !');
        }

        return $this->redirectToRoute('app_avis_index');
    }
}