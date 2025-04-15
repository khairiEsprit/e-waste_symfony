<?php
namespace App\Security;

use Psr\Log\LoggerInterface;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class MyOAuthUserProvider implements OAuthAwareUserProviderInterface
{
    private $em;
    private $logger;
    private $requestStack;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->requestStack = $requestStack;
    }

    public function loadUserByOAuthUserResponse(UserResponseInterface $response): UserInterface
    {
        $email = $response->getEmail();
        if (empty($email)) {
            $this->logger->error('No email provided by OAuth provider');
            throw new AuthenticationException('Email not provided by Google');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        $this->logger->info('Checking for existing user with email: ' . $email);
        $this->logger->info('User found: ' . ($user ? 'Yes' : 'No'));

        // Get the flow from session (default to login if not set)
        $session = $this->requestStack->getSession();
        $flow = $session->get('oauth_flow', 'login');
        $this->logger->info('OAuth flow: ' . $flow);

        if ($flow === 'login') {
            // Login flow: authenticate existing user
            if (!$user) {
                $this->logger->info('Login flow - user does not exist');
                $this->requestStack->getSession()->getFlashBag()->add(
                    'modal_error',
                    'No account found with this email. Please register first.'
                );
                throw new AuthenticationException('user_not_found');
            }

            $this->logger->info('Login flow - authenticating existing user');
            return $user;
        }

        // Registration flow
        if ($user) {
            $this->logger->info('Registration flow - user already exists');
            $this->requestStack->getSession()->getFlashBag()->add(
                'modal_error',
                'A user with this email already exists. Please log in with your existing account.'
            );
            throw new AuthenticationException('user_already_exists');
        }

        // Create new user
        $this->logger->info('Creating new user with email: ' . $email);

        $user = new User();
        $user->setEmail($email);
        $user->setProfileImage($response->getProfilePicture() ?? '1674638131915-67e8239fd0044.jpg');
        $user->setFirstName($response->getFirstName() ?? 'Unknown');
        $user->setLastName($response->getLastName() ?? 'User');
        $user->setPassword(bin2hex(random_bytes(10)));
        $user->setActive(true);
        $user->setFreeze(false);
        $user->setRoles([User::ROLE_DEFAULT, 'ROLE_CITOYEN']);
        $user->setCreatedAt(new \DateTime());
        $user->setLanguage('en');

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $identifier]);
        if (!$user) {
            throw new \Symfony\Component\Security\Core\Exception\UserNotFoundException(sprintf('User with email "%s" not found.', $identifier));
        }
        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new \Symfony\Component\Security\Core\Exception\UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        return $this->loadUserByIdentifier($user->getEmail());
    }

    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}