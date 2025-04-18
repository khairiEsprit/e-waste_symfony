<?php


namespace App\Appaydin\PdUser\Security;

use App\Appaydin\PdUser\Model\User;
use App\Appaydin\PdUser\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;


class SuperAdminVoter extends Voter
{
    protected function supports($attribute, $subject): bool
    {
        $excluded = [
            'IS_AUTHENTICATED_ANONYMOUSLY',
            'IS_AUTHENTICATED_FULLY',
            'IS_AUTHENTICATED_REMEMBERED',
            'ISGRANTED_VOTER',
            'ROLE_PREVIOUS_ADMIN',
            'IS_IMPERSONATOR',
        ];

        if (!\is_array($attribute)) {
            $attribute = [$attribute];
        }

        foreach ($attribute as $item) {
            if (\in_array($item, $excluded, false)) {
                return false;
            }
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        // Get User
        $user = $token->getUser();

        // Check Login
        if (!$user instanceof UserInterface) {
            return false;
        }

        // Check All Access
        if (\in_array(User::ROLE_ALL_ACCESS, $user->getRoles(), true)) {
            return true;
        }

        return false;
    }
}
