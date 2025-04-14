<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Cloudinary\Cloudinary;

class ProfileController extends AbstractController
{
    private $cloudinary;
    
    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }
    
    #[Route('/profile', name: 'app_profile')]
    public function index(): Response
    {
        // Make sure the user is logged in
        if (!$this->getUser()) {
            return $this->redirectToRoute('security_login');
        }

        return $this->render('front/profile/index.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Make sure the user is logged in
        if (!$this->getUser()) {
            throw new AccessDeniedException('You need to be logged in to edit your profile');
        }

        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle profile image upload
            $profileImageFile = $form->get('profileImageFile')->getData();
            
            if ($profileImageFile) {
                try {
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
                } catch (\Exception $e) {
                    $this->addFlash('error', 'There was an error uploading your profile image.');
                }
            }

            // Handle password change if provided
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);
            }

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profile updated successfully!');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('front/profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
