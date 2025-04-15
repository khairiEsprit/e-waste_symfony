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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Cloudinary\Cloudinary;
use Symfony\Component\String\Slugger\AsciiSlugger;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Security controller.
 */
class SecurityController extends AbstractController
{
    private $translator;
    private $mailer;
    private $dispatcher;
    private $hasher;
    private $registry;
    private $config;
    private $logger;
    private $cloudinary;

    public function __construct(
        TranslatorInterface $translator,
        MailerInterface $mailer,
        EventDispatcherInterface $dispatcher,
        UserPasswordHasherInterface $hasher,
        ManagerRegistry $registry,
        ?ConfigInterface $config = null,
        LoggerInterface $logger,
        Cloudinary $cloudinary
    ) {
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->dispatcher = $dispatcher;
        $this->hasher = $hasher;
        $this->registry = $registry;
        $this->config = $config;
        $this->logger = $logger;
        $this->cloudinary = $cloudinary;
    }

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
            return $this->redirectToRoute('back_dashboard');
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
        error_log('Starting registration process');

        // Check authentication and registration availability
        if ($this->checkAuth()) {
            error_log('User already authenticated, redirecting');
            return $this->redirectToRoute($this->getParameter('login_redirect'));
        }

        if (!$this->getParameter('user_registration')) {
            error_log('Registration is disabled');
            $this->addFlash('error', $this->translator->trans('security.registration_disable'));
            return $this->redirectToRoute('security_login');
        }

        // Initialize user
        error_log('Initializing user instance');
        $userClass = $this->getParameter('user_class');
        $user = new $userClass();

        if (!$user instanceof UserInterface) {
            error_log('ERROR: Invalid user class provided - ' . get_class($user));
            throw new InvalidArgumentException();
        }

        // Dispatch before registration event
        error_log('Dispatching REGISTER_BEFORE event');
        if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::REGISTER_BEFORE)->getResponse()) {
            error_log('REGISTER_BEFORE event returned a response, redirecting');
            return $response;
        }

        // Create form and handle request
        error_log('Creating registration form');
        $form = $this->createForm($this->getParameter('register_type'), $user);
        $form->handleRequest($request);
        error_log('Form data after handling: ' . json_encode($form->getData()));


        if ($form->isSubmitted()) {
            error_log('Form submitted');

            if ($form->isValid()) {
                error_log('Form is valid, processing registration');
                if (!$form->isValid()) {
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $errors[$error->getOrigin()->getName()] = $error->getMessage();
                    }
                    error_log('Form validation errors: ' . json_encode($errors));
                }

             

                // Handle file upload
                error_log('Checking for file upload');
                $profileImageFile = $form->get('profileImageFile')->getData();
                $newFilename = null;

                if ($profileImageFile) {
                    error_log('Processing file upload: ' . $profileImageFile->getClientOriginalName());

                    try {
                        error_log('Attempting to upload file to Cloudinary');
                        // Upload image to Cloudinary
                        $uploadResult = $this->cloudinary->uploadApi()->upload(
                            $profileImageFile->getPathname(),
                            [
                                'folder' => 'profile_images',
                                'resource_type' => 'image',
                            ]
                        );
                        
                        // Save the secure URL to the user's profile
                        $user->setProfileImage($uploadResult['secure_url']);
                        error_log('File successfully uploaded to Cloudinary');
                    } catch (\Exception $e) {
                        error_log('ERROR uploading file to Cloudinary: ' . $e->getMessage());
                        $this->addFlash('error', $this->translator->trans('security.profile_image_upload_error'));
                    }
                } else {
                    error_log('No file was uploaded');
                }

                // Set password
                error_log('Hashing password');
                $password = $this->hasher->hashPassword($user, $form->get('plainPassword')->getData());
                $user->setPassword($password);

                // Handle email confirmation if enabled
                if ($this->getParameter('email_confirmation')) {
                    error_log('Email confirmation enabled, processing');
                    $user->setActive(false);
                    if (empty($user->getConfirmationToken()) || null === $user->getConfirmationToken()) {
                        error_log('Generating new confirmation token');
                        $user->createConfirmationToken();
                    }

                    $emailBody = [
                        'confirmationUrl' => $this->generateUrl(
                            'security_register_confirm',
                            ['token' => $user->getConfirmationToken()],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ),
                        'profileImage' => $newFilename ?? null,
                    ];

                    error_log('Sending confirmation email');
                    $this->sendEmail($user, 'email.account_confirmation', 'register', $emailBody);
                } elseif ($this->getParameter('welcome_email')) {
                    error_log('Sending welcome email');
                    $this->sendEmail($user, 'email.registration_complete', 'welcome');
                }

                // Add to default group if configured
                $em = $this->registry->getManager();
                if ($group = $this->getParameter('default_group')) {
                    error_log('Checking default group assignment');
                    $getGroup = $em->getRepository($this->getParameter('group_class'))->find($group);
                    if ($getGroup instanceof GroupInterface) {
                        error_log('Adding user to group: ' . $group);
                        $user->addGroup($getGroup);
                    }
                }

                // Persist user
                error_log('Persisting user to database');
                $em->persist($user);
                $em->flush();
                error_log('User successfully persisted');
                $notification = new Notification();
                $notification->setMessage(sprintf(
                    'New user "%s" signed up ',
                    $user->getFirstName()
                   
                ));
                $em->persist($notification);
                $em->flush();
                // Dispatch after registration event
                error_log('Dispatching REGISTER event');
                if ($response = $this->dispatcher->dispatch(new UserEvent($user), UserEvent::REGISTER)->getResponse()) {
                    error_log('REGISTER event returned a response, redirecting');
                    return $response;
                }

                error_log('Registration complete, rendering success page');
                return $this->render($this->getParameter('template_path') . '/registration/registerSuccess.html.twig', [
                    'user' => $user,
                ]);
            } else {
                error_log('Form validation failed');
                error_log('Form errors: ' . json_encode($this->getFormErrors($form)));
            }
        }

        error_log('Rendering registration form');
        return $this->render($this->getParameter('template_path') . '/registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Helper function to get form errors
    private function getFormErrors($form)
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
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