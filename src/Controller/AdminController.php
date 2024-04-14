<?php

namespace App\Controller;

use App\Entity\OrderPurchase;
use App\Repository\OrderPurchaseRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(): Response
    {
        // Affiche le tableau de bord de l'administrateur
        return $this->render('admin/dashboard.html.twig');
    }

    #[Route('/users', name: 'users')]
    #[Route('/users/{id}/{role}', name: 'users_update')]
    public function users(UserRepository $userRepository, EntityManagerInterface $manager, $id = null, $role = null): Response
    {
        // Récupère tous les utilisateurs et affiche la page de gestion des utilisateurs
        $users = $userRepository->findAll();
        if ($id) {
            // Si un ID est spécifié, récupère l'utilisateur correspondant
            $user = $userRepository->find($id);
            if ($role) {
                // Si un rôle est spécifié, met à jour le rôle de l'utilisateur
                $user->setRoles([$role]);
                $manager->persist($user);
                $manager->flush();
                $this->addFlash('success', 'Rôle modifié');
            }
            // Redirige vers la page des utilisateurs
            return $this->redirectToRoute('users');
        }
        // Affiche la page des utilisateurs avec la liste des utilisateurs
        return $this->render('admin/users.html.twig', [
            'users' => $users
        ]);
    }

    #[Route('/users/status/{id}/{active}', name: 'users_active')]
    public function users_active(UserRepository $userRepository, EntityManagerInterface $manager, $id = null, $active = null)
    {
        // Active ou désactive un utilisateur en fonction de son ID et de l'état actif passé en paramètre
        if ($id) {
            $user = $userRepository->find($id);
            $user->setActive($active);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'Status changé');
        }
        // Redirige vers la page des utilisateurs
        return $this->redirectToRoute('users');
    }
    #[Route('/purchases/{id}/{status}', name: 'purchases')]
    public function purchases(UserRepository $userRepository, OrderPurchaseRepository $orderPurchaseRepository, EntityManagerInterface $manager, $id = null, $status = null): Response
    {
        // Récupère toutes les commandes et affiche la page de gestion des commandes
        $orders = $orderPurchaseRepository->findAll();

        if ($id) {
            $user = $userRepository->find($id);
            $purchase = $orderPurchaseRepository->find($user);
            $purchase->setStatus($status);
            $manager->persist($purchase);
            $manager->flush();
            $this->addFlash('success', 'Status de commande changé');
            // Redirige vers la page des achats
            return $this->redirectToRoute('purchases');
        }
        // Affiche la page des utilisateurs avec la liste des utilisateurs
        return $this->render('admin/purchase.html.twig', [
            'orders' => $orders
        ]);
    }
}
