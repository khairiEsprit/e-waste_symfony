<?php

// src/Security/MyOAuthUserProvider.php
// src/Security/MyOAuthUserProvider.php
namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class MyOAuthUserProvider implements OAuthAwareUserProviderInterface, UserProviderInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email = $response->getEmail();
        if (empty($email)) {
            throw new UserNotFoundException('Email not provided by Google');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($response->getFirstName() ?? 'Unknown');
            $user->setLastName($response->getLastName() ?? 'User');
            $user->setPassword(bin2hex(random_bytes(10))); // Random password for Google users
            $user->setActive(true);
            $user->setFreeze(false);
            $user->setRoles([User::ROLE_DEFAULT]);
            $user->setCreatedAt(new \DateTime());
            $user->setLanguage('en'); // Default language

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);
        if (!$user) {
            throw new UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getEmail());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}