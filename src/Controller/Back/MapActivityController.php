<?php
namespace App\Controller\Back;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class MapActivityController extends AbstractController
{
    #[Route('/map-activity', name: 'back_map_activity')]
    public function index(): Response
    {
        // Sample data for trash locations
        $trashLocations = [
            [
                'id' => 1,
                'name' => 'Trash Container #1',
                'longitude' => 10.181667,
                'latitude' => 36.806389,
                'fillLevel' => 85,
                'status' => 'Critical'
            ],
            [
                'id' => 2,
                'name' => 'Trash Container #2',
                'longitude' => 10.184667,
                'latitude' => 36.809389,
                'fillLevel' => 70,
                'status' => 'Warning'
            ],
            [
                'id' => 3,
                'name' => 'Trash Container #3',
                'longitude' => 10.179667,
                'latitude' => 36.803389,
                'fillLevel' => 45,
                'status' => 'Normal'
            ],
            [
                'id' => 4,
                'name' => 'Trash Container #4',
                'longitude' => 10.186667,
                'latitude' => 36.801389,
                'fillLevel' => 30,
                'status' => 'Normal'
            ],
            [
                'id' => 5,
                'name' => 'Trash Container #5',
                'longitude' => 10.182667,
                'latitude' => 36.807389,
                'fillLevel' => 60,
                'status' => 'Warning'
            ],
        ];

        // Collection center location
        $centerLocation = [
            'name' => 'Collection Center',
            'longitude' => 10.183667,
            'latitude' => 36.805389
        ];

        // Sort trash locations by fill level (highest to lowest)
        usort($trashLocations, function($a, $b) {
            return $b['fillLevel'] <=> $a['fillLevel'];
        });

        return $this->render('back/map_activity/index.html.twig', [
            'trashLocations' => $trashLocations,
            'centerLocation' => $centerLocation,
            'mapboxKey' => 'pk.eyJ1IjoibmFzc2VmZmFkaGxhb3VpNTUiLCJhIjoiY203ZWtjNDU0MDgwMDJqc2VsaGlweXRuZiJ9.AIx_P3VKlvQD59v9dsWpCg'
        ]);
    }
}