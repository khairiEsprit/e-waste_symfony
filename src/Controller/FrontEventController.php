<?php

// src/Controller/FrontEventController.php
namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/evenements')]
class FrontEventController extends AbstractController
{
    #[Route('/', name: 'app_front_event_list', methods: ['GET'])]
    public function list(Request $request, EventRepository $eventRepository): Response
    {
        $searchTerm = $request->query->get('q', '');
        
        if ($searchTerm) {
            $events = $eventRepository->search($searchTerm);
        } else {
            $events = $eventRepository->findBy([], ['date' => 'ASC']);
        }

        return $this->render('list_evenements/list.html.twig', [
            'events' => $events,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/{id}', name: 'app_front_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        return $this->render('list_evenements/show.html.twig', [
            'event' => $event,
        ]);
    }
}