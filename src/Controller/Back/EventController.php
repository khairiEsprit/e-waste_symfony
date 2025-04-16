<?php

namespace App\Controller\Back;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/admin/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('back/event/index.html.twig', [
            'events' => $eventRepository->findBy([], ['date' => 'ASC']),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Handle file upload
                $imageFile = $form->get('image')->getData();
                
                if ($imageFile) {
                    $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                    // Generate a unique name for the file
                    // Replace special characters and spaces with underscores
                    $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $originalFilename);
                    $safeFilename = strtolower($safeFilename);
                    $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                    
                    // Move the file to the uploads directory
                    try {
                        $imageFile->move(
                            $this->getParameter('events_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                    }
                    
                    // Update the imageName property
                    $event->setImageName($newFilename);
                }
                
                $entityManager->persist($event);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement "'.$event->getTitle().'" a été créé avec succès !');
                return $this->redirectToRoute('app_event_index');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la création de l\'événement: ' . $e->getMessage());
            }
        }

        return $this->render('back/event/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('back/event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Store original title to display in success message
        $originalTitle = $event->getTitle();
        // Store original image name
        $originalImageName = $event->getImageName();
        
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    // Handle file upload
                    $imageFile = $form->get('image')->getData();
                    
                    if ($imageFile) {
                        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                        // Generate a unique name for the file
                        // Replace special characters and spaces with underscores
                        $safeFilename = preg_replace('/[^a-zA-Z0-9]/', '_', $originalFilename);
                        $safeFilename = strtolower($safeFilename);
                        $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                        
                        // Move the file to the uploads directory
                        try {
                            $imageFile->move(
                                $this->getParameter('events_directory'),
                                $newFilename
                            );
                            
                            // Delete old image if exists
                            if ($originalImageName) {
                                $oldImagePath = $this->getParameter('events_directory') . '/' . $originalImageName;
                                if (file_exists($oldImagePath)) {
                                    unlink($oldImagePath);
                                }
                            }
                            
                            // Update the imageName property
                            $event->setImageName($newFilename);
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image');
                        }
                    } else {
                        // Keep the original image if no new image is uploaded
                        $event->setImageName($originalImageName);
                    }
                    
                    // Save changes to database
                    $entityManager->flush();

                    // Add success message
                    $this->addFlash('success', 'L\'événement "'.$originalTitle.'" a été mis à jour avec succès !');
                    
                    // Redirect to show page with success message
                    return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);

                } catch (\Exception $e) {
                    // Log the error for debugging
                    error_log($e->getMessage());
                    
                    // Add error message
                    $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de l\'événement : ' . $e->getMessage());
                }
            } else {
                // Form validation errors
                $this->addFlash('error', 'Veuillez corriger les erreurs dans le formulaire.');
            }
        }

        return $this->render('back/event/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            try {
                $entityManager->remove($event);
                $entityManager->flush();

                $this->addFlash('success', 'L\'événement "'.$event->getTitle().'" a été supprimé avec succès !');

            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la suppression de l\'événement');
            }
        }

        return $this->redirectToRoute('app_event_index');
    }
}