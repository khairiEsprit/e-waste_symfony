<?php



namespace App\Appaydin\PdUser\Event;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\EventDispatcher\Event;

class UserEvent extends Event
{
    public const REGISTER_BEFORE = 'user.register_before';
    public const REGISTER = 'user.register';
    public const REGISTER_CONFIRM = 'user.register_confirm';
    public const RESETTING = 'user.resetting';
    public const RESETTING_COMPLETE = 'user.resetting_complete';

    private $response;

    public function __construct(private UserInterface $user)
    {
    }

    /**
     * Get User.
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * Returns the response object.
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Sets a response and stops event propagation.
     */
    public function setResponse(Response $response): void
    {
        $this->response = $response;

        $this->stopPropagation();
    }

    /**
     * Returns whether a response was set.
     *
     * @return bool Whether a response was set
     */
    public function hasResponse(): bool
    {
        return null !== $this->response;
    }
}
