<?php

namespace App\EventListener;

use App\Entity\GamificationAction;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserActionEvent extends Event
{
    private User $user;
    private GamificationAction $action;

    public function __construct(User $user, GamificationAction $action)
    {
        $this->user = $user;
        $this->action = $action;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getAction(): GamificationAction
    {
        return $this->action;
    }
}
