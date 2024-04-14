<?php

namespace App\Controller;

use App\Entity\OrderPurchase;
use App\Entity\Purchase;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class StripePayController extends AbstractController
{
    // Endpoint pour l'index de paiement Stripe
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route('/stripe/pay', name: 'app_stripe_pay')]
    public function index(CartService $cs): Response
    {
        // Récupération du panier complet avec les données
        $fullCart = $cs->getCartWithData();

        // Tableau recevant les informations des produits pour la session de paiement
        $line_items = [];

        // Construction des lignes d'achat pour la session de paiement
        foreach ($fullCart as $item) {
            $line_items[] = [
                'price_data' => [
                    // Conversion du prix en centimes (Stripe exige le prix en plus petites unités)
                    'unit_amount' => $item['product']->getPrice() * 100, 
                    'currency' => 'EUR',
                    'product_data' => [
                        'name' => $item['product']->getName()
                    ]
                ],
                'quantity' => $item['quantity']
            ];
        }

        // Authentification avec la clé API Stripe
        $stripeKey = $this->getParameter('app_stripe');
        Stripe::setApiKey($stripeKey);

        // Création de la session de paiement avec les informations nécessaires
        $session = Session::create([
            'success_url' => 'https://127.0.0.1:8000/commande/success', // URL de succès de paiement
            'cancel_url' => 'https://127.0.0.1:8000/cart', // URL d'annulation de paiement
            'payment_method_types' => ['card'], // Types de méthode de paiement autorisés
            'line_items' => $line_items, // Articles à acheter
            'mode' => 'payment' // Mode de la session de paiement
        ]);

        // Redirection vers l'URL de la session de paiement Stripe
        return $this->redirect($session->url, 303);
    }

    // Endpoint de confirmation de commande
    #[Route('/commande/{success}', name: 'commande')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function commande(CartService $cartService, EntityManagerInterface $manager, $success = null): Response
    {
        if ($success) {
            // Création d'une nouvelle commande avec les informations de l'utilisateur
            $order = new OrderPurchase();
            $order->setUser($this->getUser());
            $order->setDate(new \DateTime());
            $order->setStatus(0);
            $manager->persist($order);

            // Création des achats individuels associés à la commande
            foreach ($cartService->getCartWithData() as $item) {
                $purchase = new Purchase();
                $purchase->setOrderPurchase($order);
                $purchase->setQuantity($item['quantity']);
                $purchase->setProduct($item['product']);
                $manager->persist($purchase);
            }

            // Enregistrement des changements dans la base de données
            $manager->flush();

            // Notification de succès et redirection vers la page d'accueil
            $this->addFlash('success', 'Merci pour votre confiance');
            $cartService->destroy(); // Suppression du panier
            return $this->redirectToRoute('app_home');
        } else {
            // En cas d'échec, notification et redirection vers le panier
            $this->addFlash('danger', 'Un problème est survenu merci de réitérer votre paiement');
            return $this->redirectToRoute('cart');
        }
    }
}

