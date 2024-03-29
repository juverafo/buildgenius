<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'admin_category')]
    #[Route('/{id}', name: 'admin_category_update')]
    public function index(CategoryRepository $repository, Request $request, EntityManagerInterface $manager, $id=null): Response
    {
        // AFFICHAGE DES CATEGORIES
        // récupérer la liste des catégories depuis la base de données
        $categories = $repository->findAll();

        // MODIFICATION D'UNE CATÉGORIE EXISTANTE OU AJOUT D'UNE NOUVELLE CATÉGORIE
        if($id){
            // Si un identifiant est fourni, cela signifie qu'on veut modifier une catégorie existante
            $category = $repository->find($id);
        } else {
            // Sinon, on crée une nouvelle instance de Category
            $category = new Category();
        }
        
        // GÉNÉRATION DU FORMULAIRE
        // Création du formulaire à partir de la classe CategoryType
        $form = $this->createForm(CategoryType::class, $category);

        // GESTION DE LA REQUÊTE
        // Analyse de la requête HTTP
        $form->handleRequest($request);
 
        // TRAITEMENT DU FORMULAIRE
        // Vérification si le formulaire a été soumis et est valide
        if ($form->isSubmitted() && $form->isValid()){
            // Récupération des données du formulaire
            $category = $form->getData();
 
            // Persistation des données en base de données
            $manager->persist($category);
 
            // Exécution de la transaction
            $manager->flush();

            // Ajout d'un message flash pour indiquer que la catégorie a été ajoutée avec succès
            $this->addFlash('success', 'La catégorie a bien été ajoutée');
 
            // Redirection vers la route admin_category
            return $this->redirectToRoute('admin_category');
        }
           
        // RENDU DE LA VUE
        return $this->render('category/index.html.twig', [
            'categories' => $categories,
            'form' => $form->createView()
        ]);
    }

    // SUPPRESSION DES CATÉGORIES
    #[Route('/delete/{id}', name: 'admin_category_delete')]
    public function delete(CategoryRepository $repository, EntityManagerInterface $manager, $id = null):Response
    {   
        if($id){
            // Récupération de la catégorie à supprimer
            $category = $repository->find($id);
        }
        
        // Suppression de la catégorie
        $manager->remove($category);
        $manager->flush();
        
        // Redirection vers la page d'administration des catégories
        return $this->redirectToRoute('admin_category');
    }
}
