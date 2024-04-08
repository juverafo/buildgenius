<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }
    #[Route('/products', name: 'app_products')]
    public function products(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findAll();

        return $this->render('home/products.html.twig', [
            'products' => $products
        ]);
    }
    // AFFICHAGE DES DETAILS DES PRODUITS
    #[Route('/detail/{id}', name: 'app_product_detail')]
    public function products_detail(ProductRepository $repository, $id)
    {
        $product = $repository->find($id);

        return $this->render('product/product_detail.html.twig', [
            'product' => $product
        ]);
    }
    #[Route('/profile/{id}', name: 'profile')]
    public function profile(UserRepository $userRepository, $id): Response
    {
        $user = $userRepository->find($id);

        return $this->render('home/profile.html.twig', [
            'user' => $user
        ]);
    }
}
