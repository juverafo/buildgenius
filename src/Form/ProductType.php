<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => array(
                    'placeholder' => 'Intel i5-10600k...'
                )
            ])
            ->add('reseller', TextType::class, [
                'label' => 'Revendeur',
                'attr' => array(
                    'placeholder' => 'Amazon, CDiscount etc...',
                ),
                'required' => false
            ])
            ->add('link', TextType::class, [
                'label' => 'Lien',
                'required' => false
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'attr' => array(
                    'placeholder' => '50€'
                )
            ])
            ->add('description', TextType::class, [
                'label' => 'Description',
                'attr' => array(
                    'placeholder' => "Le processeur Intel Core Comet Lake-S de 10e génération offre plus de cœurs et le multithreading Hyper Threading du Core i3 au Core i9. Avec des fréquences de fonctionnement plus élevées et le même TDP que les générations précédentes, le processeur Intel Core de 10e génération offre plus de performances et de fluidité que jamais auparavant. Le processeur Intel Core i5-10600K avec 6 cœurs (12 threads), 12 Mo de cache et des fréquences turbo allant jusqu'à 4,8 GHz sans overclocking est destiné aux joueurs exigeants et aux utilisateurs qui ont besoin d'une puissance de calcul importante au quotidien."
                )
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'choice_label' => 'name'
            ])
            ->add('medias', CollectionType::class, [
                'entry_type' => MediaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => ['label' => false],
                'attr' => [
                    'data-controller' => 'form-collection'
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Valider'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
