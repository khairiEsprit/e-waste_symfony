<?php
namespace App\Controller\Back;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    #[Route('/', name: 'back_dashboard')]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        $searchTerm = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $limit = 5;
        
        // Get total users for pagination
        if ($searchTerm) {
            $users = $userRepository->searchByNamePaginated($searchTerm, $page, $limit);
            $totalUsers = count($userRepository->searchByName($searchTerm));
        } else {
            $users = $userRepository->findByPage($page, $limit);
            $totalUsers = count($userRepository->findAll());
        }
        
        $maxPages = ceil($totalUsers / $limit);
        
        if ($request->isXmlHttpRequest()) {
            return $this->render('back/_users_table.html.twig', [
                'users' => $users,
                'currentPage' => $page,
                'maxPages' => $maxPages
            ]);
        }
        
        return $this->render('back/index.html.twig', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'currentPage' => $page,
            'maxPages' => $maxPages
        ]);
    }

    #[Route('/user/{id}', name: 'back_user_show')]
    public function show(User $user): Response
    {
        return $this->render('back/user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{id}/edit', name: 'back_user_edit', methods: ['POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $firstName = $request->request->get('firstName');
        $lastName = $request->request->get('lastName');
        $email = $request->request->get('email');
        
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        
        $userRepository->add($user, true); // Changed from save() to add()
        
        return $this->json([
            'success' => true,
            'message' => 'User updated successfully'
        ]);
    }

    #[Route('/user/{id}/delete', name: 'back_user_delete')]
    public function delete(User $user, UserRepository $userRepository): Response
    {
        $userRepository->remove($user, true);
        $this->addFlash('success', 'User deleted successfully.');
        
        return $this->redirectToRoute('back_dashboard');
    }
}