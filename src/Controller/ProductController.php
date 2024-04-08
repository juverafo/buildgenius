<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Définition du contrôleur ProductController
#[Route('/admin/products')]
class ProductController extends AbstractController
{
    // Route pour afficher la liste des produits et gérer la mise à jour d'un produit
    #[Route('/', name: 'admin_product')]
    #[Route('/{id}', name: 'admin_product_update')]
    public function index(Product $product = null, ProductRepository $repository, Request $request, EntityManagerInterface $manager, $id = null): Response
    {
        // AFFICHAGE OU MISE À JOUR DES PRODUITS
        // Récupérer la liste des produits depuis le repository
        $products = $repository->findAll();

        // Si un ID est spécifié, récupérer le produit correspondant
        // if ($id) {
        //     $product = $repository->find($id);
        // } else {
        //     $product = new Product();
        // }

        // Création du formulaire à partir de ProductType
        $form = $this->createForm(ProductType::class, $product);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        $oldImages = $product ? $product->getImg() : [];
        
        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupération du fichier image
            $imageFiles = $form->get('img')->getData();
            $imageToKeep = [];
            $imgToDelete = [];
            
            if (array_key_exists('checkbox', $request->request->all()) && !empty($request->request->all()['checkbox'])) {
                $imgToDelete = $request->request->get('checkbox');
            }
            // boucle unlik
            foreach($oldImages as $image) {
                if(!in_array($image, $imgToDelete)) {
                    $imageToKeep[] = $image;
                   // unlink dossier storage
                }
            }
            // Génération d'un nom unique pour chaque fichier image et déplacement vers le répertoire d'upload
            $images = [];
            
            foreach ($imageFiles as $imageFile) {
                $imageName = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $imageFile->getClientOriginalExtension();
                $imageFile->move(
                    $this->getParameter('upload_dir'), // Chemin vers le répertoire d'upload
                    $imageName
                );
                $images[] = $imageName;
                // Stocke le tableau mis à jour dans l'entité Product
            }
            foreach ($imageToKeep as $image) {
                $images[] = $image;
            }
            
            $product->setImg($images); 

            // Persistation des données
            $manager->persist($product);

            // Exécution de la transaction
            $manager->flush();

            // Ajout d'un message flash de succès
            $this->addFlash('success', 'Le produit a bien été ajouté');

            // Redirection vers la route admin_product
            return $this->redirectToRoute('admin_product');
        }

        // Rendu de la vue avec les produits et le formulaire
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    // Route pour supprimer un produit
    #[Route('/delete/{id}', name: 'admin_product_delete')]
    public function delete(ProductRepository $repository, EntityManagerInterface $manager, $id = null): Response
    {
        // Vérification de l'existence de l'ID
        if ($id) {
            // Récupération du produit correspondant à l'ID
            $product = $repository->find($id);
        }

        // Suppression du produit
        $manager->remove($product);
        $manager->flush();

        // Redirection vers la liste des produits
        return $this->redirectToRoute('admin_product');
    }

}
