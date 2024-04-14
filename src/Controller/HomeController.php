<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Le contrôleur HomeController gère les différentes actions liées à l'affichage des pages d'accueil, des produits et des profils utilisateur.
class HomeController extends AbstractController
{
    // Action pour afficher la page d'accueil.
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    // Action pour afficher la liste des produits.
    #[Route('/products', name: 'app_products')]
    public function products(ProductRepository $productRepository): Response
    {
        // Récupérer tous les produits depuis le repository.
        $products = $productRepository->findAll();

        // Rendre la vue des produits avec la liste des produits.
        return $this->render('home/products.html.twig', [
            'products' => $products
        ]);
    }

    // Action pour afficher les détails d'un produit.
    #[Route('/detail/{id}', name: 'app_product_detail')]
    public function products_detail(ProductRepository $repository, $id)
    {
        // Récupérer le produit correspondant à l'identifiant fourni.
        $product = $repository->find($id);

        // Rendre la vue des détails du produit avec les informations du produit.
        return $this->render('product/product_detail.html.twig', [
            'product' => $product
        ]);
    }

    // Action pour afficher le profil utilisateur.
    #[Route('/profile/{id}', name: 'profile')]
    public function profile(UserRepository $userRepository, $id): Response
    {
        // Récupérer l'utilisateur correspondant à l'identifiant fourni.
        $user = $userRepository->find($id);

        // Rendre la vue du profil utilisateur avec les informations de l'utilisateur.
        return $this->render('home/profile.html.twig', [
            'user' => $user
        ]);
    }

    // MentionLegales
    // Politique de confidentialité
}
