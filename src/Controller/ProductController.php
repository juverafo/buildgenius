<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\MediaRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Ce contrôleur gère les actions liées aux produits dans l'administration
#[IsGranted("ROLE_ADMIN")]
#[Route('/admin/products')]
class ProductController extends AbstractController
{
    // Action pour afficher la liste des produits
    #[Route('/', name: 'admin_product')]
    public function index(ProductRepository $repository)
    {
        // Récupérer la liste des produits depuis le repository
        $products = $repository->findAll();

        // Rendu de la vue avec les produits
        return $this->render('product/index.html.twig', [
            'products' => $products
        ]);
    }

    // Action pour mettre à jour un produit existant
    #[Route('/{id}/update', name: 'admin_product_update')]
    public function update(Product $product = null, Request $request, EntityManagerInterface $manager, MediaRepository $mediaRepository): Response
    {
        // Création du formulaire à partir de ProductType
        $form = $this->createForm(ProductType::class, $product);

        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {

            // Traitement des médias associés au produit
            $files = $request->files->all()['product']['medias'];
            $imagesNames = $this->uploadFiles($files);
            $medias = $product->getMedias();
            
        
            foreach ($medias as $key => $media) {
                if ($media->getSrc() === null) {
                    foreach ($imagesNames as $key => $imageName) {
                        if ($imageName !== null) {
                            $media->setSrc($imageName);
                        }
                    }
                }
                $manager->persist($media);
            }
            
            $manager->flush();

            // handle delete file from directory and media from database
            $mediasToDelete = $mediaRepository->findBy(['product' => null]);
            foreach ($mediasToDelete as $media) {
                    $this->deleteUploadedFiles($media->getSrc());
                $manager->remove($media);
            }

            $manager->flush();

            // Ajout d'un message flash de succès
            $this->addFlash('success', 'Le produit a bien été modifié');

            // Redirection vers la liste des produits
            return $this->redirectToRoute('admin_product');
        }

        // Rendu de la vue avec le formulaire de modification du produit
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    // Action pour ajouter un nouveau produit
    #[Route('/new', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de Product
        $product = new Product();
        // Création du formulaire à partir de ProductType
        $form = $this->createForm(ProductType::class, $product);
        // Gestion de la soumission du formulaire
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Traitement des médias associés au produit
            $files = $request->files->all()['product']['medias'];

            $imagesNames = $this->uploadFiles($files);

            $medias = $product->getMedias();

            foreach ($medias as $key => $media) {
                $media->setSrc($imagesNames[$key]);
            }
            // Persistation du nouveau produit en base de données
            $entityManager->persist($product);

            $entityManager->flush();

            // Ajout d'un message flash de succès
            $this->addFlash('success', 'Le produit a bien été ajouté');

            // Redirection vers la liste des produits
            return $this->redirectToRoute('admin_product');
        }

        // Rendu de la vue avec le formulaire de création du produit
        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    // Action pour supprimer un produit existant
    #[Route('/delete/{id}', name: 'admin_product_delete')]
    public function delete(Product $product, EntityManagerInterface $manager): Response
    {
        if ($product->getId()) {
            // Supprimer les médias associés au produit
            foreach ($product->getMedias() as $media) {
                $manager->remove($media);
            }
            // Suppression du produit
            $manager->remove($product);
            $manager->flush();

            // Ajout d'un message flash de succès
            $this->addFlash('success', 'Le produit a bien été supprimé');
        }
        // Redirection vers la liste des produits
        return $this->redirectToRoute('admin_product');
    }

    // Méthode privée pour gérer l'upload des fichiers
    private function uploadFiles(array $files, array $imagesNames = []): array
    {
        foreach ($files as $imageFile) {
            // Traitement de l'upload des fichiers
            if ($imageFile['src'] !== null) {
                $imageName = date('Y-m-d-H-i-s') . '-' . uniqid() . '.' . $imageFile['src']->getClientOriginalExtension();

                $imageFile['src']->move(
                    $this->getParameter('upload_dir'), // Chemin vers le répertoire d'upload
                    $imageName
                );

                $imagesNames[] = $imageName;
            } else {
                $imagesNames[] = $imageFile['src'];
            }
        }

        return $imagesNames;
    }

    // Méthode privée pour supprimer les fichiers uploadés
    public function deleteUploadedFiles(string $file): void
    {
        // Suppression des fichiers uploadés
        unlink($this->getParameter('upload_dir') . '/' . $file);
    }
}
