<?php

namespace App\Form;

use App\Entity\Media;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('src', FileType::class, [
                'label' => 'Images (formats acceptés : jpg, png)',
                'multiple' => false,
                'attr' => [
                    'accept' => 'image/*',
                ],
                'data_class' => null,
                'required' => false,
                'mapped' => true, // Mappé à une propriété de l'entité
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}
