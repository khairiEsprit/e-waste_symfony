<?php
namespace App\Controller\Front;

use App\Form\CustomRegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Entity\UserInterface;
use Doctrine\ORM\EntityManagerInterface;

class AuthController extends AbstractController
{
    private $hasher;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager)
    {
        $this->hasher = $hasher;
        $this->entityManager = $entityManager;
    }

    #[Route('/auth', name: 'app_auth')]
    public function index(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
//         // Redirect authenticated users
//         if ($this->getUser()) {
//             return $this->redirectToRoute('front_home'); // Adjust to your default route
//         }

//         // Login data
//         $lastUsername = $authenticationUtils->getLastUsername();
//         $error = $authenticationUtils->getLastAuthenticationError();

//         // Registration form
//         $userClass = 'App\Entity\User'; // Hardcode temporarily
//         $user = new $userClass();
//         if (!$user instanceof UserInterface) {
//             throw new \InvalidArgumentException('User class must implement UserInterface');
//         }

//         $registrationForm = $this->createForm(CustomRegisterType::class, $user);
//         $registrationForm->handleRequest($request);

//         if ($registrationForm->isSubmitted() && $registrationForm->isValid()) {
//             // Hash password
//             $password = $this->hasher->hashPassword($user, $registrationForm->get('plainPassword')->getData());
//             $user->setPassword($password);

//             // Save user
//             $this->entityManager->persist($user);
//             $this->entityManager->flush();

//             // Add success message
//             $this->addFlash('success', 'Registration successful! Please log in.');
//             return $this->redirectToRoute('app_auth');
//         }

//         return $this->render('front/authentication/login.html.twig', [
//             'last_username' => $lastUsername,
//             'error' => $error,
//             'registration_form' => $registrationForm->createView(),
//         ]);
     }
 }