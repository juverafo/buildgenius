<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class NewPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Nouveau mot de passe', 
                    'required' => false
                ],
                'second_options' => [
                    'label' => 'Confirmation mot de passe', 
                    'required' => false
                ],
                'invalid_message' => 'Les mots de passe ne correpsondent pas',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Champs obligatoire'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Votre mot de passe doit contenir un minimum de {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('Enregistrer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
