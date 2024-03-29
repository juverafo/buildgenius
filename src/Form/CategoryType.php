<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder            
            ->add('name', TextType::class, [
                'label' => 'Titre de catégorie',
                'attr' => array(
                    'placeholder' => 'CPU, GPU, carte mère, RAM etc...'
                )
            ])
            ->add('type', TextType::class, [
                'label' => 'Type de catégorie',
                'attr' => array(
                    'placeholder' => 'Services, Composants'
                )
            ])
            ->add('submit', SubmitType::class,[
                    'label' => 'Valider'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
