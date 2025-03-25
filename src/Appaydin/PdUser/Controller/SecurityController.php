<?php
namespace App\Appaydin\PdUser\Controller;

use Doctrine\Persistence\ManagerRegistry;
use App\Appaydin\PdUser\Configuration\ConfigInterface;
use App\Appaydin\PdUser\Event\UserEvent;
use App\Appaydin\PdUser\Model\GroupInterface;
use App\Appaydin\PdUser\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    private $translator;
    private $mailer;
    private $dispatcher;
    private $hasher;
    private $registry;
    private $config;

    /**
     * Constructor with dependency injection.
     */
    public function __construct(
        TranslatorInterface $translator,
        MailerInterface $mailer,
        EventDispatcherInterface $dispatcher,
        UserPasswordHasherInterface $hasher,
        ManagerRegistry $registry,
        ?ConfigInterface $config = null
    ) {
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
        $this->hasher = $hasher;
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * Login action.
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToDashboard();
        }

        return $this->render(
            $this->getParameter('template_path') . '/security/login.html.twig',
            [
                'last_username' => $authenticationUtils->getLastUsername(),
                'error' => $authenticationUtils->getLastAuthenticationError(),
                'user_registration' => $this->getParameter('user_registration'),
            ]
        );
    }

    /**
     * Redirect authenticated users to their dashboard.
     */
    private function redirectToDashboard(): Response
    {
        $defaultRedirect = $this->getParameter('login_redirect');

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('admin_dashboard');
        } elseif ($this->isGranted('ROLE_CITOYEN')) {
            return $this->redirectToRoute('back_dashboard');
        } elseif ($this->isGranted('ROLE_EMPLOYEE')) {
            return $this->redirectToRoute('employee_dashboard');
        }

        return $this->redirectToRoute($defaultRedirect);
    }

    /**
     * User registration action.
     */
    public function register(Request $request): Response
    {
        if ($this->checkAuth()) {
            return $this->redirectToRoute($this->getParameter('login_redirect'));
        }

        if (!$this->getParameter('user_registration')) {
            $this->addFlash('error', $this->translator->trans('security.registration_disable'));
            return $this->redirectToRoute('security_login');
        }

        $userClass = $this->getParameter('user_class');
        $user = new $userClass();
        if (!$user instanceof UserInterface) {
            throw new InvalidArgumentException();
        }

        if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::REGISTER_BEFORE)->getResponse()) {
            return $response;
        }

        $form = $this->createForm($this->getParameter('register_type'), $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();
            $password = $this->hasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password);

            if ($this->getParameter('email_confirmation')) {
                $user->setActive(false);
                if (empty($user->getConfirmationToken()) || null === $user->getConfirmationToken()) {
                    $user->createConfirmationToken();
                }
                $emailBody = [
                    'confirmationUrl' => $this->generateUrl(
                        'security_register_confirm',
                        ['token' => $user->getConfirmationToken()],
                        UrlGeneratorInterface::ABSOLUTE_URL
                    ),
                ];
                $this->sendEmail($user, 'email.account_confirmation', 'register', $emailBody);
            } elseif ($this->getParameter('welcome_email')) {
                $this->sendEmail($user, 'email.registration_complete', 'welcome');
            }

            if ($group = $this->getParameter('default_group')) {
                $getGroup = $em->getRepository($this->getParameter('group_class'))->find($group);
                if ($getGroup instanceof GroupInterface) {
                    $user->addGroup($getGroup);
                }
            }

            $em->persist($user);
            $em->flush();

            if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::REGISTER)->getResponse()) {
                return $response;
            }

            return $this->render($this->getParameter('template_path') . '/registration/registerSuccess.html.twig', [
                'user' => $user,
            ]);
        }

        return $this->render($this->getParameter('template_path') . '/registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Confirm registration with token.
     */
    public function registerConfirm(MailerInterface $mailer, string $token): Response
    {
        $em = $this->registry->getManager();
        $user = $em->getRepository($this->getParameter('user_class'))->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            throw $this->createNotFoundException(sprintf($this->translator->trans('security.token_notfound'), $token));
        }

        $user->setConfirmationToken(null);
        $user->setActive(true);

        if ($this->getParameter('welcome_email')) {
            $this->sendEmail($user, 'email.registration_complete', 'welcome');
        }

        $em->persist($user);
        $em->flush();

        if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::REGISTER_CONFIRM)->getResponse()) {
            return $response;
        }

        return $this->render($this->getParameter('template_path') . '/registration/registerSuccess.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * Password reset request.
     */
    public function resetting(Request $request): Response
    {
        if ($this->checkAuth()) {
            return $this->redirectToRoute($this->getParameter('login_redirect'));
        }

        $form = $this->createForm($this->getParameter('resetting_type'));
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->registry->getManager();
            $user = $em->getRepository($this->getParameter('user_class'))->findOneBy(['email' => $form->get('username')->getData()]);

            if (null === $user) {
                $form->get('username')->addError(new FormError($this->translator->trans('security.user_not_found')));
            } else {
                if ($user->isPasswordRequestNonExpired($this->getParameter('resetting_request_time'))) {
                    $form->get('username')->addError(new FormError($this->translator->trans(
                        'security.resetpw_wait_resendig',
                        ['%s' => ($this->getParameter('resetting_request_time') / 3600)]
                    )));
                } else {
                    if (empty($user->getConfirmationToken()) || null === $user->getConfirmationToken()) {
                        $user->createConfirmationToken();
                        $user->setPasswordRequestedAt(new \DateTime());
                    }

                    $emailBody = [
                        'confirmationUrl' => $this->generateUrl(
                            'security_resetting_password',
                            ['token' => $user->getConfirmationToken()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                    ];
                    $this->sendEmail($user, 'email.account_password_resetting', 'resetting', $emailBody);

                    $em->persist($user);
                    $em->flush();

                    if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::RESETTING)->getResponse()) {
                        return $response;
                    }

                    return $this->render($this->getParameter('template_path') . '/resetting/resettingSuccess.html.twig', [
                        'sendEmail' => true,
                    ]);
                }
            }
        }

        return $this->render($this->getParameter('template_path') . '/resetting/resetting.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Reset password with token.
     */
    public function resettingPassword(Request $request, string $token): Response
    {
        $em = $this->registry->getManager();
        $user = $em->getRepository($this->getParameter('user_class'))->findOneBy(['confirmationToken' => $token]);

        if (null === $user) {
            throw $this->createNotFoundException(sprintf($this->translator->trans('security.token_notfound'), $token));
        }

        $form = $this->createForm($this->getParameter('resetting_password_type'), $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $this->hasher->hashPassword($user, $form->get('plainPassword')->getData());
            $user->setPassword($password)
                ->setConfirmationToken(null)
                ->setPasswordRequestedAt(null);

            $em->persist($user);
            $em->flush();

            if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::RESETTING_COMPLETE)->getResponse()) {
                return $response;
            }

            $this->sendEmail($user, 'email.password_resetting_completed', 'resetting-complete');

            return $this->render($this->getParameter('template_path') . '/resetting/resettingSuccess.html.twig', [
                'sendEmail' => false,
            ]);
        }

        return $this->render($this->getParameter('template_path') . '/resetting/resettingPassword.html.twig', [
            'token' => $token,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Check if user is authenticated.
     */
    private function checkAuth(): bool
    {
        return $this->isGranted('IS_AUTHENTICATED_FULLY') || $this->isGranted('IS_AUTHENTICATED_REMEMBERED');
    }

    /**
     * Send email to user.
     */
    private function sendEmail(UserInterface $user, string $subject, string $templateId, array $data = []): void
    {
        $email = (new Email())
            ->from(new Address($this->getParameter('mail_sender_address'), $this->getParameter('mail_sender_name')))
            ->to($user->getEmail())
            ->subject($this->translator->trans($subject))
            ->html($this->renderView($this->getParameter('template_path') . "/email/{$templateId}.html.twig", array_merge(['user' => $user], $data)));

        $this->mailer->send($email);
    }

    /**
     * Override parameter retrieval.
     */
    protected function getParameter(string $name): \UnitEnum|array|string|int|float|bool|null
    {
        if ($this->config) {
            return $this->config->get($name);
        }
        return parent::getParameter($name);
    }
}