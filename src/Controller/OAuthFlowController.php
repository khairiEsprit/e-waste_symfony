<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OAuthFlowController extends AbstractController
{
    #[Route('/oauth/set-flow/{flow}', name: 'set_oauth_flow')]
    public function setFlow(string $flow, Request $request): RedirectResponse
    {
        // Validate flow
        if (!in_array($flow, ['login', 'register'])) {
            throw $this->createNotFoundException('Invalid flow parameter');
        }

        // Store flow in session
        $request->getSession()->set('oauth_flow', $flow);

        // Redirect to Google OAuth
        return $this->redirectToRoute('hwi_oauth_service_redirect', [
            'service' => 'google'
        ]);
    }
}