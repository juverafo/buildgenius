<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartController extends AbstractController
{
    public function __construct(
        private ProductRepository $productRepository,
        private CartService $cartService
    ) {
        // Injection des dépendances ProductRepository et CartService dans le contrôleur
        $this->productRepository = $productRepository;
        $this->cartService = $cartService;
    }

    #[Route('/cart/add/{id}/{target}', name: 'add_cart')]
    public function add_cart(CartService $cartService, $id, $target)
    {
        // Ajoute un produit au panier
        $cartService->add($id);
        $this->addFlash('success', 'Le produit a bien été ajouté dans le panier');
        return $this->redirectToRoute($target);
    }

    #[Route('/cart/remove/{id}/{target}', name: 'remove_cart')]
    public function remove_cart(CartService $cartService, $id, $target)
    {
        // Supprime un produit du panier
        $cartService->remove($id);
        $this->addFlash('success', 'Le produit a bien été supprimé du panier');
        return $this->redirectToRoute($target);
    }

    #[Route('/cart/delete/{id}', name: 'delete_cart')]
    public function delete_cart(CartService $cartService, $id)
    {
        // Supprime complètement un produit du panier
        $cartService->delete($id);
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/destroy', name: 'destroy_cart')]
    public function destroy_cart(CartService $cartService)
    {
        // Supprime tous les produits du panier
        $cartService->destroy();
        return $this->redirectToRoute('app_products');
    }

    #[Route('/cart', name: 'cart')]
    public function cart(CartService $cartService)
    {
        // Affiche le contenu du panier avec les détails des produits et le total
        $cart = $cartService->getCartWithData();
        $total = $cartService->getTotal();
        return $this->render('cart/cart.html.twig', [
            'cart' => $cart,
            'total' => $total
        ]);
    }
}
