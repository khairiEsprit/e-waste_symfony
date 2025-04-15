<?php


namespace App\Appaydin\PdUser\Listener;

use Doctrine\ORM\EntityManagerInterface;
use App\Appaydin\PdUser\Model\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LoginListener implements EventSubscriberInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * On Login Event.
     */
    public function onLogin(InteractiveLoginEvent $event): void
    {
        // Get User
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof UserInterface) {
            // Change Site Language to User
            if ($user->getLanguage()) {
                $event->getRequest()->getSession()->set('_locale', $user->getLanguage());
            }

            // Set Last Login
            $user->setLastLogin(new \DateTime());

            // Save
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onLogin',
        ];
    }
}
