<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        return $this->render('admin/dashboard.html.twig');
    }
    #[Route('/users', name: 'users')]
    #[Route('/users/{id}/{role}', name: 'users_update')]
    public function users(UserRepository $userRepository, EntityManagerInterface $manager, $id = null, $role = null): Response
    {
        $users = $userRepository->findAll();
        if ($id)
        {
            $user = $userRepository->find($id);
            if($role){
                $user->setRoles([$role]);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Rôle modifié');
            }
        return $this->redirectToRoute('users');
        
    }
            return $this->render('admin/users.html.twig', [
                'users' => $users
            ]);
    }

    #[Route('/users/status/{id}/{active}', name: 'users_active')]
    public function users_active(UserRepository $userRepository, EntityManagerInterface $manager, $id = null, $active = null)
    {
        if($id){
        $user = $userRepository->find($id);
        $user->setActive($active);
        $manager->persist($user);
        $manager->flush();
        $this->addFlash('success', 'Status changé');
        }
        return $this->redirectToRoute('users');
    }

}
