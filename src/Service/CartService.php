<?php 

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $repository;

    private $session;

    // Le constructeur permet d'injecter automatiquement le ProductRepository et la RequestStack lors de l'instanciation du service dans les contrôleurs.
    public function __construct(ProductRepository $repo, RequestStack $session)
    {
        $this->repository = $repo;
        $this->session = $session;
    }

    // Méthode pour ajouter un produit au panier
    public function add(int $id): void
    {
        // Récupération de la session
        $local = $this->session->getSession();
        $cart = $local->get('cart', []); // Récupération du panier s'il existe, sinon création d'un nouveau panier

        // Si l'indice $id n'existe pas dans le tableau, le produit n'a pas été ajouté, donc initialisation de la quantité à 1
        if (!isset($cart[$id])) {
            $cart[$id] = 1;
        } else {
            // Sinon, on incrémente la quantité
            $cart[$id]++;
        }

        // Mise à jour de la session
        $local->set('cart', $cart);
    }

    // Méthode pour retirer une unité d'un produit du panier
    public function remove(int $id): void
    {
        // Récupération de la session
        $local = $this->session->getSession();
        $cart = $local->get('cart', []);

        // Vérification de la présence de l'ID dans le panier et que la quantité est supérieure à 1
        if (isset($cart[$id]) && $cart[$id] > 1) {
            $cart[$id]--; // Décrémentation de la quantité
        } else {
            // Sinon, suppression totale de l'entrée correspondant à cet ID
            unset($cart[$id]);
        }

        // Mise à jour de la session
        $local->set('cart', $cart);
    }

    // Méthode pour supprimer complètement un produit du panier
    public function delete(int $id): void
    {
        // Récupération de la session
        $local = $this->session->getSession();
        $cart = $local->get('cart', []);
        
        // Si l'ID existe dans le panier, suppression de l'entrée correspondante
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }

        // Mise à jour de la session
        $local->set('cart', $cart);
    }

    // Méthode pour vider complètement le panier
    public function destroy(): void
    {
        $this->session->getSession()->remove('cart'); // Suppression de la clé 'cart' de la session
    }

    // Méthode pour récupérer le panier avec les données des produits
    public function getCartWithData(): array
    {
        // Récupération de la session
        $local = $this->session->getSession();
        $cart = $local->get('cart', []); // Récupération du panier s'il existe, sinon panier vide
        $cartWithData = []; // Initialisation du tableau

        // Parcours du panier pour récupérer les données des produits
        foreach ($cart as $id => $quantity) {
            $cartWithData[] = [
                'product' => $this->repository->find($id), // Récupération du produit depuis le repository
                'quantity' => $quantity // Quantité du produit dans le panier
            ];
        }

        return $cartWithData;
    }

    // Méthode pour calculer le total du panier
    public function getTotal(): float
    {
        $total = 0; // Initialisation du total à 0

        // Parcours du panier pour calculer le total en additionnant les prix des produits
        foreach ($this->getCartWithData() as $data) {
            $total += $data['product']->getPrice() * $data['quantity']; // Calcul du total pour chaque produit
        }

        return $total; // Retourne le total du panier
    }
}
