<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestController extends AbstractController
{
    #[Route('/test/face-recognition', name: 'test_face_recognition')]
    public function testFaceRecognition(): Response
    {
        return $this->render('test_face_recognition.html.twig');
    }
}
